<?php

declare(strict_types=1);

final class UserRepository
{
    public function __construct(private readonly PDO $connection)
    {
    }

    public function findByEmail(string $email): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT id, name, email, password_hash
             FROM users
             WHERE email = :email
             LIMIT 1'
        );
        $statement->execute(['email' => $email]);
        $user = $statement->fetch();

        return is_array($user) ? $user : null;
    }

    public function findById(int $id): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT id, name, email
             FROM users
             WHERE id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $user = $statement->fetch();

        return is_array($user) ? $user : null;
    }

    public function findByIdWithPassword(int $id): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT id, name, email, password_hash
             FROM users
             WHERE id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $user = $statement->fetch();

        return is_array($user) ? $user : null;
    }

    public function emailExistsForAnotherUser(string $email, int $excludeId): bool
    {
        $statement = $this->connection->prepare(
            'SELECT COUNT(*)
             FROM users
             WHERE email = :email
               AND id <> :id'
        );
        $statement->execute([
            'email' => $email,
            'id' => $excludeId,
        ]);

        return (int) $statement->fetchColumn() > 0;
    }

    public function updateProfile(int $id, string $name, string $email): bool
    {
        $statement = $this->connection->prepare(
            'UPDATE users
             SET name = :name,
                 email = :email
             WHERE id = :id'
        );

        return $statement->execute([
            'id' => $id,
            'name' => $name,
            'email' => $email,
        ]);
    }

    public function updatePassword(int $id, string $passwordHash): bool
    {
        $statement = $this->connection->prepare(
            'UPDATE users
             SET password_hash = :password_hash
             WHERE id = :id'
        );

        return $statement->execute([
            'id' => $id,
            'password_hash' => $passwordHash,
        ]);
    }
}
