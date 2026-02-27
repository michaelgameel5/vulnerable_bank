<?php
require_once __DIR__ . '/functions.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $user = loginUser($username, $password);

    if ($user) {
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — SecureVault Bank</title>
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --navy: #0a1628; --blue: #1a3a5c; --gold: #c9a84c;
            --gold-light: #e2cc7e; --white: #f4f4f4; --gray: #d1d5db; --bg: #f0f2f5;
        }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: var(--bg); color: #333; line-height: 1.6; }
        a { text-decoration: none; color: inherit; }

        .navbar { background: var(--navy); padding: 0 2rem; display: flex; align-items: center; justify-content: space-between; height: 64px; box-shadow: 0 2px 8px rgba(0,0,0,.3); }
        .navbar .logo { font-size: 1.4rem; font-weight: 700; color: var(--gold); letter-spacing: 1px; }
        .navbar .logo span { color: var(--white); }
        .navbar nav a { color: var(--gray); margin-left: 1.8rem; font-size: .95rem; transition: color .2s; }
        .navbar nav a:hover { color: var(--gold); }

        .login-wrapper { display: flex; justify-content: center; align-items: center; min-height: calc(100vh - 64px); padding: 2rem; }
        .login-card {
            background: #fff; border-radius: 10px; box-shadow: 0 8px 32px rgba(0,0,0,.1);
            width: 100%; max-width: 420px; padding: 2.5rem 2rem;
        }
        .login-card h2 { text-align: center; color: var(--navy); margin-bottom: .3rem; font-size: 1.6rem; }
        .login-card .subtitle { text-align: center; color: #888; margin-bottom: 2rem; font-size: .9rem; }

        .form-group { margin-bottom: 1.2rem; }
        .form-group label { display: block; font-weight: 600; margin-bottom: .35rem; font-size: .9rem; color: var(--blue); }
        .form-group input {
            width: 100%; padding: .75rem 1rem; border: 1px solid #ccc; border-radius: 6px;
            font-size: .95rem; transition: border-color .2s;
        }
        .form-group input:focus { outline: none; border-color: var(--gold); }

        .btn-submit {
            width: 100%; padding: .85rem; background: var(--gold); color: var(--navy);
            border: none; border-radius: 6px; font-size: 1rem; font-weight: 700;
            cursor: pointer; transition: background .2s;
        }
        .btn-submit:hover { background: var(--gold-light); }

        .error-msg { background: #fee2e2; color: #b91c1c; padding: .7rem 1rem; border-radius: 6px; margin-bottom: 1.2rem; font-size: .9rem; }
        .footer { text-align: center; padding: 2rem; font-size: .85rem; color: #888; border-top: 1px solid #ddd; margin-top: 2rem; }
    </style>
</head>
<body>

<header class="navbar">
    <a href="index.php" class="logo">Secure<span>Vault</span></a>
    <nav>
        <a href="index.php">Home</a>
        <a href="login.php">Login</a>
        <a href="register.php">Register</a>
    </nav>
</header>

<main class="login-wrapper">
    <div class="login-card">
        <h2>Sign In</h2>
        <p class="subtitle">Access your SecureVault account</p>

        <?php if ($error): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter your username" required
                       value="<?php echo $_POST['username'] ?? ''; ?>">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="btn-submit">Sign In</button>
        </form>
        <p style="text-align:center;margin-top:1rem;font-size:.9rem;color:#666;">
            Don't have an account? <a href="register.php" style="color:var(--gold);font-weight:600;">Create one</a>
        </p>
    </div>
</main>

<footer class="footer">&copy; 2026 SecureVault Bank &mdash; <em>Training Environment Only</em></footer>

</body>
</html>
