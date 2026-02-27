<?php require_once __DIR__ . '/functions.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SecureVault Bank</title>
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --navy:    #0a1628;
            --blue:    #1a3a5c;
            --gold:    #c9a84c;
            --gold-light: #e2cc7e;
            --white:   #f4f4f4;
            --gray:    #d1d5db;
            --bg:      #f0f2f5;
            --shadow:  0 4px 24px rgba(0,0,0,.12);
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--bg);
            color: #333;
            line-height: 1.6;
        }
        a { text-decoration: none; color: inherit; }

        .navbar {
            background: var(--navy);
            padding: 0 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 64px;
            box-shadow: 0 2px 8px rgba(0,0,0,.3);
        }
        .navbar .logo {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--gold);
            letter-spacing: 1px;
        }
        .navbar .logo span { color: var(--white); }
        .navbar nav a {
            color: var(--gray);
            margin-left: 1.8rem;
            font-size: .95rem;
            transition: color .2s;
        }
        .navbar nav a:hover { color: var(--gold); }

        .hero {
            background: linear-gradient(135deg, var(--navy) 0%, var(--blue) 100%);
            color: var(--white);
            text-align: center;
            padding: 6rem 2rem 5rem;
        }
        .hero h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            letter-spacing: 1px;
        }
        .hero h1 span { color: var(--gold); }
        .hero p {
            font-size: 1.15rem;
            max-width: 600px;
            margin: 0 auto 2.5rem;
            opacity: .85;
        }
        .btn {
            display: inline-block;
            padding: .85rem 2.4rem;
            border-radius: 4px;
            font-weight: 600;
            font-size: 1rem;
            transition: background .2s, transform .15s;
            cursor: pointer;
            border: none;
        }
        .btn-gold {
            background: var(--gold);
            color: var(--navy);
        }
        .btn-gold:hover { background: var(--gold-light); transform: translateY(-2px); }
        .btn-outline {
            border: 2px solid var(--gold);
            color: var(--gold);
            background: transparent;
            margin-left: 1rem;
        }
        .btn-outline:hover { background: var(--gold); color: var(--navy); }

        .features {
            display: flex;
            gap: 2rem;
            max-width: 960px;
            margin: -3rem auto 4rem;
            padding: 0 2rem;
        }
        .feature-card {
            flex: 1;
            background: #fff;
            border-radius: 8px;
            padding: 2rem 1.5rem;
            box-shadow: var(--shadow);
            text-align: center;
        }
        .feature-card .icon {
            font-size: 2.4rem;
            margin-bottom: .8rem;
        }
        .feature-card h3 {
            color: var(--blue);
            margin-bottom: .5rem;
        }
        .feature-card p {
            font-size: .9rem;
            color: #666;
        }

        .footer {
            text-align: center;
            padding: 2rem;
            font-size: .85rem;
            color: #888;
            border-top: 1px solid #ddd;
            margin-top: 3rem;
        }
    </style>
</head>
<body>

<header class="navbar">
    <div class="logo">Secure<span>Vault</span></div>
    <nav>
        <a href="index.php">Home</a>
        <?php if (isLoggedIn()): ?>
            <a href="dashboard.php">Dashboard</a>
            <a href="transfer.php">Transfer</a>
            <a href="profile.php">Profile</a>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
        <?php endif; ?>
    </nav>
</header>

<section class="hero">
    <h1>Welcome to <span>SecureVault</span></h1>
    <p>Your trusted partner in digital banking. Fast transfers, real-time balances, and a seamless experience — all in one place.</p>
    <?php if (!isLoggedIn()): ?>
        <a href="login.php" class="btn btn-gold">Sign In</a>
        <a href="register.php" class="btn btn-outline">Create Account</a>
    <?php else: ?>
        <a href="dashboard.php" class="btn btn-gold">Go to Dashboard</a>
    <?php endif; ?>
</section>

<section class="features">
    <div class="feature-card">
        <div class="icon">&#128179;</div>
        <h3>Instant Transfers</h3>
        <p>Move funds between accounts in seconds with our lightning-fast transfer engine.</p>
    </div>
    <div class="feature-card">
        <div class="icon">&#128202;</div>
        <h3>Real-Time Balance</h3>
        <p>Always see your up-to-date balance and full transaction history at a glance.</p>
    </div>
    <div class="feature-card">
        <div class="icon">&#128274;</div>
        <h3>Account Management</h3>
        <p>Update your profile, manage credentials, and stay in control of your finances.</p>
    </div>
</section>

<footer class="footer">
    &copy; 2026 SecureVault Bank &mdash; <em>Training Environment Only</em>
</footer>

</body>
</html>
