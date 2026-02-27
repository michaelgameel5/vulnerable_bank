<?php
require_once __DIR__ . '/functions.php';
requireLogin();

$userId       = currentUserId();
$user         = getUserById($userId);
$balance      = getBalance($userId);
$transactions = getTransactions($userId, 10);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — SecureVault Bank</title>
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

        .container { max-width: 960px; margin: 2.5rem auto; padding: 0 2rem; }
        .greeting { font-size: 1.5rem; color: var(--navy); margin-bottom: 1.5rem; }
        .greeting span { color: var(--gold); }

        .balance-card {
            background: linear-gradient(135deg, var(--navy), var(--blue));
            color: #fff; border-radius: 12px; padding: 2rem 2.5rem;
            box-shadow: var(--shadow); margin-bottom: 2.5rem; display: flex;
            align-items: center; justify-content: space-between;
        }
        .balance-card .label { font-size: .95rem; opacity: .7; margin-bottom: .25rem; }
        .balance-card .amount { font-size: 2.6rem; font-weight: 700; letter-spacing: 1px; }
        .balance-card .actions a {
            display: inline-block; padding: .6rem 1.6rem; border-radius: 6px;
            font-weight: 600; font-size: .9rem; transition: background .2s;
        }
        .btn-transfer { background: var(--gold); color: var(--navy); }
        .btn-transfer:hover { background: var(--gold-light); }

        .section-title { font-size: 1.15rem; color: var(--blue); margin-bottom: 1rem; }
        .tx-table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: var(--shadow); }
        .tx-table th { background: var(--navy); color: var(--gold); text-align: left; padding: .85rem 1rem; font-size: .85rem; text-transform: uppercase; letter-spacing: .5px; }
        .tx-table td { padding: .8rem 1rem; border-bottom: 1px solid #eee; font-size: .9rem; }
        .tx-table tr:last-child td { border-bottom: none; }
        .tx-table tr:hover td { background: #fafafa; }
        .amount-out { color: #dc2626; font-weight: 600; }
        .amount-in  { color: #16a34a; font-weight: 600; }
        .empty-state { text-align: center; padding: 3rem; color: #999; background: #fff; border-radius: 8px; box-shadow: var(--shadow); }

        .footer { text-align: center; padding: 2rem; font-size: .85rem; color: #888; border-top: 1px solid #ddd; margin-top: 3rem; }
    </style>
</head>
<body>

<header class="navbar">
    <a href="index.php" class="logo">Secure<span>Vault</span></a>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="transfer.php">Transfer</a>
        <a href="profile.php">Profile</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="container">
    <h1 class="greeting">Welcome back, <span><?php echo $user['username'] ?? currentUsername(); ?></span></h1>

    <div class="balance-card">
        <div>
            <div class="label">Available Balance</div>
            <div class="amount">$<?php echo number_format((float)$balance, 2); ?></div>
        </div>
        <div class="actions">
            <a href="transfer.php" class="btn-transfer">New Transfer</a>
        </div>
    </div>

    <h2 class="section-title">Recent Transactions</h2>

    <?php if (empty($transactions)): ?>
        <div class="empty-state">No transactions yet. Start by making a transfer!</div>
    <?php else: ?>
        <table class="tx-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>From / To</th>
                    <th>Description</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $tx): ?>
                    <tr>
                        <td><?php echo $tx['created_at']; ?></td>
                        <td>
                            <?php if ($tx['from_user_id'] == $userId): ?>
                                To: <?php echo $tx['to_username'] ?? 'User #' . $tx['to_user_id']; ?>
                            <?php else: ?>
                                From: <?php echo $tx['from_username'] ?? 'User #' . $tx['from_user_id']; ?>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $tx['description']; ?></td>
                        <td class="<?php echo ($tx['from_user_id'] == $userId) ? 'amount-out' : 'amount-in'; ?>">
                            <?php echo ($tx['from_user_id'] == $userId) ? '-' : '+'; ?>
                            $<?php echo number_format((float)$tx['amount'], 2); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<footer class="footer">&copy; 2026 SecureVault Bank &mdash; <em>Training Environment Only</em></footer>

</body>
</html>
