<?php

declare(strict_types=1);

final class AdminController
{
    public function __construct(
        private readonly AdminAuthService $auth,
        private readonly AdminDashboardRepository $dashboard,
        private readonly AdminEventRepository $events,
        private readonly AdminGalleryRepository $gallery,
        private readonly AdminBlogRepository $blog,
        private readonly AdminPageRepository $pages,
        private readonly AdminSettingsRepository $settings,
        private readonly AdminAccountRepository $account,
        private readonly AdminSubmissionRepository $submissions
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

    public function forgotPassword(): void
    {
        if ($this->auth->check()) {
            redirect_to(route_url('/admin'));
        }

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->handleForgotPassword();
        }

        View::render('pages/admin-forgot-password', [
            'pageTitle' => 'Forgot Password',
            'metaTitle' => 'Forgot Password | AnkammaThalli Temple',
            'metaDescription' => 'Request an admin password reset for AnkammaThalli Temple.',
            'adminShellMode' => 'auth',
            'authState' => flash_pull('admin_auth_state', []),
            'authForm' => flash_pull('admin_auth_form', ['email' => '']),
            'resetLink' => flash_pull('admin_reset_link'),
        ], 'admin');
    }

    public function resetPassword(): void
    {
        if ($this->auth->check()) {
            redirect_to(route_url('/admin'));
        }

        $token = trim((string) query_value('token'));

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $token = trim((string) ($_POST['token'] ?? $token));
            $this->handleResetPassword($token);
        }

        $context = $this->account->resetContext($token);

        View::render('pages/admin-reset-password', [
            'pageTitle' => 'Reset Password',
            'metaTitle' => 'Reset Password | AnkammaThalli Temple',
            'metaDescription' => 'Reset the admin password for AnkammaThalli Temple.',
            'adminShellMode' => 'auth',
            'authState' => flash_pull('admin_auth_state', []),
            'resetForm' => flash_pull('admin_reset_form', []),
            'resetToken' => $token,
            'resetContext' => $context,
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

    public function accountProfile(): void
    {
        $user = $this->requireAuth();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->handleAccountProfileSave($user);
        }

        View::render('pages/admin-account-form', [
            'pageTitle' => 'Admin Account',
            'metaTitle' => 'Admin Account | AnkammaThalli Temple',
            'metaDescription' => 'Manage the admin profile and account details.',
            'adminShellMode' => 'dashboard',
            'adminPage' => 'account',
            'adminUser' => $user,
            'accountState' => flash_pull('admin_account_state', []),
            'accountForm' => flash_pull('admin_account_form', $this->account->profile((int) $user['id']) ?? []),
        ], 'admin');
    }

    public function accountPassword(): void
    {
        $user = $this->requireAuth();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->handleAccountPasswordSave($user);
        }

        View::render('pages/admin-account-password-form', [
            'pageTitle' => 'Change Password',
            'metaTitle' => 'Change Password | AnkammaThalli Temple',
            'metaDescription' => 'Change the admin password and secure the temple dashboard.',
            'adminShellMode' => 'dashboard',
            'adminPage' => 'account',
            'adminUser' => $user,
            'accountPasswordState' => flash_pull('admin_account_password_state', []),
        ], 'admin');
    }

    public function submissionsIndex(): void
    {
        $user = $this->requireAuth();

        View::render('pages/admin-submissions-index', [
            'pageTitle' => 'Submission Center',
            'metaTitle' => 'Submission Center | AnkammaThalli Temple',
            'metaDescription' => 'Review contact messages, donation notices, gallery submissions, and newsletter signups.',
            'adminShellMode' => 'dashboard',
            'adminPage' => 'submissions',
            'adminUser' => $user,
            'submissionState' => flash_pull('admin_submission_state', []),
            'submissionCounts' => $this->submissions->overviewCounts(),
        ], 'admin');
    }

    public function submissionsContactIndex(): void
    {
        $user = $this->requireAuth();
        $listing = $this->submissions->contactListing([
            'status' => query_value('status'),
            'q' => query_value('q'),
            'page' => query_value('page'),
        ]);

        View::render('pages/admin-submissions-contact-index', [
            'pageTitle' => 'Contact Inquiries',
            'metaTitle' => 'Contact Inquiries | AnkammaThalli Temple',
            'metaDescription' => 'Review contact inquiries sent from the public Contact page.',
            'adminShellMode' => 'dashboard',
            'adminPage' => 'submissions',
            'adminUser' => $user,
            'submissionState' => flash_pull('admin_submission_state', []),
            'contactItems' => $listing['items'],
            'contactFilters' => $listing['filters'],
            'contactStatus' => $listing['status'],
            'contactSearchTerm' => $listing['search_term'],
            'contactPagination' => $listing['pagination'],
            'contactTotalItems' => $listing['total_items'],
        ], 'admin');
    }

    public function submissionsContactShow(int $id): void
    {
        $user = $this->requireAuth();
        $item = $this->submissions->contactFind($id);

        if ($item === null) {
            http_response_code(404);
            $this->dashboard();
            return;
        }

        View::render('pages/admin-submissions-contact-show', [
            'pageTitle' => 'View Contact Inquiry',
            'metaTitle' => 'View Contact Inquiry | AnkammaThalli Temple',
            'metaDescription' => 'View a contact inquiry in the admin dashboard.',
            'adminShellMode' => 'dashboard',
            'adminPage' => 'submissions',
            'adminUser' => $user,
            'submissionState' => flash_pull('admin_submission_state', []),
            'contactItem' => $item,
        ], 'admin');
    }

    public function submissionsContactReview(int $id): never
    {
        $this->requireAuth();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || ! csrf_is_valid($_POST['_csrf'] ?? null)) {
            http_response_code(403);
            exit('Invalid review request.');
        }

        $this->submissions->markContactReviewed($id);
        flash_set('admin_submission_state', [
            'type' => 'success',
            'message' => 'The contact inquiry was marked as reviewed.',
        ]);

        redirect_to(route_url('/admin/submissions/contact'));
    }

    public function submissionsDonationsIndex(): void
    {
        $user = $this->requireAuth();
        $listing = $this->submissions->donationListing([
            'status' => query_value('status'),
            'q' => query_value('q'),
            'page' => query_value('page'),
        ]);

        View::render('pages/admin-submissions-donations-index', [
            'pageTitle' => 'Donation Notices',
            'metaTitle' => 'Donation Notices | AnkammaThalli Temple',
            'metaDescription' => 'Review donation notifications sent from the public Donations page.',
            'adminShellMode' => 'dashboard',
            'adminPage' => 'submissions',
            'adminUser' => $user,
            'submissionState' => flash_pull('admin_submission_state', []),
            'donationItems' => $listing['items'],
            'donationFilters' => $listing['filters'],
            'donationStatus' => $listing['status'],
            'donationSearchTerm' => $listing['search_term'],
            'donationPagination' => $listing['pagination'],
            'donationTotalItems' => $listing['total_items'],
        ], 'admin');
    }

    public function submissionsDonationShow(int $id): void
    {
        $user = $this->requireAuth();
        $item = $this->submissions->donationFind($id);

        if ($item === null) {
            http_response_code(404);
            $this->dashboard();
            return;
        }

        View::render('pages/admin-submissions-donation-show', [
            'pageTitle' => 'View Donation Notice',
            'metaTitle' => 'View Donation Notice | AnkammaThalli Temple',
            'metaDescription' => 'View a donation notice in the admin dashboard.',
            'adminShellMode' => 'dashboard',
            'adminPage' => 'submissions',
            'adminUser' => $user,
            'submissionState' => flash_pull('admin_submission_state', []),
            'donationItem' => $item,
        ], 'admin');
    }

    public function submissionsDonationReview(int $id): never
    {
        $this->requireAuth();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || ! csrf_is_valid($_POST['_csrf'] ?? null)) {
            http_response_code(403);
            exit('Invalid review request.');
        }

        $this->submissions->markDonationReviewed($id);
        flash_set('admin_submission_state', [
            'type' => 'success',
            'message' => 'The donation notice was marked as reviewed.',
        ]);

        redirect_to(route_url('/admin/submissions/donations'));
    }

    public function submissionsGalleryIndex(): void
    {
        $user = $this->requireAuth();
        $listing = $this->submissions->galleryListing([
            'status' => query_value('status'),
            'q' => query_value('q'),
            'page' => query_value('page'),
        ]);

        View::render('pages/admin-submissions-gallery-index', [
            'pageTitle' => 'Gallery Submissions',
            'metaTitle' => 'Gallery Submissions | AnkammaThalli Temple',
            'metaDescription' => 'Review public photo submissions before they appear in the temple gallery.',
            'adminShellMode' => 'dashboard',
            'adminPage' => 'submissions',
            'adminUser' => $user,
            'submissionState' => flash_pull('admin_submission_state', []),
            'gallerySubmissionItems' => $listing['items'],
            'gallerySubmissionFilters' => $listing['filters'],
            'gallerySubmissionStatus' => $listing['status'],
            'gallerySubmissionSearchTerm' => $listing['search_term'],
            'gallerySubmissionPagination' => $listing['pagination'],
            'gallerySubmissionTotalItems' => $listing['total_items'],
        ], 'admin');
    }

    public function submissionsGalleryShow(int $id): void
    {
        $user = $this->requireAuth();
        $item = $this->submissions->galleryFind($id);

        if ($item === null) {
            http_response_code(404);
            $this->dashboard();
            return;
        }

        View::render('pages/admin-submissions-gallery-show', [
            'pageTitle' => 'Review Gallery Submission',
            'metaTitle' => 'Review Gallery Submission | AnkammaThalli Temple',
            'metaDescription' => 'Review a public gallery submission in the admin dashboard.',
            'adminShellMode' => 'dashboard',
            'adminPage' => 'submissions',
            'adminUser' => $user,
            'submissionState' => flash_pull('admin_submission_state', []),
            'gallerySubmissionItem' => $item,
            'galleryReviewForm' => flash_pull('admin_gallery_review_form', [
                'status' => (string) ($item['status'] ?? 'pending'),
                'review_notes' => (string) ($item['review_notes'] ?? ''),
            ]),
        ], 'admin');
    }

    public function submissionsGalleryReview(int $id): never
    {
        $user = $this->requireAuth();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || ! csrf_is_valid($_POST['_csrf'] ?? null)) {
            http_response_code(403);
            exit('Invalid review request.');
        }

        $result = $this->submissions->reviewGallery($id, (int) $user['id'], $_POST);

        if (! ($result['ok'] ?? false)) {
            flash_set('admin_submission_state', [
                'type' => 'error',
                'message' => implode(' ', $result['errors'] ?? ['The gallery submission could not be reviewed.']),
            ]);
            flash_set('admin_gallery_review_form', $result['old'] ?? []);
            redirect_to(route_url('/admin/submissions/gallery/' . $id));
        }

        flash_set('admin_submission_state', [
            'type' => 'success',
            'message' => 'The gallery submission review was saved successfully.',
        ]);

        redirect_to(route_url('/admin/submissions/gallery'));
    }

    public function submissionsNewsletterIndex(): void
    {
        $user = $this->requireAuth();
        $listing = $this->submissions->newsletterListing([
            'status' => query_value('status'),
            'q' => query_value('q'),
            'page' => query_value('page'),
        ]);

        View::render('pages/admin-submissions-newsletter-index', [
            'pageTitle' => 'Newsletter Subscribers',
            'metaTitle' => 'Newsletter Subscribers | AnkammaThalli Temple',
            'metaDescription' => 'Review newsletter subscribers collected from the public website.',
            'adminShellMode' => 'dashboard',
            'adminPage' => 'submissions',
            'adminUser' => $user,
            'submissionState' => flash_pull('admin_submission_state', []),
            'newsletterItems' => $listing['items'],
            'newsletterFilters' => $listing['filters'],
            'newsletterStatus' => $listing['status'],
            'newsletterSearchTerm' => $listing['search_term'],
            'newsletterPagination' => $listing['pagination'],
            'newsletterTotalItems' => $listing['total_items'],
        ], 'admin');
    }

    public function submissionsNewsletterActivate(int $id): never
    {
        $this->handleNewsletterStatusChange($id, 'active');
    }

    public function submissionsNewsletterUnsubscribe(int $id): never
    {
        $this->handleNewsletterStatusChange($id, 'unsubscribed');
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

    public function galleryIndex(): void
    {
        $user = $this->requireAuth();
        $listing = $this->gallery->listing([
            'status' => query_value('status'),
            'category' => query_value('category'),
            'q' => query_value('q'),
            'page' => query_value('page'),
        ]);

        View::render('pages/admin-gallery-index', [
            'pageTitle' => 'Media Library',
            'metaTitle' => 'Media Library | AnkammaThalli Temple',
            'metaDescription' => 'Manage temple gallery media in the admin dashboard.',
            'adminShellMode' => 'dashboard',
            'adminPage' => 'media',
            'adminUser' => $user,
            'galleryItems' => $listing['items'],
            'galleryFilters' => $listing['filters'],
            'galleryCategoryFilters' => $listing['category_filters'],
            'galleryPagination' => $listing['pagination'],
            'gallerySearchTerm' => $listing['search_term'],
            'gallerySelectedStatus' => $listing['status'],
            'gallerySelectedCategory' => $listing['category'],
            'galleryTotalItems' => $listing['total_items'],
            'galleryState' => flash_pull('admin_gallery_state', []),
        ], 'admin');
    }

    public function galleryCreate(): void
    {
        $user = $this->requireAuth();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->handleGallerySave(null);
        }

        View::render('pages/admin-gallery-form', [
            'pageTitle' => 'Add Media Item',
            'metaTitle' => 'Add Media Item | AnkammaThalli Temple',
            'metaDescription' => 'Create a new gallery item in the admin dashboard.',
            'adminShellMode' => 'dashboard',
            'adminPage' => 'media',
            'adminUser' => $user,
            'galleryFormMode' => 'create',
            'galleryState' => flash_pull('admin_gallery_state', []),
            'galleryForm' => flash_pull('admin_gallery_form', $this->emptyGalleryForm()),
            'galleryCategories' => $this->gallery->categories(),
            'galleryItemId' => null,
        ], 'admin');
    }

    public function galleryEdit(int $id): void
    {
        $user = $this->requireAuth();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->handleGallerySave($id);
        }

        $item = $this->gallery->find($id);

        if ($item === null) {
            http_response_code(404);
            $this->dashboard();
            return;
        }

        View::render('pages/admin-gallery-form', [
            'pageTitle' => 'Edit Media Item',
            'metaTitle' => 'Edit Media Item | AnkammaThalli Temple',
            'metaDescription' => 'Edit a gallery item in the admin dashboard.',
            'adminShellMode' => 'dashboard',
            'adminPage' => 'media',
            'adminUser' => $user,
            'galleryFormMode' => 'edit',
            'galleryState' => flash_pull('admin_gallery_state', []),
            'galleryForm' => flash_pull('admin_gallery_form', $item),
            'galleryCategories' => $this->gallery->categories(),
            'galleryItemId' => $id,
        ], 'admin');
    }

    public function galleryDelete(int $id): never
    {
        $this->requireAuth();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || ! csrf_is_valid($_POST['_csrf'] ?? null)) {
            http_response_code(403);
            exit('Invalid delete request.');
        }

        if ($this->gallery->delete($id)) {
            flash_set('admin_gallery_state', [
                'type' => 'success',
                'message' => 'The gallery item was removed successfully.',
            ]);
        } else {
            flash_set('admin_gallery_state', [
                'type' => 'error',
                'message' => 'The gallery item could not be removed.',
            ]);
        }

        redirect_to(route_url('/admin/media'));
    }

    public function blogIndex(): void
    {
        $user = $this->requireAuth();
        $items = $this->blog->all();
        $listing = $this->blog->listing([
            'status' => query_value('status'),
            'category' => query_value('category'),
            'q' => query_value('q'),
            'page' => query_value('page'),
        ]);

        View::render('pages/admin-blog-index', [
            'pageTitle' => 'Blog Library',
            'metaTitle' => 'Blog Library | AnkammaThalli Temple',
            'metaDescription' => 'Manage temple blog articles in the admin dashboard.',
            'adminShellMode' => 'dashboard',
            'adminPage' => 'blog',
            'adminUser' => $user,
            'blogPosts' => $listing['items'],
            'blogState' => flash_pull('admin_blog_state', []),
            'blogFilters' => $listing['filters'],
            'blogCategoryFilters' => $listing['category_filters'],
            'blogPagination' => $listing['pagination'],
            'blogSearchTerm' => $listing['search_term'],
            'blogSelectedStatus' => $listing['status'],
            'blogSelectedCategory' => $listing['category'],
            'blogTotalItems' => $listing['total_items'],
            'blogStats' => [
                'published' => count(array_filter($items, static fn (array $item): bool => ($item['status'] ?? '') === 'published')),
                'draft' => count(array_filter($items, static fn (array $item): bool => ($item['status'] ?? '') === 'draft')),
                'featured' => count(array_filter($items, static fn (array $item): bool => (int) ($item['is_featured'] ?? 0) === 1)),
            ],
            'latestPost' => $items[0] ?? null,
        ], 'admin');
    }

    public function blogCreate(): void
    {
        $user = $this->requireAuth();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->handleBlogSave(null);
        }

        View::render('pages/admin-blog-form', [
            'pageTitle' => 'Add Blog Article',
            'metaTitle' => 'Add Blog Article | AnkammaThalli Temple',
            'metaDescription' => 'Create a new temple blog article in the admin dashboard.',
            'adminShellMode' => 'dashboard',
            'adminPage' => 'blog',
            'adminUser' => $user,
            'blogFormMode' => 'create',
            'blogState' => flash_pull('admin_blog_state', []),
            'blogForm' => flash_pull('admin_blog_form', $this->emptyBlogForm()),
            'blogCategories' => $this->blog->categories(),
            'blogPostId' => null,
        ], 'admin');
    }

    public function blogEdit(int $id): void
    {
        $user = $this->requireAuth();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->handleBlogSave($id);
        }

        $post = $this->blog->find($id);

        if ($post === null) {
            http_response_code(404);
            $this->dashboard();
            return;
        }

        View::render('pages/admin-blog-form', [
            'pageTitle' => 'Edit Blog Article',
            'metaTitle' => 'Edit Blog Article | AnkammaThalli Temple',
            'metaDescription' => 'Edit a temple blog article in the admin dashboard.',
            'adminShellMode' => 'dashboard',
            'adminPage' => 'blog',
            'adminUser' => $user,
            'blogFormMode' => 'edit',
            'blogState' => flash_pull('admin_blog_state', []),
            'blogForm' => flash_pull('admin_blog_form', $post),
            'blogCategories' => $this->blog->categories(),
            'blogPostId' => $id,
        ], 'admin');
    }

    public function blogDelete(int $id): never
    {
        $this->requireAuth();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || ! csrf_is_valid($_POST['_csrf'] ?? null)) {
            http_response_code(403);
            exit('Invalid delete request.');
        }

        if ($this->blog->delete($id)) {
            flash_set('admin_blog_state', [
                'type' => 'success',
                'message' => 'The article was removed successfully.',
            ]);
        } else {
            flash_set('admin_blog_state', [
                'type' => 'error',
                'message' => 'The article could not be removed.',
            ]);
        }

        redirect_to(route_url('/admin/blog'));
    }

    public function pagesIndex(): void
    {
        $user = $this->requireAuth();

        View::render('pages/admin-pages-index', [
            'pageTitle' => 'Website Pages',
            'metaTitle' => 'Website Pages | AnkammaThalli Temple',
            'metaDescription' => 'Manage page content in the admin dashboard.',
            'adminShellMode' => 'dashboard',
            'adminPage' => 'pages',
            'adminUser' => $user,
            'pagesState' => flash_pull('admin_pages_state', []),
            'pagesList' => $this->pages->pages(),
        ], 'admin');
    }

    public function pagesHome(): void
    {
        $user = $this->requireAuth();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->handleHomePageSave();
        }

        $home = $this->pages->homeEditor();

        if ($home === null) {
            http_response_code(404);
            $this->dashboard();
            return;
        }

        View::render('pages/admin-page-home-form', [
            'pageTitle' => 'Edit Home Page',
            'metaTitle' => 'Edit Home Page | AnkammaThalli Temple',
            'metaDescription' => 'Edit the Home page content in the admin dashboard.',
            'adminShellMode' => 'dashboard',
            'adminPage' => 'pages',
            'adminUser' => $user,
            'pageEditorMode' => 'home',
            'pageState' => flash_pull('admin_pages_state', []),
            'homeForm' => flash_pull('admin_home_form', $home),
        ], 'admin');
    }

    public function pagesAbout(): void
    {
        $user = $this->requireAuth();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->handleAboutPageSave();
        }

        $about = $this->pages->aboutEditor();

        if ($about === null) {
            http_response_code(404);
            $this->dashboard();
            return;
        }

        View::render('pages/admin-page-about-form', [
            'pageTitle' => 'Edit About Page',
            'metaTitle' => 'Edit About Page | AnkammaThalli Temple',
            'metaDescription' => 'Edit the About page content in the admin dashboard.',
            'adminShellMode' => 'dashboard',
            'adminPage' => 'pages',
            'adminUser' => $user,
            'pageEditorMode' => 'about',
            'pageState' => flash_pull('admin_pages_state', []),
            'aboutForm' => flash_pull('admin_about_form', $about),
        ], 'admin');
    }

    public function pagesContact(): void
    {
        $user = $this->requireAuth();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->handleContactPageSave();
        }

        $contact = $this->pages->contactEditor();

        if ($contact === null) {
            http_response_code(404);
            $this->dashboard();
            return;
        }

        View::render('pages/admin-page-contact-form', [
            'pageTitle' => 'Edit Contact Page',
            'metaTitle' => 'Edit Contact Page | AnkammaThalli Temple',
            'metaDescription' => 'Edit the Contact page content in the admin dashboard.',
            'adminShellMode' => 'dashboard',
            'adminPage' => 'pages',
            'adminUser' => $user,
            'pageEditorMode' => 'contact',
            'pageState' => flash_pull('admin_pages_state', []),
            'contactForm' => flash_pull('admin_contact_form', $contact),
        ], 'admin');
    }

    public function pagesDonations(): void
    {
        $user = $this->requireAuth();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->handleDonationsPageSave();
        }

        $donations = $this->pages->donationsEditor();

        if ($donations === null) {
            http_response_code(404);
            $this->dashboard();
            return;
        }

        View::render('pages/admin-page-donations-form', [
            'pageTitle' => 'Edit Donations Page',
            'metaTitle' => 'Edit Donations Page | AnkammaThalli Temple',
            'metaDescription' => 'Edit the Donations page content in the admin dashboard.',
            'adminShellMode' => 'dashboard',
            'adminPage' => 'pages',
            'adminUser' => $user,
            'pageEditorMode' => 'donations',
            'pageState' => flash_pull('admin_pages_state', []),
            'donationsForm' => flash_pull('admin_donations_form', $donations),
        ], 'admin');
    }

    public function settingsIndex(): void
    {
        $user = $this->requireAuth();

        View::render('pages/admin-settings-index', [
            'pageTitle' => 'Global Settings',
            'metaTitle' => 'Global Settings | AnkammaThalli Temple',
            'metaDescription' => 'Manage global temple website settings in the admin dashboard.',
            'adminShellMode' => 'dashboard',
            'adminPage' => 'settings',
            'adminUser' => $user,
            'settingsState' => flash_pull('admin_settings_state', []),
            'settingsItems' => $this->settings->settingsItems(),
        ], 'admin');
    }

    public function settingsFooter(): void
    {
        $user = $this->requireAuth();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->handleFooterSettingsSave();
        }

        View::render('pages/admin-settings-footer-form', [
            'pageTitle' => 'Edit Footer Settings',
            'metaTitle' => 'Edit Footer Settings | AnkammaThalli Temple',
            'metaDescription' => 'Edit the global footer settings in the admin dashboard.',
            'adminShellMode' => 'dashboard',
            'adminPage' => 'settings',
            'adminUser' => $user,
            'settingsState' => flash_pull('admin_settings_state', []),
            'footerForm' => flash_pull('admin_footer_form', $this->settings->footerEditor()),
        ], 'admin');
    }

    public function settingsGeneral(): void
    {
        $user = $this->requireAuth();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->handleGeneralSettingsSave();
        }

        View::render('pages/admin-settings-general-form', [
            'pageTitle' => 'Edit General Settings',
            'metaTitle' => 'Edit General Settings | AnkammaThalli Temple',
            'metaDescription' => 'Edit the global site settings in the admin dashboard.',
            'adminShellMode' => 'dashboard',
            'adminPage' => 'settings',
            'adminUser' => $user,
            'settingsState' => flash_pull('admin_settings_state', []),
            'generalForm' => flash_pull('admin_general_form', $this->settings->generalEditor()),
        ], 'admin');
    }

    public function settingsHeader(): void
    {
        $user = $this->requireAuth();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->handleHeaderSettingsSave();
        }

        View::render('pages/admin-settings-header-form', [
            'pageTitle' => 'Edit Header Settings',
            'metaTitle' => 'Edit Header Settings | AnkammaThalli Temple',
            'metaDescription' => 'Edit the global header settings in the admin dashboard.',
            'adminShellMode' => 'dashboard',
            'adminPage' => 'settings',
            'adminUser' => $user,
            'settingsState' => flash_pull('admin_settings_state', []),
            'headerForm' => flash_pull('admin_header_form', $this->settings->headerEditor()),
        ], 'admin');
    }

    public function settingsSeo(): void
    {
        $user = $this->requireAuth();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->handleSeoSettingsSave();
        }

        View::render('pages/admin-settings-seo-form', [
            'pageTitle' => 'Edit SEO Settings',
            'metaTitle' => 'Edit SEO Settings | AnkammaThalli Temple',
            'metaDescription' => 'Edit the global SEO defaults in the admin dashboard.',
            'adminShellMode' => 'dashboard',
            'adminPage' => 'settings',
            'adminUser' => $user,
            'settingsState' => flash_pull('admin_settings_state', []),
            'seoForm' => flash_pull('admin_seo_form', $this->settings->seoEditor()),
        ], 'admin');
    }

    public function settingsNotifications(): void
    {
        $user = $this->requireAuth();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->handleNotificationsSettingsSave();
        }

        View::render('pages/admin-settings-notifications-form', [
            'pageTitle' => 'Edit Notification Settings',
            'metaTitle' => 'Edit Notification Settings | AnkammaThalli Temple',
            'metaDescription' => 'Manage future mail delivery and admin notification recipients.',
            'adminShellMode' => 'dashboard',
            'adminPage' => 'settings',
            'adminUser' => $user,
            'settingsState' => flash_pull('admin_settings_state', []),
            'notificationsForm' => flash_pull('admin_notifications_form', $this->settings->notificationsEditor()),
        ], 'admin');
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

    private function handleForgotPassword(): never
    {
        if (! csrf_is_valid($_POST['_csrf'] ?? null)) {
            flash_set('admin_auth_state', [
                'type' => 'error',
                'message' => 'Your session expired. Please try again.',
            ]);
            flash_set('admin_auth_form', [
                'email' => trim((string) ($_POST['email'] ?? '')),
            ]);
            redirect_to(route_url('/admin/forgot-password'));
        }

        $result = $this->account->createResetRequest((string) ($_POST['email'] ?? ''));

        if (! ($result['ok'] ?? false)) {
            flash_set('admin_auth_state', [
                'type' => 'error',
                'message' => implode(' ', $result['errors'] ?? ['The reset request could not be completed.']),
            ]);
            flash_set('admin_auth_form', $result['old'] ?? ['email' => '']);
            redirect_to(route_url('/admin/forgot-password'));
        }

        flash_set('admin_auth_state', [
            'type' => 'success',
            'message' => 'If that admin email exists, a password reset link is now ready for the next step.',
        ]);
        flash_set('admin_auth_form', ['email' => (string) ($result['email'] ?? '')]);

        if (($result['reset_link'] ?? null) !== null && (Env::get('APP_ENV', 'local') ?? 'local') === 'local') {
            flash_set('admin_reset_link', (string) $result['reset_link']);
        }

        redirect_to(route_url('/admin/forgot-password'));
    }

    private function handleResetPassword(string $token): never
    {
        if (! csrf_is_valid($_POST['_csrf'] ?? null)) {
            flash_set('admin_auth_state', [
                'type' => 'error',
                'message' => 'Your session expired. Please try again.',
            ]);
            flash_set('admin_reset_form', []);
            redirect_to(route_url('/admin/reset-password?token=' . rawurlencode($token)));
        }

        $result = $this->account->resetPassword($token, $_POST);

        if (! ($result['ok'] ?? false)) {
            flash_set('admin_auth_state', [
                'type' => 'error',
                'message' => implode(' ', $result['errors'] ?? ['The password could not be reset.']),
            ]);
            flash_set('admin_reset_form', $result['old'] ?? []);
            redirect_to(route_url('/admin/reset-password?token=' . rawurlencode($token)));
        }

        flash_set('admin_auth_state', [
            'type' => 'success',
            'message' => 'Your password has been reset. Please sign in with the new password.',
        ]);

        redirect_to(route_url('/admin/login'));
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

    private function handleNewsletterStatusChange(int $id, string $status): never
    {
        $this->requireAuth();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || ! csrf_is_valid($_POST['_csrf'] ?? null)) {
            http_response_code(403);
            exit('Invalid newsletter status request.');
        }

        if (! $this->submissions->updateNewsletterStatus($id, $status)) {
            flash_set('admin_submission_state', [
                'type' => 'error',
                'message' => 'The newsletter subscription status could not be updated.',
            ]);
            redirect_to(route_url('/admin/submissions/newsletter'));
        }

        flash_set('admin_submission_state', [
            'type' => 'success',
            'message' => $status === 'active'
                ? 'The newsletter subscription was reactivated.'
                : 'The newsletter subscription was marked as unsubscribed.',
        ]);

        redirect_to(route_url('/admin/submissions/newsletter'));
    }

    private function handleAccountProfileSave(array $user): never
    {
        if (! csrf_is_valid($_POST['_csrf'] ?? null)) {
            flash_set('admin_account_state', [
                'type' => 'error',
                'message' => 'Your session expired. Please try again.',
            ]);
            flash_set('admin_account_form', $this->account->profile((int) $user['id']) ?? []);
            redirect_to(route_url('/admin/account'));
        }

        $result = $this->account->saveProfile((int) $user['id'], $_POST);

        if (! ($result['ok'] ?? false)) {
            flash_set('admin_account_state', [
                'type' => 'error',
                'message' => implode(' ', $result['errors'] ?? ['The admin profile could not be updated.']),
            ]);
            flash_set('admin_account_form', $result['old'] ?? []);
            redirect_to(route_url('/admin/account'));
        }

        $updated = $this->account->profile((int) $user['id']);

        if (is_array($updated)) {
            $this->auth->syncProfile((int) $user['id'], (string) $updated['name'], (string) $updated['email']);
        }

        flash_set('admin_account_state', [
            'type' => 'success',
            'message' => 'The admin profile was updated successfully.',
        ]);

        redirect_to(route_url('/admin/account'));
    }

    private function handleAccountPasswordSave(array $user): never
    {
        if (! csrf_is_valid($_POST['_csrf'] ?? null)) {
            flash_set('admin_account_password_state', [
                'type' => 'error',
                'message' => 'Your session expired. Please try again.',
            ]);
            redirect_to(route_url('/admin/account/password'));
        }

        $result = $this->account->changePassword((int) $user['id'], $_POST);

        if (! ($result['ok'] ?? false)) {
            flash_set('admin_account_password_state', [
                'type' => 'error',
                'message' => implode(' ', $result['errors'] ?? ['The password could not be updated.']),
            ]);
            redirect_to(route_url('/admin/account/password'));
        }

        flash_set('admin_account_password_state', [
            'type' => 'success',
            'message' => 'The password was updated successfully.',
        ]);

        redirect_to(route_url('/admin/account/password'));
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

    private function handleGallerySave(?int $id): never
    {
        if (! csrf_is_valid($_POST['_csrf'] ?? null)) {
            flash_set('admin_gallery_state', [
                'type' => 'error',
                'message' => 'Your session expired. Please try again.',
            ]);
            flash_set('admin_gallery_form', $this->galleryFormFromPost($_POST));
            redirect_to($id === null ? route_url('/admin/media/new') : route_url('/admin/media/' . $id . '/edit'));
        }

        $result = $this->gallery->save($_POST, $id);

        if (! ($result['ok'] ?? false)) {
            flash_set('admin_gallery_state', [
                'type' => 'error',
                'message' => implode(' ', $result['errors'] ?? ['The gallery item could not be saved.']),
            ]);
            flash_set('admin_gallery_form', $result['old'] ?? $this->galleryFormFromPost($_POST));
            redirect_to($id === null ? route_url('/admin/media/new') : route_url('/admin/media/' . $id . '/edit'));
        }

        flash_set('admin_gallery_state', [
            'type' => 'success',
            'message' => $id === null ? 'The gallery item was created successfully.' : 'The gallery item was updated successfully.',
        ]);

        redirect_to(route_url('/admin/media'));
    }

    private function handleBlogSave(?int $id): never
    {
        if (! csrf_is_valid($_POST['_csrf'] ?? null)) {
            flash_set('admin_blog_state', [
                'type' => 'error',
                'message' => 'Your session expired. Please try again.',
            ]);
            flash_set('admin_blog_form', $this->blogFormFromPost($_POST));
            redirect_to($id === null ? route_url('/admin/blog/new') : route_url('/admin/blog/' . $id . '/edit'));
        }

        $result = $this->blog->save($_POST, $id);

        if (! ($result['ok'] ?? false)) {
            flash_set('admin_blog_state', [
                'type' => 'error',
                'message' => implode(' ', $result['errors'] ?? ['The article could not be saved.']),
            ]);
            flash_set('admin_blog_form', $result['old'] ?? $this->blogFormFromPost($_POST));
            redirect_to($id === null ? route_url('/admin/blog/new') : route_url('/admin/blog/' . $id . '/edit'));
        }

        flash_set('admin_blog_state', [
            'type' => 'success',
            'message' => $id === null ? 'The article was created successfully.' : 'The article was updated successfully.',
        ]);

        redirect_to(route_url('/admin/blog'));
    }

    private function handleHomePageSave(): never
    {
        if (! csrf_is_valid($_POST['_csrf'] ?? null)) {
            flash_set('admin_pages_state', [
                'type' => 'error',
                'message' => 'Your session expired. Please try again.',
            ]);
            flash_set('admin_home_form', $this->pages->homeEditor() ?? []);
            redirect_to(route_url('/admin/pages/home'));
        }

        $result = $this->pages->saveHome($_POST);

        if (! ($result['ok'] ?? false)) {
            flash_set('admin_pages_state', [
                'type' => 'error',
                'message' => implode(' ', $result['errors'] ?? ['The Home page could not be saved.']),
            ]);
            flash_set('admin_home_form', $result['old'] ?? []);
            redirect_to(route_url('/admin/pages/home'));
        }

        flash_set('admin_pages_state', [
            'type' => 'success',
            'message' => 'The Home page was updated successfully.',
        ]);

        redirect_to(route_url('/admin/pages/home'));
    }

    private function handleAboutPageSave(): never
    {
        if (! csrf_is_valid($_POST['_csrf'] ?? null)) {
            flash_set('admin_pages_state', [
                'type' => 'error',
                'message' => 'Your session expired. Please try again.',
            ]);
            flash_set('admin_about_form', $this->pages->aboutEditor() ?? []);
            redirect_to(route_url('/admin/pages/about'));
        }

        $result = $this->pages->saveAbout($_POST);

        if (! ($result['ok'] ?? false)) {
            flash_set('admin_pages_state', [
                'type' => 'error',
                'message' => implode(' ', $result['errors'] ?? ['The About page could not be saved.']),
            ]);
            flash_set('admin_about_form', $result['old'] ?? []);
            redirect_to(route_url('/admin/pages/about'));
        }

        flash_set('admin_pages_state', [
            'type' => 'success',
            'message' => 'The About page was updated successfully.',
        ]);

        redirect_to(route_url('/admin/pages/about'));
    }

    private function handleContactPageSave(): never
    {
        if (! csrf_is_valid($_POST['_csrf'] ?? null)) {
            flash_set('admin_pages_state', [
                'type' => 'error',
                'message' => 'Your session expired. Please try again.',
            ]);
            flash_set('admin_contact_form', $this->pages->contactEditor() ?? []);
            redirect_to(route_url('/admin/pages/contact'));
        }

        $result = $this->pages->saveContact($_POST);

        if (! ($result['ok'] ?? false)) {
            flash_set('admin_pages_state', [
                'type' => 'error',
                'message' => implode(' ', $result['errors'] ?? ['The Contact page could not be saved.']),
            ]);
            flash_set('admin_contact_form', $result['old'] ?? []);
            redirect_to(route_url('/admin/pages/contact'));
        }

        flash_set('admin_pages_state', [
            'type' => 'success',
            'message' => 'The Contact page was updated successfully.',
        ]);

        redirect_to(route_url('/admin/pages/contact'));
    }

    private function handleDonationsPageSave(): never
    {
        if (! csrf_is_valid($_POST['_csrf'] ?? null)) {
            flash_set('admin_pages_state', [
                'type' => 'error',
                'message' => 'Your session expired. Please try again.',
            ]);
            flash_set('admin_donations_form', $this->pages->donationsEditor() ?? []);
            redirect_to(route_url('/admin/pages/donations'));
        }

        $result = $this->pages->saveDonations($_POST);

        if (! ($result['ok'] ?? false)) {
            flash_set('admin_pages_state', [
                'type' => 'error',
                'message' => implode(' ', $result['errors'] ?? ['The Donations page could not be saved.']),
            ]);
            flash_set('admin_donations_form', $result['old'] ?? []);
            redirect_to(route_url('/admin/pages/donations'));
        }

        flash_set('admin_pages_state', [
            'type' => 'success',
            'message' => 'The Donations page was updated successfully.',
        ]);

        redirect_to(route_url('/admin/pages/donations'));
    }

    private function handleFooterSettingsSave(): never
    {
        if (! csrf_is_valid($_POST['_csrf'] ?? null)) {
            flash_set('admin_settings_state', [
                'type' => 'error',
                'message' => 'Your session expired. Please try again.',
            ]);
            flash_set('admin_footer_form', $this->settings->footerEditor());
            redirect_to(route_url('/admin/settings/footer'));
        }

        $result = $this->settings->saveFooter($_POST);

        if (! ($result['ok'] ?? false)) {
            flash_set('admin_settings_state', [
                'type' => 'error',
                'message' => implode(' ', $result['errors'] ?? ['The footer settings could not be saved.']),
            ]);
            flash_set('admin_footer_form', $result['old'] ?? []);
            redirect_to(route_url('/admin/settings/footer'));
        }

        flash_set('admin_settings_state', [
            'type' => 'success',
            'message' => 'The footer settings were updated successfully.',
        ]);

        redirect_to(route_url('/admin/settings/footer'));
    }

    private function handleGeneralSettingsSave(): never
    {
        if (! csrf_is_valid($_POST['_csrf'] ?? null)) {
            flash_set('admin_settings_state', [
                'type' => 'error',
                'message' => 'Your session expired. Please try again.',
            ]);
            flash_set('admin_general_form', $this->settings->generalEditor());
            redirect_to(route_url('/admin/settings/general'));
        }

        $result = $this->settings->saveGeneral($_POST);

        if (! ($result['ok'] ?? false)) {
            flash_set('admin_settings_state', [
                'type' => 'error',
                'message' => implode(' ', $result['errors'] ?? ['The general settings could not be saved.']),
            ]);
            flash_set('admin_general_form', $result['old'] ?? []);
            redirect_to(route_url('/admin/settings/general'));
        }

        flash_set('admin_settings_state', [
            'type' => 'success',
            'message' => 'The general settings were updated successfully.',
        ]);

        redirect_to(route_url('/admin/settings/general'));
    }

    private function handleHeaderSettingsSave(): never
    {
        if (! csrf_is_valid($_POST['_csrf'] ?? null)) {
            flash_set('admin_settings_state', [
                'type' => 'error',
                'message' => 'Your session expired. Please try again.',
            ]);
            flash_set('admin_header_form', $this->settings->headerEditor());
            redirect_to(route_url('/admin/settings/header'));
        }

        $result = $this->settings->saveHeader($_POST);

        if (! ($result['ok'] ?? false)) {
            flash_set('admin_settings_state', [
                'type' => 'error',
                'message' => implode(' ', $result['errors'] ?? ['The header settings could not be saved.']),
            ]);
            flash_set('admin_header_form', $result['old'] ?? []);
            redirect_to(route_url('/admin/settings/header'));
        }

        flash_set('admin_settings_state', [
            'type' => 'success',
            'message' => 'The header settings were updated successfully.',
        ]);

        redirect_to(route_url('/admin/settings/header'));
    }

    private function handleSeoSettingsSave(): never
    {
        if (! csrf_is_valid($_POST['_csrf'] ?? null)) {
            flash_set('admin_settings_state', [
                'type' => 'error',
                'message' => 'Your session expired. Please try again.',
            ]);
            flash_set('admin_seo_form', $this->settings->seoEditor());
            redirect_to(route_url('/admin/settings/seo'));
        }

        $result = $this->settings->saveSeo($_POST);

        if (! ($result['ok'] ?? false)) {
            flash_set('admin_settings_state', [
                'type' => 'error',
                'message' => implode(' ', $result['errors'] ?? ['The SEO settings could not be saved.']),
            ]);
            flash_set('admin_seo_form', $result['old'] ?? []);
            redirect_to(route_url('/admin/settings/seo'));
        }

        flash_set('admin_settings_state', [
            'type' => 'success',
            'message' => 'The SEO settings were updated successfully.',
        ]);

        redirect_to(route_url('/admin/settings/seo'));
    }

    private function handleNotificationsSettingsSave(): never
    {
        if (! csrf_is_valid($_POST['_csrf'] ?? null)) {
            flash_set('admin_settings_state', [
                'type' => 'error',
                'message' => 'Your session expired. Please try again.',
            ]);
            flash_set('admin_notifications_form', $this->settings->notificationsEditor());
            redirect_to(route_url('/admin/settings/notifications'));
        }

        $result = $this->settings->saveNotifications($_POST);

        if (! ($result['ok'] ?? false)) {
            flash_set('admin_settings_state', [
                'type' => 'error',
                'message' => implode(' ', $result['errors'] ?? ['The notification settings could not be saved.']),
            ]);
            flash_set('admin_notifications_form', $result['old'] ?? []);
            redirect_to(route_url('/admin/settings/notifications'));
        }

        flash_set('admin_settings_state', [
            'type' => 'success',
            'message' => 'The notification settings were updated successfully.',
        ]);

        redirect_to(route_url('/admin/settings/notifications'));
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

    private function emptyGalleryForm(): array
    {
        return [
            'title' => '',
            'caption' => '',
            'category_id' => '',
            'image_path' => '',
            'sort_order' => 0,
            'status' => 'draft',
            'is_featured' => 0,
            'is_featured_home' => 0,
        ];
    }

    private function galleryFormFromPost(array $post): array
    {
        return [
            'title' => trim((string) ($post['title'] ?? '')),
            'caption' => trim((string) ($post['caption'] ?? '')),
            'category_id' => trim((string) ($post['category_id'] ?? '')),
            'image_path' => trim((string) ($post['image_path'] ?? '')),
            'sort_order' => trim((string) ($post['sort_order'] ?? '0')),
            'status' => trim((string) ($post['status'] ?? 'draft')),
            'is_featured' => isset($post['is_featured']) ? 1 : 0,
            'is_featured_home' => isset($post['is_featured_home']) ? 1 : 0,
        ];
    }

    private function emptyBlogForm(): array
    {
        return [
            'title' => '',
            'slug' => '',
            'excerpt' => '',
            'body_long' => '',
            'category_id' => '',
            'image_path' => '',
            'read_time_label' => '6 Min Read',
            'status' => 'draft',
            'meta_title' => '',
            'meta_description' => '',
            'published_at' => '',
            'is_featured' => 0,
        ];
    }

    private function blogFormFromPost(array $post): array
    {
        return [
            'title' => trim((string) ($post['title'] ?? '')),
            'slug' => trim((string) ($post['slug'] ?? '')),
            'excerpt' => trim((string) ($post['excerpt'] ?? '')),
            'body_long' => trim((string) ($post['body_long'] ?? '')),
            'category_id' => trim((string) ($post['category_id'] ?? '')),
            'image_path' => trim((string) ($post['image_path'] ?? '')),
            'read_time_label' => trim((string) ($post['read_time_label'] ?? '6 Min Read')),
            'status' => trim((string) ($post['status'] ?? 'draft')),
            'meta_title' => trim((string) ($post['meta_title'] ?? '')),
            'meta_description' => trim((string) ($post['meta_description'] ?? '')),
            'published_at' => trim((string) ($post['published_at'] ?? '')),
            'is_featured' => isset($post['is_featured']) ? 1 : 0,
        ];
    }
}
