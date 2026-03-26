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
        private readonly AdminSettingsRepository $settings
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
