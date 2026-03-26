<?php

declare(strict_types=1);

final class AdminSubmissionRepository
{
    private const PAGE_SIZE = 10;

    public function __construct(private readonly PDO $connection)
    {
    }

    public function overviewCounts(): array
    {
        return [
            'contact_total' => $this->count('SELECT COUNT(*) FROM contact_inquiries'),
            'contact_pending' => $this->count("SELECT COUNT(*) FROM contact_inquiries WHERE status = 'pending'"),
            'donation_total' => $this->count('SELECT COUNT(*) FROM donation_notifications'),
            'donation_pending' => $this->count("SELECT COUNT(*) FROM donation_notifications WHERE status = 'pending'"),
            'gallery_total' => $this->count('SELECT COUNT(*) FROM gallery_submissions'),
            'gallery_pending' => $this->count("SELECT COUNT(*) FROM gallery_submissions WHERE status = 'pending'"),
            'newsletter_total' => $this->count('SELECT COUNT(*) FROM newsletter_subscriptions'),
            'newsletter_active' => $this->count("SELECT COUNT(*) FROM newsletter_subscriptions WHERE status = 'active'"),
        ];
    }

    public function contactListing(array $filters): array
    {
        $status = $this->normalizeStatus((string) ($filters['status'] ?? ''), ['pending', 'reviewed']);
        $search = trim((string) ($filters['q'] ?? ''));
        $page = max(1, (int) ($filters['page'] ?? 1));

        $where = [];
        $params = [];

        if ($status !== 'all') {
            $where[] = 'status = :status';
            $params['status'] = $status;
        }

        if ($search !== '') {
            $where[] = '(full_name LIKE :search OR email LIKE :search OR subject LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        $whereSql = $where !== [] ? 'WHERE ' . implode(' AND ', $where) : '';
        $total = $this->countPrepared("SELECT COUNT(*) FROM contact_inquiries {$whereSql}", $params);
        $pagination = $this->pagination($total, $page);

        $statement = $this->connection->prepare(
            "SELECT id, full_name, email, subject, message, status, created_at
             FROM contact_inquiries
             {$whereSql}
             ORDER BY created_at DESC, id DESC
             LIMIT :limit OFFSET :offset"
        );
        $this->bindListingParams($statement, $params, $pagination);
        $statement->execute();

        return [
            'items' => $statement->fetchAll() ?: [],
            'status' => $status,
            'search_term' => $search,
            'pagination' => $pagination,
            'total_items' => $total,
            'filters' => [
                ['key' => 'all', 'label' => 'All Messages', 'count' => $this->count('SELECT COUNT(*) FROM contact_inquiries')],
                ['key' => 'pending', 'label' => 'Pending', 'count' => $this->count("SELECT COUNT(*) FROM contact_inquiries WHERE status = 'pending'")],
                ['key' => 'reviewed', 'label' => 'Reviewed', 'count' => $this->count("SELECT COUNT(*) FROM contact_inquiries WHERE status = 'reviewed'")],
            ],
        ];
    }

    public function contactFind(int $id): ?array
    {
        return $this->findOne(
            'SELECT id, full_name, email, subject, message, status, created_at
             FROM contact_inquiries
             WHERE id = :id
             LIMIT 1',
            ['id' => $id]
        );
    }

    public function markContactReviewed(int $id): bool
    {
        $statement = $this->connection->prepare(
            "UPDATE contact_inquiries
             SET status = 'reviewed'
             WHERE id = :id"
        );

        return $statement->execute(['id' => $id]);
    }

    public function donationListing(array $filters): array
    {
        $status = $this->normalizeStatus((string) ($filters['status'] ?? ''), ['pending', 'reviewed']);
        $search = trim((string) ($filters['q'] ?? ''));
        $page = max(1, (int) ($filters['page'] ?? 1));

        $where = [];
        $params = [];

        if ($status !== 'all') {
            $where[] = 'status = :status';
            $params['status'] = $status;
        }

        if ($search !== '') {
            $where[] = '(full_name LIKE :search OR payment_method LIKE :search OR reference_id LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        $whereSql = $where !== [] ? 'WHERE ' . implode(' AND ', $where) : '';
        $total = $this->countPrepared("SELECT COUNT(*) FROM donation_notifications {$whereSql}", $params);
        $pagination = $this->pagination($total, $page);

        $statement = $this->connection->prepare(
            "SELECT id, full_name, payment_method, amount, reference_id, phone, email, address, message, purpose, status, created_at
             FROM donation_notifications
             {$whereSql}
             ORDER BY created_at DESC, id DESC
             LIMIT :limit OFFSET :offset"
        );
        $this->bindListingParams($statement, $params, $pagination);
        $statement->execute();

        return [
            'items' => $statement->fetchAll() ?: [],
            'status' => $status,
            'search_term' => $search,
            'pagination' => $pagination,
            'total_items' => $total,
            'filters' => [
                ['key' => 'all', 'label' => 'All Notices', 'count' => $this->count('SELECT COUNT(*) FROM donation_notifications')],
                ['key' => 'pending', 'label' => 'Pending', 'count' => $this->count("SELECT COUNT(*) FROM donation_notifications WHERE status = 'pending'")],
                ['key' => 'reviewed', 'label' => 'Reviewed', 'count' => $this->count("SELECT COUNT(*) FROM donation_notifications WHERE status = 'reviewed'")],
            ],
        ];
    }

    public function donationFind(int $id): ?array
    {
        return $this->findOne(
            'SELECT id, full_name, payment_method, amount, reference_id, phone, email, address, message, purpose, status, created_at
             FROM donation_notifications
             WHERE id = :id
             LIMIT 1',
            ['id' => $id]
        );
    }

    public function markDonationReviewed(int $id): bool
    {
        $statement = $this->connection->prepare(
            "UPDATE donation_notifications
             SET status = 'reviewed'
             WHERE id = :id"
        );

        return $statement->execute(['id' => $id]);
    }

    public function galleryListing(array $filters): array
    {
        $status = $this->normalizeStatus((string) ($filters['status'] ?? ''), ['pending', 'approved', 'rejected']);
        $search = trim((string) ($filters['q'] ?? ''));
        $page = max(1, (int) ($filters['page'] ?? 1));

        $where = [];
        $params = [];

        if ($status !== 'all') {
            $where[] = 'gs.status = :status';
            $params['status'] = $status;
        }

        if ($search !== '') {
            $where[] = '(gs.submitter_name LIKE :search OR gs.submitter_email LIKE :search OR gs.description LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        $whereSql = $where !== [] ? 'WHERE ' . implode(' AND ', $where) : '';
        $total = $this->countPrepared("SELECT COUNT(*) FROM gallery_submissions gs {$whereSql}", $params);
        $pagination = $this->pagination($total, $page);

        $statement = $this->connection->prepare(
            "SELECT
                gs.id,
                gs.submitter_name,
                gs.submitter_email,
                gs.description,
                gs.status,
                gs.created_at,
                gs.reviewed_at,
                (
                    SELECT COUNT(*)
                    FROM gallery_submission_files gsf
                    WHERE gsf.submission_id = gs.id
                ) AS file_count
             FROM gallery_submissions gs
             {$whereSql}
             ORDER BY gs.created_at DESC, gs.id DESC
             LIMIT :limit OFFSET :offset"
        );
        $this->bindListingParams($statement, $params, $pagination);
        $statement->execute();

        return [
            'items' => $statement->fetchAll() ?: [],
            'status' => $status,
            'search_term' => $search,
            'pagination' => $pagination,
            'total_items' => $total,
            'filters' => [
                ['key' => 'all', 'label' => 'All Submissions', 'count' => $this->count('SELECT COUNT(*) FROM gallery_submissions')],
                ['key' => 'pending', 'label' => 'Pending', 'count' => $this->count("SELECT COUNT(*) FROM gallery_submissions WHERE status = 'pending'")],
                ['key' => 'approved', 'label' => 'Approved', 'count' => $this->count("SELECT COUNT(*) FROM gallery_submissions WHERE status = 'approved'")],
                ['key' => 'rejected', 'label' => 'Rejected', 'count' => $this->count("SELECT COUNT(*) FROM gallery_submissions WHERE status = 'rejected'")],
            ],
        ];
    }

    public function galleryFind(int $id): ?array
    {
        $submission = $this->findOne(
            'SELECT
                gs.id,
                gs.submitter_name,
                gs.submitter_email,
                gs.description,
                gs.status,
                gs.review_notes,
                gs.created_at,
                gs.reviewed_at,
                reviewer.name AS reviewer_name
             FROM gallery_submissions gs
             LEFT JOIN users reviewer
               ON reviewer.id = gs.reviewed_by
             WHERE gs.id = :id
             LIMIT 1',
            ['id' => $id]
        );

        if ($submission === null) {
            return null;
        }

        $files = $this->findAll(
            'SELECT file_path, original_name, mime_type, size_bytes
             FROM gallery_submission_files
             WHERE submission_id = :id
             ORDER BY id ASC',
            ['id' => $id]
        );

        $submission['files'] = $files;

        return $submission;
    }

    public function reviewGallery(int $id, int $reviewerId, array $input): array
    {
        $status = trim((string) ($input['status'] ?? ''));
        $notes = trim((string) ($input['review_notes'] ?? ''));
        $allowed = ['approved', 'rejected'];

        if (! in_array($status, $allowed, true)) {
            return [
                'ok' => false,
                'errors' => ['Please choose whether the submission is approved or rejected.'],
                'old' => ['status' => $status, 'review_notes' => $notes],
            ];
        }

        $statement = $this->connection->prepare(
            'UPDATE gallery_submissions
             SET status = :status,
                 reviewed_by = :reviewed_by,
                 reviewed_at = NOW(),
                 review_notes = :review_notes
             WHERE id = :id'
        );

        $statement->execute([
            'id' => $id,
            'status' => $status,
            'reviewed_by' => $reviewerId,
            'review_notes' => $notes !== '' ? $notes : null,
        ]);

        return ['ok' => true];
    }

    public function newsletterListing(array $filters): array
    {
        $status = $this->normalizeStatus((string) ($filters['status'] ?? ''), ['active', 'unsubscribed']);
        $search = trim((string) ($filters['q'] ?? ''));
        $page = max(1, (int) ($filters['page'] ?? 1));

        $where = [];
        $params = [];

        if ($status !== 'all') {
            $where[] = 'status = :status';
            $params['status'] = $status;
        }

        if ($search !== '') {
            $where[] = '(email LIKE :search OR source LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        $whereSql = $where !== [] ? 'WHERE ' . implode(' AND ', $where) : '';
        $total = $this->countPrepared("SELECT COUNT(*) FROM newsletter_subscriptions {$whereSql}", $params);
        $pagination = $this->pagination($total, $page);

        $statement = $this->connection->prepare(
            "SELECT id, email, source, status, created_at
             FROM newsletter_subscriptions
             {$whereSql}
             ORDER BY created_at DESC, id DESC
             LIMIT :limit OFFSET :offset"
        );
        $this->bindListingParams($statement, $params, $pagination);
        $statement->execute();

        return [
            'items' => $statement->fetchAll() ?: [],
            'status' => $status,
            'search_term' => $search,
            'pagination' => $pagination,
            'total_items' => $total,
            'filters' => [
                ['key' => 'all', 'label' => 'All Subscribers', 'count' => $this->count('SELECT COUNT(*) FROM newsletter_subscriptions')],
                ['key' => 'active', 'label' => 'Active', 'count' => $this->count("SELECT COUNT(*) FROM newsletter_subscriptions WHERE status = 'active'")],
                ['key' => 'unsubscribed', 'label' => 'Unsubscribed', 'count' => $this->count("SELECT COUNT(*) FROM newsletter_subscriptions WHERE status = 'unsubscribed'")],
            ],
        ];
    }

    public function updateNewsletterStatus(int $id, string $status): bool
    {
        if (! in_array($status, ['active', 'unsubscribed'], true)) {
            return false;
        }

        $statement = $this->connection->prepare(
            'UPDATE newsletter_subscriptions
             SET status = :status
             WHERE id = :id'
        );

        return $statement->execute([
            'id' => $id,
            'status' => $status,
        ]);
    }

    private function normalizeStatus(string $value, array $allowed): string
    {
        return in_array($value, $allowed, true) ? $value : 'all';
    }

    private function count(string $sql): int
    {
        $value = $this->connection->query($sql)->fetchColumn();

        return $value === false ? 0 : (int) $value;
    }

    private function countPrepared(string $sql, array $params): int
    {
        $statement = $this->connection->prepare($sql);

        foreach ($params as $key => $value) {
            $statement->bindValue(':' . $key, $value);
        }

        $statement->execute();
        $value = $statement->fetchColumn();

        return $value === false ? 0 : (int) $value;
    }

    private function pagination(int $totalItems, int $requestedPage): array
    {
        $totalPages = max(1, (int) ceil($totalItems / self::PAGE_SIZE));
        $page = min(max(1, $requestedPage), $totalPages);

        return [
            'page' => $page,
            'per_page' => self::PAGE_SIZE,
            'total_pages' => $totalPages,
            'offset' => ($page - 1) * self::PAGE_SIZE,
        ];
    }

    private function bindListingParams(PDOStatement $statement, array $params, array $pagination): void
    {
        foreach ($params as $key => $value) {
            $statement->bindValue(':' . $key, $value);
        }

        $statement->bindValue(':limit', (int) ($pagination['per_page'] ?? self::PAGE_SIZE), PDO::PARAM_INT);
        $statement->bindValue(':offset', (int) ($pagination['offset'] ?? 0), PDO::PARAM_INT);
    }

    private function findOne(string $sql, array $params): ?array
    {
        $statement = $this->connection->prepare($sql);
        $statement->execute($params);
        $row = $statement->fetch();

        return is_array($row) ? $row : null;
    }

    private function findAll(string $sql, array $params): array
    {
        $statement = $this->connection->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll() ?: [];
    }
}
