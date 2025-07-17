<?php
session_start();
// Jika pengguna sudah login, alihkan ke halaman utama
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - AIREN Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --custom-primary-color: #14b5ff;
            --custom-primary-color-darker: hsl(199, 100%, 44%);
            --current-bg: linear-gradient(135deg, #eef1f5 0%, #f8f9fc 100%);
            --current-text: #333;
            --current-card-bg: #ffffff;
            --current-border-color: #dee2e6;
            --current-input-bg: #ffffff;
            --current-input-text: #495057;
            --current-input-border: #ced4da;
            --current-placeholder-text: #6c757d;
            --current-box-shadow: 0 0.75rem 2rem rgba(0, 0, 0, 0.08);
            --current-theme-icon: "\f185";
        }

        html[data-theme="dark"] {
            --custom-primary-color: hsl(199, 100%, 70%);
            --custom-primary-color-darker: hsl(199, 90%, 60%);
            --current-bg: linear-gradient(135deg, #1a1e23 0%, #222831 100%);
            --current-text: #e0e0e0;
            --current-card-bg: #2c333a;
            --current-border-color: #4a545f;
            --current-input-bg: #343b42;
            --current-input-text: #f0f0f0;
            --current-input-border: #505a64;
            --current-placeholder-text: #8a939d;
            --current-box-shadow: 0 0.75rem 2.5rem rgba(0, 0, 0, 0.25);
            --current-theme-icon: "\f186";
        }

        body {
            background: var(--current-bg);
            color: var(--current-text);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            font-family: 'Poppins', sans-serif;
            transition: background-color 0.3s ease, color 0.3s ease;
            margin: 0;
        }

        .login-container {
            background-color: var(--current-card-bg);
            padding: 2.8rem 2.5rem;
            border-radius: 0.85rem;
            box-shadow: var(--current-box-shadow);
            width: 100%;
            max-width: 450px;
            margin: 20px;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid var(--current-border-color);
        }

        .login-container h2 {
            color: var(--custom-primary-color);
            margin-bottom: 2rem;
            font-weight: 700;
            text-align: center;
            font-size: 2rem;
            transition: color 0.3s ease;
        }

        html[data-theme="dark"] .login-container h2 {
            text-shadow: 0 0 8px rgba(var(--custom-primary-color-rgb), 0.5);
        }

        .form-label {
            font-weight: 500;
            color: var(--current-text);
            margin-bottom: 0.6rem;
        }

        .form-control {
            background-color: var(--current-input-bg);
            color: var(--current-input-text);
            border: 1px solid var(--current-input-border);
            padding: 0.85rem 1rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }

        .form-control::placeholder {
            color: var(--current-placeholder-text);
            opacity: 1;
        }

        .form-control:focus {
            border-color: var(--custom-primary-color);
            background-color: var(--current-input-bg);
            color: var(--current-input-text);
            box-shadow: 0 0 0 0.25rem rgba(var(--custom-primary-color-rgb), 0.3);
        }

        .btn-login {
            background: linear-gradient(90deg, var(--custom-primary-color-darker) 0%, var(--custom-primary-color) 100%);
            border: none;
            color: #fff;
            padding: 0.85rem;
            font-weight: 600;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(var(--custom-primary-color-rgb), 0.2);
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(var(--custom-primary-color-rgb), 0.3);
        }

        .alert {
            border-radius: 0.5rem;
        }

        #themeToggleBtn {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            background-color: var(--current-card-bg);
            color: var(--current-text);
            border: 1px solid var(--current-border-color);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        #themeToggleBtn:hover {
            background-color: var(--current-input-bg);
            color: var(--custom-primary-color);
            border-color: var(--custom-primary-color);
        }

        #themeToggleBtn .fa-solid::before {
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            content: var(--current-theme-icon);
        }
    </style>
</head>

<body>
    <button id="themeToggleBtn" class="btn" title="Toggle theme"><i class="fa-solid"></i></button>

    <div class="login-container">
        <h2>Buat Akun</h2>
        <?php
        if (isset($_SESSION['error_message'])) {
            echo '<div class="alert alert-danger" role="alert">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
            unset($_SESSION['error_message']);
        }
        ?>
        <form action="process_register.php" method="POST">
            <div class="mb-3">
                <label for="email" class="form-label">Alamat Email</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="contoh@email.com"
                    required>
            </div>
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" placeholder="Pilih username Anda"
                    required>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Nomor Telepon</label>
                <input type="number" class="form-control" id="phone" name="phone" placeholder="Masukan Nomor Telepon Anda"
                    required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Buat password"
                    required>
            </div>
            <div class="mb-4">
                <label for="password_confirm" class="form-label">Konfirmasi Password</label>
                <input type="password" class="form-control" id="password_confirm" name="password_confirm"
                    placeholder="Ketik ulang password" required>
            </div>
            <button type="submit" class="btn btn-login w-100">Daftar</button>
        </form>
        <div class="text-center mt-4">
            <p>Sudah punya akun? <a href="login.php"
                    style="color: var(--custom-primary-color); text-decoration: none; font-weight: 500;">Login di
                    sini</a></p>
        </div>
    </div>

    <script>
        const themeToggleBtn = document.getElementById('themeToggleBtn');
        const htmlEl = document.documentElement;
        function setTheme(theme) {
            htmlEl.setAttribute('data-theme', theme);
            localStorage.setItem('login_theme', theme);
        }
        function toggleTheme() {
            const currentTheme = htmlEl.getAttribute('data-theme') || 'light';
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            setTheme(newTheme);
        }
        const savedTheme = localStorage.getItem('login_theme') || 'light';
        setTheme(savedTheme);
        themeToggleBtn.addEventListener('click', toggleTheme);
    </script>
</body>

</html>