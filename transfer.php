<?php
require_once __DIR__ . '/functions.php';
requireLogin();

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $toUsername  = $_POST['to_username'];
    $amount      = $_POST['amount'];
    $description = $_POST['description'];

    $result = transferFunds(currentUserId(), $toUsername, $amount, $description);

    if ($result === true) {
        $success = "Transfer of $$amount to <strong>$toUsername</strong> completed successfully.";
    } else {
        $error = $result;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transfer — SecureVault Bank</title>
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
        .form-group { margin-bottom: 1.2rem; }
        .form-group label { display: block; font-weight: 600; margin-bottom: .35rem; font-size: .9rem; color: var(--blue); }
        .form-group input, .form-group textarea {
            width: 100%; padding: .75rem 1rem; border: 1px solid #ccc; border-radius: 6px;
            font-size: .95rem; font-family: inherit; transition: border-color .2s;
        }
        .form-group input:focus, .form-group textarea:focus { outline: none; border-color: var(--gold); }
        .form-group textarea { resize: vertical; min-height: 80px; }

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
    <h1 class="page-title">Transfer Funds</h1>

    <div class="card">
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="transfer.php">
            <div class="form-group">
                <label for="to_username">Recipient Username</label>
                <input type="text" id="to_username" name="to_username" placeholder="e.g. johndoe" required
                       value="<?php echo $_POST['to_username'] ?? ''; ?>">
            </div>
            <div class="form-group">
                <label for="amount">Amount ($)</label>
                <input type="text" id="amount" name="amount" placeholder="0.00" required
                       value="<?php echo $_POST['amount'] ?? ''; ?>">
            </div>
            <div class="form-group">
                <label for="description">Description (optional)</label>
                <textarea id="description" name="description" placeholder="What's this transfer for?"><?php echo $_POST['description'] ?? ''; ?></textarea>
            </div>
            <button type="submit" class="btn-submit">Send Money</button>
        </form>
    </div>
</div>

<footer class="footer">&copy; 2026 SecureVault Bank &mdash; <em>Training Environment Only</em></footer>

</body>
</html>
