<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) : 'Admin Panel' ?> | AIREN Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

    <style>
        /* Global Theme Variables and Base Styles */
        :root {
            --custom-primary-color: #14b5ff;
            --custom-primary-color-darker: hsl(199, 100%, 44%);
            --custom-primary-color-lighter: hsl(199, 100%, 64%);
            --custom-primary-color-rgb: 20, 181, 255;
            --custom-primary-text-on-bg: #ffffff;

            /* Light Theme (Default) */
            --current-bg: #f0f2f5;
            /* Slightly different admin bg */
            --current-text: #343a40;
            --current-text-muted: #6c757d;
            --current-card-bg: #ffffff;
            --current-navbar-bg: #ffffff;
            --current-sidebar-bg: #2c3e50;
            /* Example sidebar color */
            --current-sidebar-text: #ecf0f1;
            --current-sidebar-link-active-bg: var(--custom-primary-color);
            --current-border-color: #e9ecef;
            --current-input-bg: #ffffff;
            --current-input-text: #495057;
            --current-input-border: #ced4da;
            --current-placeholder-text: #6c757d;
            --current-box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.075);
            --current-section-bg: #ffffff;
            --current-theme-icon: "\f185";
            /* Sun icon */
            --current-link-color: var(--custom-primary-color);
            --current-link-hover-color: var(--custom-primary-color-darker);
        }

        html[data-theme="dark"] {
            --custom-primary-color: hsl(199, 100%, 65%);
            /* Brighter for dark mode */
            --custom-primary-color-darker: hsl(199, 90%, 55%);
            --custom-primary-color-lighter: hsl(199, 100%, 75%);

            --current-bg: #161a1d;
            /* Darker admin bg */
            --current-text: #e0e0e0;
            --current-text-muted: #adb5bd;
            --current-card-bg: #1f2327;
            --current-navbar-bg: #1f2327;
            --current-sidebar-bg: #1f2327;
            /* Example dark sidebar */
            --current-sidebar-text: #e0e0e0;
            --current-sidebar-link-active-bg: var(--custom-primary-color);
            --current-border-color: #3b4045;
            --current-input-bg: #2a2e32;
            --current-input-text: #f0f0f0;
            --current-input-border: #454a4f;
            --current-placeholder-text: #8a939d;
            --current-box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.2);
            --current-section-bg: #1f2327;
            --current-theme-icon: "\f186";
            /* Moon icon */
            --current-link-color: var(--custom-primary-color);
            --current-link-hover-color: var(--custom-primary-color-lighter);
        }

        html[data-theme="dark"] .dropdown-toggle {
            color: var(--custom-primary-color);
        }

        html[data-theme="dark"] .navbar-toggler-icon {
            background-color: var(--custom-primary-color);
        }

        html[data-theme="dark"] .navbar-toggler {
            background-color: var(--custom-primary-color);
        }

        body {
            background-color: var(--current-bg);
            color: var(--current-text);
            font-family: 'Poppins', sans-serif;
            transition: background-color 0.3s ease, color 0.3s ease;
            font-size: 0.95rem;
        }



        /* Base admin layout styles, navbar, sidebar would go here or in a separate admin.css */
        .admin-wrapper {
            display: flex;
        }


        .admin-main-content {
            flex-grow: 1;
            padding: 0;

        }

        .navbar.admin-navbar {
            background-color: var(--current-navbar-bg);
            box-shadow: var(--current-box-shadow);
            border-bottom: 1px solid var(--current-border-color);
            transition: background-color 0.3s ease, border-color 0.3s ease;
            width: 100%;
        }

        .admin-navbar {
            overflow: visible;
            /* pastikan dropdown bisa keluar */
            position: relative;
            /* biar absolute di dropdown pakai body, bukan parent */
            z-index: 1;
        }

        /* ... more navbar styles ... */

        #themeToggleBtn {
            background-color: transparent;
            /* var(--current-card-bg); */
            color: var(--current-text-muted);
            border: 1px solid transparent;
            /* var(--current-border-color); */
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            /* box-shadow: 0 2px 5px rgba(0,0,0,0.1); */
            transition: all 0.3s ease;
            font-size: 1.2rem;
        }

        #themeToggleBtn:hover {
            color: var(--custom-primary-color);
            /* background-color: rgba(var(--custom-primary-color-rgb), 0.1); */
        }

        #themeToggleBtn .fa-solid::before {
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            content: var(--current-theme-icon);
        }

        /* General purpose admin primary button */
        .btn-admin-primary {
            background: linear-gradient(90deg, var(--custom-primary-color-darker) 0%, var(--custom-primary-color) 100%);
            border: none;
            color: var(--custom-primary-text-on-bg) !important;
            /* Ensure text color */
            padding: 0.6rem 1.2rem;
            font-weight: 500;
            border-radius: 0.375rem;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(var(--custom-primary-color-rgb), 0.15);
        }

        html[data-theme="dark"] .btn-admin-primary {
            box-shadow: 0 2px 8px rgba(var(--custom-primary-color-rgb), 0.25);
        }

        .btn-admin-primary:hover {
            background: linear-gradient(90deg, var(--custom-primary-color) 0%, var(--custom-primary-color-darker) 100%);
            color: var(--custom-primary-text-on-bg) !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(var(--custom-primary-color-rgb), 0.25);
        }

        html[data-theme="dark"] .btn-admin-primary:hover {
            box-shadow: 0 4px 12px rgba(var(--custom-primary-color-rgb), 0.35);
        }

        .btn-admin-primary i {
            margin-right: 0.5em;
        }

        /* General Card Styling */
        .card {
            background-color: var(--current-card-bg);
            border: 1px solid var(--current-border-color);
            box-shadow: var(--current-box-shadow);
            border-radius: 0.5rem;
            /* Consistent border radius */
            transition: all 0.3s ease;
        }

        .card-header {
            background-color: var(--current-card-bg);
            /* Or a slightly different shade like var(--current-section-bg) */
            border-bottom: 1px solid var(--current-border-color);
            font-weight: 600;
        }

        /* Additional specific styles for dashboard page can go in admin/index.php or a separate dashboard.css */
    </style>
</head>

<body>

    <!-- <nav class="navbar navbar-expand-lg admin-navbar sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-gem"></i>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbarContent"
                aria-controls="adminNavbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="adminNavbarContent">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
                    <li class="nav-item">
                        <button id="themeToggleBtn" class="btn nav-link" title="Toggle theme">
                            <i class="fa-solid"></i>
                        </button>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownAdmin" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle me-1"></i>
                            <?= isset($_SESSION['admin_username']) ? htmlspecialchars($_SESSION['admin_username']) : 'Admin' ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownAdmin">
                            <li><a class="dropdown-item" href="#">Profil</a></li>
                             <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="../index.php" target="_blank">Landing Page</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav> -->

  <nav class="navbar admin-navbar sticky-top px-md-4">
  <div class="container-fluid"> <!-- Tambahkan container agar navbar full-width -->
    <div class="d-flex align-items-center">
      <button class="navbar-toggler d-lg-none me-3" type="button" id="mobileSidebarToggler"
        aria-label="Toggle navigation">
        <i class="ri-menu-line"></i>
      </button>

      <a class="navbar-brand d-lg-none" href="index.php">
        <i class="ri-diamond-line" style="color: var(--custom-primary-color);"></i>
        AIREN Store
      </a>
    </div>

    <div class="ms-auto d-flex align-items-center">
      <ul class="navbar-nav flex-row align-items-center">
        <li class="nav-item">
          <button id="themeToggleBtn" class="btn nav-link" title="Toggle theme">
            <i class="ri-sun-line"></i>
          </button>
        </li>

        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownAdmin" role="button"
            data-bs-toggle="dropdown" aria-expanded="false">
            <i class="ri-user-3-line me-1"></i>
            <span class="d-none d-sm-inline">
              <?= isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin' ?>
            </span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end"
            aria-labelledby="navbarDropdownAdmin">
            <li><a class="dropdown-item" href="#">Profil</a></li>
            <li><a class="dropdown-item" href="../index.php" target="_blank">Lihat Landing Page</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>




</body>

</html>