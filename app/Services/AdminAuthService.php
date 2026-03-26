<?php

declare(strict_types=1);

final class AdminAuthService
{
    private const SESSION_KEY = 'admin_auth';

    public function __construct(private readonly UserRepository $users)
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

        session_put(self::SESSION_KEY, [
            'id' => (int) $user['id'],
            'name' => (string) $user['name'],
            'email' => (string) $user['email'],
        ]);

        return true;
    }

    public function currentUser(): ?array
    {
        $auth = session_value(self::SESSION_KEY);

        return is_array($auth) ? $auth : null;
    }

    public function check(): bool
    {
        return is_array($this->currentUser());
    }

    public function logout(): void
    {
        session_forget(self::SESSION_KEY);
    }
}
