<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'Parlour POS', ENT_QUOTES, 'UTF-8') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="/assets/css/app.css" rel="stylesheet">
    <style>
        /* Robust viewport layout: ensures sidebar never clips off-screen and footer is ALWAYS visible */
        .app-sidebar {
            height: 100vh !important;
            max-height: 100vh !important;
            position: sticky !important;
            top: 0 !important;
            display: flex !important;
            flex-direction: column !important;
            overflow: hidden !important;
            padding: 20px 14px 16px !important;
            z-index: 1020 !important;
            box-sizing: border-box !important;
        }

        .app-sidebar .brand {
            flex-shrink: 0 !important;
            margin-bottom: 18px !important;
        }

        .app-sidebar .nav-caption {
            flex-shrink: 0 !important;
            margin-bottom: 6px !important;
        }

        /* Navigation links scroll smoothly if screen height is smaller than nav list */
        .app-sidebar .side-nav {
            flex: 1 1 auto !important;
            min-height: 0 !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            margin-bottom: 10px !important;
            padding-right: 4px !important;
        }

        .app-sidebar .side-nav::-webkit-scrollbar {
            width: 4px;
        }
        .app-sidebar .side-nav::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 4px;
        }

        /* Footer permanently pinned at the bottom of the sidebar */
        .app-sidebar .sidebar-footer {
            flex-shrink: 0 !important;
            margin-top: auto !important;
            padding-top: 14px !important;
            padding-bottom: 4px !important;
            border-top: 1px solid rgba(255, 255, 255, 0.14) !important;
            background: var(--sidebar) !important;
            display: flex !important;
            flex-direction: column !important;
            gap: 10px !important;
        }

        /* Highly visible, professional red-accented Sign out button */
        .signout-btn {
            background: rgba(220, 53, 69, 0.15) !important;
            border: 1px solid rgba(220, 53, 69, 0.4) !important;
            color: #ff9da7 !important;
            font-size: 0.85rem !important;
            font-weight: 600 !important;
            border-radius: 8px !important;
            padding: 7px 12px !important;
            transition: all 0.18s ease !important;
            text-decoration: none !important;
            cursor: pointer !important;
        }
        .signout-btn:hover {
            background: #dc3545 !important;
            border-color: #dc3545 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.35) !important;
        }
    </style>
</head>
<body>
    <?php if (\App\Services\AuthService::userId() && !empty($_SESSION['user'])):
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $userPerms = $_SESSION['user']['permissions'] ?? [];
        $allNav = [
            ['/dashboard', 'grid-1', 'Dashboard', 'dashboard.view'],
            ['/pos', 'cart3', 'POS & Billing', 'pos.use'],
            ['/appointments', 'calendar3', 'Appointments', 'appointments.manage'],
            ['/customers', 'people', 'Customers', 'customers.manage'],
            ['/services', 'scissors', 'Services', 'catalog.view'],
            ['/products', 'bag', 'Products', 'catalog.view'],
            ['/staff', 'person-badge', 'Staff', 'staff.manage'],
            ['/purchases', 'box-seam', 'Purchases', 'purchases.manage'],
            ['/suppliers', 'truck', 'Suppliers', 'purchases.manage'],
            ['/expenses', 'receipt', 'Expenses', 'expenses.manage'],
            ['/reports', 'bar-chart-line', 'Reports', 'reports.view'],
            ['/users', 'people-fill', 'Users', 'users.manage'],
            ['/audit-logs', 'shield-check', 'Audit Logs', 'audit.view'],
        ];
        $nav = array_filter($allNav, static function ($item) use ($userPerms) {
            return empty($item[3]) || in_array($item[3], $userPerms, true);
        });
    ?>
    <div class="app-shell">
        <aside class="app-sidebar" id="app-sidebar">
            <a class="brand" href="/dashboard">
                <span class="brand-mark"><i class="bi bi-stars"></i></span>
                <span>Velora<small>PARLOUR POS</small></span>
            </a>
            <div class="nav-caption">Workspace</div>
            <nav class="side-nav">
                <?php foreach ($nav as [$url, $icon, $label]):
                    $active = $path === $url || ($url !== '/dashboard' && str_starts_with($path, $url . '/'));
                ?>
                    <a href="<?= $url ?>" class="<?= $active ? 'active' : '' ?>">
                        <i class="bi bi-<?= $icon ?>"></i>
                        <span><?= $label ?></span>
                    </a>
                <?php endforeach; ?>
            </nav>
            <div class="sidebar-footer">
                <div class="d-flex align-items-center gap-2">
                    <div class="user-avatar"><?= htmlspecialchars(strtoupper(substr($_SESSION['user']['name'], 0, 1))) ?></div>
                    <div class="small text-truncate flex-grow-1">
                        <strong class="d-block text-truncate text-white" style="font-size: 0.92rem;"><?= htmlspecialchars($_SESSION['user']['name']) ?></strong>
                        <span class="badge bg-secondary text-white rounded-pill" style="font-size: 10px; letter-spacing: 0.03em;">
                            <?= htmlspecialchars($_SESSION['user']['role_name'] ?? 'User') ?>
                        </span>
                    </div>
                    <a href="/change-password" class="icon-button" title="Change Password" aria-label="Change Password" style="color: #aa9dbd; font-size: 1.05rem; padding: 2px 6px;">
                        <i class="bi bi-key"></i>
                    </a>
                </div>
                <form method="post" action="/logout" class="m-0 w-100">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars(\App\Support\Flash::csrf()) ?>">
                    <button type="submit" class="btn signout-btn w-100 d-flex align-items-center justify-content-center gap-2" title="Sign out" aria-label="Sign out">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Sign out</span>
                    </button>
                </form>
            </div>
        </aside>
        <section class="app-content">
            <header class="app-header">
                <button class="sidebar-toggle d-lg-none" type="button" data-app-sidebar aria-label="Toggle navigation">
                    <i class="bi bi-list"></i>
                </button>
                <div>
                    <span class="page-context">Velora workspace</span>
                    <span class="header-date"><?= date('D, j M Y') ?></span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <?php if (in_array('pos.use', $userPerms, true)): ?>
                        <a class="btn btn-primary btn-sm" href="/pos"><i class="bi bi-plus-lg"></i> New sale</a>
                    <?php endif; ?>
                    <form method="post" action="/logout" class="d-inline m-0">
                        <input type="hidden" name="_token" value="<?= htmlspecialchars(\App\Support\Flash::csrf()) ?>">
                        <button type="submit" class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1" title="Sign out" aria-label="Sign out">
                            <i class="bi bi-box-arrow-right"></i>
                            <span class="d-none d-sm-inline">Sign out</span>
                        </button>
                    </form>
                </div>
            </header>
            <main class="page-body"><?= $content ?></main>
        </section>
    </div>
    <?php else: ?>
        <main class="auth-page container py-4 py-lg-5"><?= $content ?></main>
    <?php endif; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/app.js"></script>
</body>
</html>
