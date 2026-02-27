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
    $fullName = $_POST['full_name'];
    $email    = $_POST['email'];

    if (empty($username) || empty($password)) {
        $error = 'Username and password are required.';
    } else {
        $existing = getUserByUsername($username);
        if ($existing) {
            $error = "Username '<strong>$username</strong>' is already taken.";
        } else {
            $user = registerUser($username, $password, $fullName, $email);
            if ($user) {
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — SecureVault Bank</title>
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

        .register-wrapper { display: flex; justify-content: center; align-items: center; min-height: calc(100vh - 64px); padding: 2rem; }
        .register-card {
            background: #fff; border-radius: 10px; box-shadow: 0 8px 32px rgba(0,0,0,.1);
            width: 100%; max-width: 420px; padding: 2.5rem 2rem;
        }
        .register-card h2 { text-align: center; color: var(--navy); margin-bottom: .3rem; font-size: 1.6rem; }
        .register-card .subtitle { text-align: center; color: #888; margin-bottom: 2rem; font-size: .9rem; }

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

<main class="register-wrapper">
    <div class="register-card">
        <h2>Create Account</h2>
        <p class="subtitle">Join SecureVault today</p>

        <?php if ($error): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="register.php">
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" placeholder="Enter your full name"
                       value="<?php echo $_POST['full_name'] ?? ''; ?>">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="text" id="email" name="email" placeholder="Enter your email"
                       value="<?php echo $_POST['email'] ?? ''; ?>">
            </div>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Choose a username" required
                       value="<?php echo $_POST['username'] ?? ''; ?>">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Choose a password" required>
            </div>
            <button type="submit" class="btn-submit">Create Account</button>
        </form>
        <p style="text-align:center;margin-top:1rem;font-size:.9rem;color:#666;">
            Already have an account? <a href="login.php" style="color:var(--gold);font-weight:600;">Sign in</a>
        </p>
    </div>
</main>

<footer class="footer">&copy; 2026 SecureVault Bank &mdash; <em>Training Environment Only</em></footer>

</body>
</html>
