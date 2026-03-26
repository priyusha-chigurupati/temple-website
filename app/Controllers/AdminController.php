<?php

declare(strict_types=1);

final class AdminController
{
    public function __construct(
        private readonly AdminAuthService $auth,
        private readonly AdminDashboardRepository $dashboard,
        private readonly AdminEventRepository $events
    )
    {
    }

    public function login(): void
    {
        if ($this->auth->check()) {
            redirect_to(route_url('/admin'));
        }

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->handleLogin();
        }

        View::render('pages/admin-login', [
            'pageTitle' => 'Admin Login',
            'metaTitle' => 'Admin Login | AnkammaThalli Temple',
            'metaDescription' => 'Secure admin login for AnkammaThalli Temple.',
            'adminShellMode' => 'auth',
            'authState' => flash_pull('admin_auth_state', []),
            'authForm' => flash_pull('admin_auth_form', [
                'email' => '',
            ]),
        ], 'admin');
    }

    public function dashboard(): void
    {
        $user = $this->requireAuth();

        View::render('pages/admin-dashboard', [
            'pageTitle' => 'Admin Dashboard',
            'metaTitle' => 'Admin Dashboard | AnkammaThalli Temple',
            'metaDescription' => 'Admin dashboard for AnkammaThalli Temple.',
            'adminShellMode' => 'dashboard',
            'adminPage' => 'dashboard',
            'adminUser' => $user,
            'contentCounts' => $this->dashboard->contentCounts(),
            'totalContentCount' => $this->dashboard->totalContentCount(),
            'submissionCounts' => $this->dashboard->submissionCounts(),
            'donationSummary' => $this->dashboard->donationSummary(),
            'nextEventStatus' => $this->dashboard->nextEventStatus(),
            'upcomingEvents' => $this->dashboard->upcomingEvents(),
            'recentContactInquiries' => $this->dashboard->recentContactInquiries(),
            'recentDonationNotifications' => $this->dashboard->recentDonationNotifications(),
        ], 'admin');
    }

    public function logout(): never
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || ! csrf_is_valid($_POST['_csrf'] ?? null)) {
            http_response_code(403);
            exit('Invalid logout request.');
        }

        $this->auth->logout();
        flash_set('admin_auth_state', [
            'type' => 'success',
            'message' => 'You have been logged out.',
        ]);

        redirect_to(route_url('/admin/login'));
    }

    public function eventsIndex(): void
    {
        $user = $this->requireAuth();
        $items = $this->events->all();
        $listing = $this->events->listing([
            'phase' => query_value('phase'),
            'q' => query_value('q'),
            'page' => query_value('page'),
        ]);
        $phaseCounts = [
            'upcoming' => count(array_filter($items, static fn (array $item): bool => ($item['phase'] ?? '') === 'upcoming')),
            'active' => count(array_filter($items, static fn (array $item): bool => ($item['phase'] ?? '') === 'active')),
            'completed' => count(array_filter($items, static fn (array $item): bool => ($item['phase'] ?? '') === 'completed')),
            'draft' => count(array_filter($items, static fn (array $item): bool => ($item['status'] ?? '') === 'draft')),
        ];
        $nextEvent = $items[0] ?? null;

        View::render('pages/admin-events-index', [
            'pageTitle' => 'Manage Events',
            'metaTitle' => 'Manage Events | AnkammaThalli Temple',
            'metaDescription' => 'Manage temple events in the admin dashboard.',
            'adminShellMode' => 'dashboard',
            'adminPage' => 'events',
            'adminUser' => $user,
            'events' => $listing['items'],
            'phaseCounts' => $phaseCounts,
            'nextEvent' => $nextEvent,
            'eventState' => flash_pull('admin_event_state', []),
            'eventFilters' => $listing['filters'],
            'eventSearchTerm' => $listing['search_term'],
            'eventPagination' => $listing['pagination'],
            'eventSelectedPhase' => $listing['phase'],
            'eventTotalItems' => $listing['total_items'],
        ], 'admin');
    }

    public function eventsCreate(): void
    {
        $user = $this->requireAuth();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->handleEventSave(null);
        }

        View::render('pages/admin-event-form', [
            'pageTitle' => 'Add New Event',
            'metaTitle' => 'Add New Event | AnkammaThalli Temple',
            'metaDescription' => 'Create a new temple event in the admin dashboard.',
            'adminShellMode' => 'dashboard',
            'adminPage' => 'events',
            'adminUser' => $user,
            'eventFormMode' => 'create',
            'eventState' => flash_pull('admin_event_state', []),
            'eventForm' => flash_pull('admin_event_form', $this->emptyEventForm()),
            'eventId' => null,
        ], 'admin');
    }

    public function eventsEdit(int $id): void
    {
        $user = $this->requireAuth();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->handleEventSave($id);
        }

        $event = $this->events->find($id);

        if ($event === null) {
            http_response_code(404);
            $this->dashboard();
            return;
        }

        View::render('pages/admin-event-form', [
            'pageTitle' => 'Edit Event',
            'metaTitle' => 'Edit Event | AnkammaThalli Temple',
            'metaDescription' => 'Edit a temple event in the admin dashboard.',
            'adminShellMode' => 'dashboard',
            'adminPage' => 'events',
            'adminUser' => $user,
            'eventFormMode' => 'edit',
            'eventState' => flash_pull('admin_event_state', []),
            'eventForm' => flash_pull('admin_event_form', $event),
            'eventId' => $id,
        ], 'admin');
    }

    public function eventsDelete(int $id): never
    {
        $this->requireAuth();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || ! csrf_is_valid($_POST['_csrf'] ?? null)) {
            http_response_code(403);
            exit('Invalid delete request.');
        }

        if ($this->events->delete($id)) {
            flash_set('admin_event_state', [
                'type' => 'success',
                'message' => 'The event was removed successfully.',
            ]);
        } else {
            flash_set('admin_event_state', [
                'type' => 'error',
                'message' => 'The event could not be removed.',
            ]);
        }

        redirect_to(route_url('/admin/events'));
    }

    private function handleLogin(): never
    {
        if (! csrf_is_valid($_POST['_csrf'] ?? null)) {
            flash_set('admin_auth_state', [
                'type' => 'error',
                'message' => 'Your session expired. Please try again.',
            ]);
            flash_set('admin_auth_form', [
                'email' => trim((string) ($_POST['email'] ?? '')),
            ]);
            redirect_to(route_url('/admin/login'));
        }

        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($email === '' || $password === '') {
            flash_set('admin_auth_state', [
                'type' => 'error',
                'message' => 'Please enter both email and password.',
            ]);
            flash_set('admin_auth_form', ['email' => $email]);
            redirect_to(route_url('/admin/login'));
        }

        if (! $this->auth->attempt($email, $password)) {
            flash_set('admin_auth_state', [
                'type' => 'error',
                'message' => 'The admin credentials are incorrect.',
            ]);
            flash_set('admin_auth_form', ['email' => $email]);
            redirect_to(route_url('/admin/login'));
        }

        redirect_to(route_url('/admin'));
    }

    private function requireAuth(): array
    {
        $user = $this->auth->currentUser();

        if ($user === null) {
            flash_set('admin_auth_state', [
                'type' => 'error',
                'message' => 'Please log in to access the admin area.',
            ]);
            redirect_to(route_url('/admin/login'));
        }

        return $user;
    }

    private function handleEventSave(?int $id): never
    {
        if (! csrf_is_valid($_POST['_csrf'] ?? null)) {
            flash_set('admin_event_state', [
                'type' => 'error',
                'message' => 'Your session expired. Please try again.',
            ]);
            flash_set('admin_event_form', $this->eventFormFromPost($_POST));
            redirect_to($id === null ? route_url('/admin/events/new') : route_url('/admin/events/' . $id . '/edit'));
        }

        $result = $this->events->save($_POST, $id);

        if (! ($result['ok'] ?? false)) {
            flash_set('admin_event_state', [
                'type' => 'error',
                'message' => implode(' ', $result['errors'] ?? ['The event could not be saved.']),
            ]);
            flash_set('admin_event_form', $result['old'] ?? $this->eventFormFromPost($_POST));
            redirect_to($id === null ? route_url('/admin/events/new') : route_url('/admin/events/' . $id . '/edit'));
        }

        flash_set('admin_event_state', [
            'type' => 'success',
            'message' => $id === null ? 'The event was created successfully.' : 'The event was updated successfully.',
        ]);

        redirect_to(route_url('/admin/events'));
    }

    private function emptyEventForm(): array
    {
        return [
            'title' => '',
            'slug' => '',
            'starts_at' => '',
            'ends_at' => '',
            'schedule_label' => '',
            'summary' => '',
            'body_long' => '',
            'image_path' => '',
            'sort_order' => 0,
            'status' => 'draft',
            'is_featured_home' => 0,
        ];
    }

    private function eventFormFromPost(array $post): array
    {
        return [
            'title' => trim((string) ($post['title'] ?? '')),
            'slug' => trim((string) ($post['slug'] ?? '')),
            'starts_at' => trim((string) ($post['starts_at'] ?? '')),
            'ends_at' => trim((string) ($post['ends_at'] ?? '')),
            'schedule_label' => trim((string) ($post['schedule_label'] ?? '')),
            'summary' => trim((string) ($post['summary'] ?? '')),
            'body_long' => trim((string) ($post['body_long'] ?? '')),
            'image_path' => trim((string) ($post['image_path'] ?? '')),
            'sort_order' => trim((string) ($post['sort_order'] ?? '0')),
            'status' => trim((string) ($post['status'] ?? 'draft')),
            'is_featured_home' => isset($post['is_featured_home']) ? 1 : 0,
        ];
    }
}
