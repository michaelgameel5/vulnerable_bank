<?php
require_once __DIR__ . '/functions.php';
requireLogin();

$userId  = currentUserId();
$user    = getUserById($userId);
$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = $_POST['full_name'];
    $email    = $_POST['email'];
    $password = $_POST['password'];

    $result = updateProfile($userId, $fullName, $email, $password);

    if ($result) {
        $success = 'Profile updated successfully.';
        $user = getUserById($userId);
    } else {
        $error = 'Failed to update profile.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile — SecureVault Bank</title>
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --navy: #0a1628; --blue: #1a3a5c; --gold: #c9a84c;
            --gold-light: #e2cc7e; --white: #f4f4f4; --gray: #d1d5db; --bg: #f0f2f5;
            --shadow: 0 4px 24px rgba(0,0,0,.12);
        }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: var(--bg); color: #333; line-height: 1.6; }
        a { text-decoration: none; color: inherit; }

        .navbar { background: var(--navy); padding: 0 2rem; display: flex; align-items: center; justify-content: space-between; height: 64px; box-shadow: 0 2px 8px rgba(0,0,0,.3); }
        .navbar .logo { font-size: 1.4rem; font-weight: 700; color: var(--gold); letter-spacing: 1px; }
        .navbar .logo span { color: var(--white); }
        .navbar nav a { color: var(--gray); margin-left: 1.8rem; font-size: .95rem; transition: color .2s; }
        .navbar nav a:hover { color: var(--gold); }

        .container { max-width: 560px; margin: 3rem auto; padding: 0 2rem; }
        .page-title { font-size: 1.5rem; color: var(--navy); margin-bottom: 1.5rem; }

        .card { background: #fff; border-radius: 10px; box-shadow: var(--shadow); padding: 2rem; }

        .profile-header {
            text-align: center; padding-bottom: 1.5rem; margin-bottom: 1.5rem; border-bottom: 1px solid #eee;
        }
        .avatar {
            width: 72px; height: 72px; border-radius: 50%; background: var(--navy); color: var(--gold);
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 1.8rem; font-weight: 700; margin-bottom: .5rem;
        }
        .profile-header h3 { color: var(--navy); }
        .profile-header p { color: #888; font-size: .85rem; }

        .form-group { margin-bottom: 1.2rem; }
        .form-group label { display: block; font-weight: 600; margin-bottom: .35rem; font-size: .9rem; color: var(--blue); }
        .form-group input {
            width: 100%; padding: .75rem 1rem; border: 1px solid #ccc; border-radius: 6px;
            font-size: .95rem; transition: border-color .2s;
        }
        .form-group input:focus { outline: none; border-color: var(--gold); }
        .form-group .hint { font-size: .8rem; color: #999; margin-top: .25rem; }

        .btn-submit {
            width: 100%; padding: .85rem; background: var(--gold); color: var(--navy);
            border: none; border-radius: 6px; font-size: 1rem; font-weight: 700;
            cursor: pointer; transition: background .2s;
        }
        .btn-submit:hover { background: var(--gold-light); }

        .alert { padding: .8rem 1rem; border-radius: 6px; margin-bottom: 1.2rem; font-size: .9rem; }
        .alert-success { background: #dcfce7; color: #166534; }
        .alert-error   { background: #fee2e2; color: #b91c1c; }

        .footer { text-align: center; padding: 2rem; font-size: .85rem; color: #888; border-top: 1px solid #ddd; margin-top: 3rem; }
    </style>
</head>
<body>

<header class="navbar">
    <a href="index.php" class="logo">Secure<span>Vault</span></a>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="transfer.php">Transfer</a>
        <a href="bill_payment.php" class="active">Bill Payment</a>
        <a href="profile.php">Profile</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="container">
    <h1 class="page-title">My Profile</h1>

    <div class="card">
        <div class="profile-header">
            <div class="avatar"><?php echo strtoupper(substr($user['username'] ?? 'U', 0, 1)); ?></div>
            <h3><?php echo $user['full_name'] ?? ''; ?></h3>
            <p>@<?php echo $user['username'] ?? ''; ?></p>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="profile.php">
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name"
                       value="<?php echo $user['full_name'] ?? ''; ?>">
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="text" id="email" name="email"
                       value="<?php echo $user['email'] ?? ''; ?>">
            </div>
            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" id="password" name="password" placeholder="Leave blank to keep current">
                <div class="hint">Only fill this if you want to change your password.</div>
            </div>
            <button type="submit" class="btn-submit">Update Profile</button>
        </form>
    </div>
</div>

<footer class="footer">&copy; 2026 SecureVault Bank &mdash; <em>Training Environment Only</em></footer>

</body>
</html>
