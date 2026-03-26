<?php

declare(strict_types=1);

final class AdminController
{
    public function __construct(
        private readonly AdminAuthService $auth,
        private readonly AdminDashboardRepository $dashboard
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
}
