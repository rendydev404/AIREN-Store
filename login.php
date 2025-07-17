<?php
session_start();
require_once 'config/db.php';

// Jika sudah login, redirect
if (isset($_SESSION['user_id'])) {
    header('Location: ' . ($_SESSION['role'] === 'admin' ? 'admin/index.php' : 'index.php'));
    exit;
}

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login_identifier = trim($_POST['login_identifier']);
    $password = trim($_POST['password']);

    if (empty($login_identifier) || empty($password)) {
        $error_message = "Username/Email dan password tidak boleh kosong.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, username, email, nomor, password, role FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$login_identifier, $login_identifier]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                // Login berhasil, buat session
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                // Alihkan berdasarkan peran
                if ($user['role'] === 'admin') {
                    header('Location: admin/index.php');
                } else {
                    // Cek jika ada redirect
                    if (isset($_GET['redirect']) && $_GET['redirect'] === 'cart') {
                        header('Location: index.php#keranjang'); // Arahkan kembali ke section keranjang
                    } else {
                        header('Location: index.php');
                    }
                }
                exit;
            } else {
                $error_message = "Username/Email atau password salah.";
            }
        } catch (PDOException $e) {
            // error_log("Login error: " . $e->getMessage());
            $error_message = "Terjadi kesalahan sistem. Coba lagi nanti.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AIREN Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
        body { background: var(--current-bg); color: var(--current-text); display: flex; justify-content: center; align-items: center; min-height: 100vh; font-family: 'Poppins', sans-serif; transition: background-color 0.3s ease, color 0.3s ease; margin: 0; }
        .login-container { background-color: var(--current-card-bg); padding: 2.8rem 2.5rem; border-radius: 0.85rem; box-shadow: var(--current-box-shadow); width: 100%; max-width: 450px; margin: 20px; transition: background-color 0.3s ease, box-shadow 0.3s ease; border: 1px solid var(--current-border-color); }
        .login-container h2 { color: var(--custom-primary-color); margin-bottom: 2rem; font-weight: 700; text-align: center; font-size: 2rem; transition: color 0.3s ease; }
        html[data-theme="dark"] .login-container h2 { text-shadow: 0 0 8px rgba(var(--custom-primary-color-rgb), 0.5); }
        .form-label { font-weight: 500; color: var(--current-text); margin-bottom: 0.6rem; }
        .form-control { background-color: var(--current-input-bg); color: var(--current-input-text); border: 1px solid var(--current-input-border); padding: 0.85rem 1rem; border-radius: 0.5rem; transition: all 0.3s ease; }
        .form-control::placeholder { color: var(--current-placeholder-text); opacity: 1; }
        .form-control:focus { border-color: var(--custom-primary-color); background-color: var(--current-input-bg); color: var(--current-input-text); box-shadow: 0 0 0 0.25rem rgba(var(--custom-primary-color-rgb), 0.3); }
        .btn-login { background: linear-gradient(90deg, var(--custom-primary-color-darker) 0%, var(--custom-primary-color) 100%); border: none; color: #fff; padding: 0.85rem; font-weight: 600; border-radius: 0.5rem; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(var(--custom-primary-color-rgb), 0.2); }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(var(--custom-primary-color-rgb), 0.3); }
        .alert { border-radius: 0.5rem; }
        #themeToggleBtn { position: fixed; top: 20px; right: 20px; z-index: 1000; background-color: var(--current-card-bg); color: var(--current-text); border: 1px solid var(--current-border-color); width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); transition: all 0.3s ease; }
        #themeToggleBtn:hover { background-color: var(--current-input-bg); color: var(--custom-primary-color); border-color: var(--custom-primary-color); }
        #themeToggleBtn .fa-solid::before { font-family: "Font Awesome 6 Free"; font-weight: 900; content: var(--current-theme-icon); }
    </style>
</head>
<body>
    <button id="themeToggleBtn" class="btn" title="Toggle theme"><i class="fa-solid"></i></button>

    <div class="login-container">
        <h2>Login</h2>
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>
        <?php 
        if (isset($_SESSION['success_message'])) {
            echo '<div class="alert alert-success" role="alert">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
            unset($_SESSION['success_message']);
        }
        ?>
        <form method="POST" action="login.php<?= isset($_GET['redirect']) ? '?redirect=' . htmlspecialchars($_GET['redirect']) : '' ?>">
            <div class="mb-3">
                <label for="login_identifier" class="form-label">Username atau Email</label>
                <input type="text" class="form-control" id="login_identifier" name="login_identifier" placeholder="Masukkan username atau email" required>
            </div>
            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password" required>
            </div>
            <button type="submit" class="btn btn-login w-100">Login</button>
        </form>
        <div class="text-center mt-4">
            <p>Belum punya akun? <a href="register.php" style="color: var(--custom-primary-color); text-decoration: none; font-weight: 500;">Daftar di sini</a></p>
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