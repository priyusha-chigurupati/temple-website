<?php

declare(strict_types=1);

final class AdminAuthService
{
    private const SESSION_KEY = 'admin_auth';
    private const DEFAULT_TIMEOUT_SECONDS = 1800;

    public function __construct(
        private readonly UserRepository $users,
        private readonly int $timeoutSeconds = self::DEFAULT_TIMEOUT_SECONDS
    )
    {
    }

    public function attempt(string $email, string $password): bool
    {
        $user = $this->users->findByEmail($email);

        if ($user === null) {
            return false;
        }

        if (! password_verify($password, (string) ($user['password_hash'] ?? ''))) {
            return false;
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }

        session_put(self::SESSION_KEY, [
            'id' => (int) $user['id'],
            'name' => (string) $user['name'],
            'email' => (string) $user['email'],
            'last_activity' => time(),
        ]);

        return true;
    }

    public function currentUser(): ?array
    {
        $auth = session_value(self::SESSION_KEY);

        if (! is_array($auth)) {
            return null;
        }

        $lastActivity = (int) ($auth['last_activity'] ?? 0);

        if ($lastActivity > 0 && (time() - $lastActivity) > $this->timeoutSeconds) {
            $this->logout();
            flash_set('admin_auth_state', [
                'type' => 'error',
                'message' => 'You were logged out after inactivity. Please sign in again.',
            ]);

            return null;
        }

        $auth['last_activity'] = time();
        session_put(self::SESSION_KEY, $auth);

        unset($auth['last_activity']);

        return $auth;
    }

    public function check(): bool
    {
        return is_array($this->currentUser());
    }

    public function logout(): void
    {
        session_forget(self::SESSION_KEY);
    }

    public function syncProfile(int $id, string $name, string $email): void
    {
        $auth = session_value(self::SESSION_KEY);

        if (! is_array($auth) || (int) ($auth['id'] ?? 0) !== $id) {
            return;
        }

        $auth['name'] = $name;
        $auth['email'] = $email;
        $auth['last_activity'] = time();

        session_put(self::SESSION_KEY, $auth);
    }
}
