<?php

declare(strict_types=1);

final class AdminAccountRepository
{
    public function __construct(
        private readonly PDO $connection,
        private readonly UserRepository $users
    )
    {
    }

    public function profile(int $userId): ?array
    {
        return $this->users->findById($userId);
    }

    public function saveProfile(int $userId, array $input): array
    {
        $old = [
            'name' => trim((string) ($input['name'] ?? '')),
            'email' => trim((string) ($input['email'] ?? '')),
        ];

        $errors = [];

        if ($old['name'] === '') {
            $errors[] = 'Please enter the admin name.';
        }

        if ($old['email'] === '' || filter_var($old['email'], FILTER_VALIDATE_EMAIL) === false) {
            $errors[] = 'Please enter a valid admin email address.';
        }

        if ($old['email'] !== '' && $this->users->emailExistsForAnotherUser($old['email'], $userId)) {
            $errors[] = 'That email is already assigned to another admin account.';
        }

        if ($errors !== []) {
            return [
                'ok' => false,
                'errors' => $errors,
                'old' => $old,
            ];
        }

        try {
            $this->users->updateProfile($userId, $old['name'], $old['email']);

            return ['ok' => true];
        } catch (Throwable) {
            return [
                'ok' => false,
                'errors' => ['The admin profile could not be updated.'],
                'old' => $old,
            ];
        }
    }

    public function changePassword(int $userId, array $input): array
    {
        $old = [
            'current_password' => (string) ($input['current_password'] ?? ''),
            'new_password' => (string) ($input['new_password'] ?? ''),
            'confirm_password' => (string) ($input['confirm_password'] ?? ''),
        ];

        $errors = [];
        $user = $this->users->findByIdWithPassword($userId);

        if ($user === null) {
            $errors[] = 'The admin account could not be found.';
        }

        if ($old['current_password'] === '') {
            $errors[] = 'Please enter the current password.';
        }

        if ($old['new_password'] === '') {
            $errors[] = 'Please enter the new password.';
        } elseif (strlen($old['new_password']) < 10) {
            $errors[] = 'The new password must be at least 10 characters long.';
        }

        if ($old['confirm_password'] === '') {
            $errors[] = 'Please confirm the new password.';
        }

        if ($old['new_password'] !== '' && $old['confirm_password'] !== '' && $old['new_password'] !== $old['confirm_password']) {
            $errors[] = 'The new password and confirmation do not match.';
        }

        if (
            $user !== null
            && $old['current_password'] !== ''
            && ! password_verify($old['current_password'], (string) ($user['password_hash'] ?? ''))
        ) {
            $errors[] = 'The current password is incorrect.';
        }

        if (
            $user !== null
            && $old['new_password'] !== ''
            && password_verify($old['new_password'], (string) ($user['password_hash'] ?? ''))
        ) {
            $errors[] = 'Please choose a password different from the current password.';
        }

        if ($errors !== []) {
            return [
                'ok' => false,
                'errors' => $errors,
            ];
        }

        try {
            $this->users->updatePassword($userId, password_hash($old['new_password'], PASSWORD_DEFAULT));
            $this->expireResetTokensForUser($userId);

            return ['ok' => true];
        } catch (Throwable) {
            return [
                'ok' => false,
                'errors' => ['The password could not be updated.'],
            ];
        }
    }

    public function createResetRequest(string $email): array
    {
        $email = trim($email);

        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return [
                'ok' => false,
                'errors' => ['Please enter a valid admin email address.'],
                'old' => ['email' => $email],
            ];
        }

        $user = $this->users->findByEmail($email);
        $resetLink = null;

        if ($user !== null) {
            try {
                $token = bin2hex(random_bytes(32));
                $tokenHash = hash('sha256', $token);
                $expiresAt = date('Y-m-d H:i:s', time() + 3600);

                $this->connection->beginTransaction();

                $invalidate = $this->connection->prepare(
                    'UPDATE password_reset_tokens
                     SET used_at = NOW()
                     WHERE user_id = :user_id
                       AND used_at IS NULL'
                );
                $invalidate->execute(['user_id' => (int) $user['id']]);

                $insert = $this->connection->prepare(
                    'INSERT INTO password_reset_tokens (user_id, token_hash, expires_at)
                     VALUES (:user_id, :token_hash, :expires_at)'
                );
                $insert->execute([
                    'user_id' => (int) $user['id'],
                    'token_hash' => $tokenHash,
                    'expires_at' => $expiresAt,
                ]);

                $this->connection->commit();

                $resetLink = route_url('/admin/reset-password?token=' . rawurlencode($token));
            } catch (Throwable) {
                if ($this->connection->inTransaction()) {
                    $this->connection->rollBack();
                }

                return [
                    'ok' => false,
                    'errors' => ['The reset request could not be created right now.'],
                    'old' => ['email' => $email],
                ];
            }
        }

        return [
            'ok' => true,
            'reset_link' => $resetLink,
            'email' => $email,
        ];
    }

    public function resetContext(string $token): ?array
    {
        if ($token === '') {
            return null;
        }

        $statement = $this->connection->prepare(
            'SELECT prt.id,
                    prt.user_id,
                    u.email,
                    u.name,
                    prt.expires_at,
                    prt.used_at
             FROM password_reset_tokens prt
             INNER JOIN users u ON u.id = prt.user_id
             WHERE prt.token_hash = :token_hash
             LIMIT 1'
        );
        $statement->execute([
            'token_hash' => hash('sha256', $token),
        ]);
        $row = $statement->fetch();

        if (! is_array($row)) {
            return null;
        }

        if (($row['used_at'] ?? null) !== null) {
            return null;
        }

        $expiresAt = strtotime((string) ($row['expires_at'] ?? ''));

        if ($expiresAt === false || $expiresAt < time()) {
            return null;
        }

        return [
            'id' => (int) $row['id'],
            'user_id' => (int) $row['user_id'],
            'email' => (string) $row['email'],
            'name' => (string) $row['name'],
            'expires_at' => (string) $row['expires_at'],
        ];
    }

    public function resetPassword(string $token, array $input): array
    {
        $context = $this->resetContext($token);
        $old = [
            'new_password' => (string) ($input['new_password'] ?? ''),
            'confirm_password' => (string) ($input['confirm_password'] ?? ''),
        ];

        if ($context === null) {
            return [
                'ok' => false,
                'errors' => ['This reset link is invalid or has expired. Please request a new password reset.'],
                'old' => $old,
            ];
        }

        $errors = [];

        if ($old['new_password'] === '') {
            $errors[] = 'Please enter a new password.';
        } elseif (strlen($old['new_password']) < 10) {
            $errors[] = 'The new password must be at least 10 characters long.';
        }

        if ($old['confirm_password'] === '') {
            $errors[] = 'Please confirm the new password.';
        }

        if ($old['new_password'] !== '' && $old['confirm_password'] !== '' && $old['new_password'] !== $old['confirm_password']) {
            $errors[] = 'The new password and confirmation do not match.';
        }

        if ($errors !== []) {
            return [
                'ok' => false,
                'errors' => $errors,
                'old' => $old,
                'context' => $context,
            ];
        }

        try {
            $this->connection->beginTransaction();
            $this->users->updatePassword((int) $context['user_id'], password_hash($old['new_password'], PASSWORD_DEFAULT));

            $markUsed = $this->connection->prepare(
                'UPDATE password_reset_tokens
                 SET used_at = NOW()
                 WHERE id = :id'
            );
            $markUsed->execute(['id' => (int) $context['id']]);

            $this->expireResetTokensForUser((int) $context['user_id']);
            $this->connection->commit();

            return ['ok' => true];
        } catch (Throwable) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }

            return [
                'ok' => false,
                'errors' => ['The password could not be reset. Please try again.'],
                'old' => $old,
                'context' => $context,
            ];
        }
    }

    private function expireResetTokensForUser(int $userId): void
    {
        $statement = $this->connection->prepare(
            'UPDATE password_reset_tokens
             SET used_at = COALESCE(used_at, NOW())
             WHERE user_id = :user_id'
        );
        $statement->execute(['user_id' => $userId]);
    }
}
