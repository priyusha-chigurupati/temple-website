<?php

declare(strict_types=1);

final class AdminDashboardRepository
{
    public function __construct(private readonly PDO $connection)
    {
    }

    public function contentCounts(): array
    {
        return [
            'pages' => $this->countWhere("SELECT COUNT(*) FROM pages WHERE status = 'published'"),
            'events' => $this->countWhere("SELECT COUNT(*) FROM events WHERE status = 'published'"),
            'gallery_items' => $this->countWhere("SELECT COUNT(*) FROM gallery_items WHERE status = 'published'"),
            'blog_posts' => $this->countWhere("SELECT COUNT(*) FROM blog_posts WHERE status = 'published'"),
        ];
    }

    public function submissionCounts(): array
    {
        return [
            'contact_pending' => $this->countWhere("SELECT COUNT(*) FROM contact_inquiries WHERE status = 'pending'"),
            'donation_pending' => $this->countWhere("SELECT COUNT(*) FROM donation_notifications WHERE status = 'pending'"),
        ];
    }

    public function recentContactInquiries(int $limit = 5): array
    {
        $statement = $this->connection->prepare(
            'SELECT id, full_name, email, subject, status, created_at
             FROM contact_inquiries
             ORDER BY created_at DESC, id DESC
             LIMIT :limit'
        );
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll() ?: [];
    }

    public function recentDonationNotifications(int $limit = 5): array
    {
        $statement = $this->connection->prepare(
            'SELECT id, full_name, payment_method, amount, reference_id, status, created_at
             FROM donation_notifications
             ORDER BY created_at DESC, id DESC
             LIMIT :limit'
        );
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll() ?: [];
    }

    private function countWhere(string $sql): int
    {
        $value = $this->connection->query($sql)->fetchColumn();

        return $value === false ? 0 : (int) $value;
    }
}
