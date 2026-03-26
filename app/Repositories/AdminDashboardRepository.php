<?php

declare(strict_types=1);

final class AdminDashboardRepository
{
    public function __construct(
        private readonly PDO $connection,
        private readonly string $localeCode = 'en'
    )
    {
    }

    public function totalContentCount(): int
    {
        return ($this->contentCounts()['pages'] ?? 0)
            + ($this->contentCounts()['events'] ?? 0)
            + ($this->contentCounts()['gallery_items'] ?? 0)
            + ($this->contentCounts()['blog_posts'] ?? 0);
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
        $contactPending = $this->countWhere("SELECT COUNT(*) FROM contact_inquiries WHERE status = 'pending'");
        $donationPending = $this->countWhere("SELECT COUNT(*) FROM donation_notifications WHERE status = 'pending'");

        return [
            'contact_pending' => $contactPending,
            'donation_pending' => $donationPending,
            'total_pending' => $contactPending + $donationPending,
        ];
    }

    public function donationSummary(): array
    {
        $statement = $this->connection->query(
            "SELECT
                COALESCE(SUM(amount), 0) AS total_amount,
                COUNT(*) AS total_records
             FROM donation_notifications
             WHERE created_at >= (NOW() - INTERVAL 30 DAY)"
        );
        $row = $statement->fetch();

        return [
            'total_amount' => isset($row['total_amount']) ? (float) $row['total_amount'] : 0.0,
            'total_records' => isset($row['total_records']) ? (int) $row['total_records'] : 0,
        ];
    }

    public function nextEventStatus(): array
    {
        $statement = $this->connection->query(
            "SELECT starts_at
             FROM events
             WHERE status = 'published'
               AND starts_at >= NOW()
             ORDER BY starts_at ASC
             LIMIT 1"
        );
        $startsAt = $statement->fetchColumn();

        if ($startsAt === false) {
            return [
                'title' => 'No Upcoming Event',
                'detail' => 'Schedule pending',
            ];
        }

        $timestamp = strtotime((string) $startsAt);
        $days = $timestamp === false ? null : (int) floor(($timestamp - time()) / 86400);

        return [
            'title' => 'Next Event',
            'detail' => $days !== null && $days >= 0 ? 'In ' . $days . ' days' : date('M d, Y', $timestamp ?: time()),
        ];
    }

    public function upcomingEvents(int $limit = 4): array
    {
        $statement = $this->connection->prepare(
            'SELECT
                e.slug,
                e.starts_at,
                e.is_featured_home,
                et.title,
                m.file_path AS image_path
             FROM events e
             INNER JOIN locales l
                ON l.code = :locale_code
             INNER JOIN event_translations et
                ON et.event_id = e.id
               AND et.locale_id = l.id
             LEFT JOIN media m
                ON m.id = e.image_id
             WHERE e.status = :status
               AND e.starts_at >= NOW()
             ORDER BY e.starts_at ASC
             LIMIT :limit'
        );
        $statement->bindValue(':locale_code', $this->localeCode);
        $statement->bindValue(':status', 'published');
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        $items = [];

        foreach ($statement->fetchAll() as $row) {
            $items[] = [
                'slug' => (string) ($row['slug'] ?? ''),
                'title' => (string) ($row['title'] ?? ''),
                'date' => $this->formatEventDate((string) ($row['starts_at'] ?? '')),
                'status' => ((int) ($row['is_featured_home'] ?? 0) === 1) ? 'Featured' : 'Published',
                'image' => (string) ($row['image_path'] ?? ''),
            ];
        }

        return $items;
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

    private function formatEventDate(string $value): string
    {
        $timestamp = strtotime($value);

        return $timestamp === false
            ? $value
            : date('M d, Y • h:i A', $timestamp);
    }
}
