<?php
require_once __DIR__ . '/functions.php';
requireLogin();

$userId       = currentUserId();
$user         = getUserById($userId);
$balance      = getBalance($userId);
$transactions = getTransactions($userId, 10);
$virtualCards = getVirtualCards($userId);

// Handle virtual card actions
$cardMessage = '';
$cardError   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {

        if ($_POST['action'] === 'create_card') {
            $cardType = $_POST['card_type'] ?? 'standard';
            $result   = createVirtualCard($userId, $cardType);
            if ($result) {
                $cardMessage = "Virtual card created successfully!";
                $virtualCards = getVirtualCards($userId); // refresh
            } else {
                $cardError = "Failed to create virtual card.";
            }

        } elseif ($_POST['action'] === 'fund_card') {
            $cardId = (int)$_POST['card_id'];
            $amount = $_POST['amount'];
            $result = fundCard($userId, $cardId, $amount);
            if ($result === true) {
                $cardMessage = "Card funded successfully!";
                $balance      = getBalance($userId);      // refresh
                $virtualCards = getVirtualCards($userId);
            } else {
                $cardError = is_string($result) ? $result : "Failed to fund card.";
            }

        } elseif ($_POST['action'] === 'terminate_card') {
            $cardId = (int)$_POST['card_id'];
            $result = terminateCard($userId, $cardId);
            if ($result === true) {
                $cardMessage = "Card terminated and balance returned to your account.";
                $balance      = getBalance($userId);
                $virtualCards = getVirtualCards($userId);
            } else {
                $cardError = "Failed to terminate card.";
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

        /* ── Virtual Cards ── */
        .cards-section { margin-top: 3rem; }

        .alert {
            padding: .85rem 1.2rem; border-radius: 8px; margin-bottom: 1.2rem; font-size: .9rem;
        }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert-error   { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

        /* Create-card panel */
        .create-card-panel {
            background: #fff; border-radius: 10px; padding: 1.5rem 2rem;
            box-shadow: var(--shadow); margin-bottom: 2rem; display: flex;
            align-items: center; gap: 1rem; flex-wrap: wrap;
        }
        .create-card-panel label { font-size: .9rem; color: #555; margin-right: .25rem; }
        .create-card-panel select, .create-card-panel button {
            padding: .55rem 1rem; border-radius: 6px; border: 1px solid #d1d5db;
            font-size: .9rem; cursor: pointer;
        }
        .create-card-panel button {
            background: var(--gold); color: var(--navy); font-weight: 700; border: none;
            transition: background .2s;
        }
        .create-card-panel button:hover { background: var(--gold-light); }

        /* Card grid */
        .cards-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        /* Individual virtual card */
        .vcard {
            border-radius: 14px; padding: 1.6rem 1.8rem;
            box-shadow: 0 8px 30px rgba(0,0,0,.15); position: relative; overflow: hidden;
            color: #fff; min-height: 175px; display: flex; flex-direction: column; justify-content: space-between;
        }
        .vcard.standard { background: linear-gradient(135deg, #1a3a5c 0%, #0a1628 100%); }
        .vcard.premium  { background: linear-gradient(135deg, #7c5f1a 0%, #3d2c06 100%); }

        /* decorative circles */
        .vcard::before, .vcard::after {
            content: ''; position: absolute; border-radius: 50%; opacity: .15;
        }
        .vcard::before { width: 180px; height: 180px; background: #fff; top: -60px; right: -50px; }
        .vcard::after  { width: 120px; height: 120px; background: #fff; bottom: -40px; left: -30px; }

        .vcard-header { display: flex; justify-content: space-between; align-items: flex-start; }
        .vcard-type   { font-size: .72rem; text-transform: uppercase; letter-spacing: 1.5px; opacity: .7; }
        .vcard-badge  {
            font-size: .7rem; padding: .2rem .6rem; border-radius: 20px;
            background: rgba(255,255,255,.15); letter-spacing: .5px; text-transform: uppercase;
        }
        .vcard-number { font-size: 1.1rem; letter-spacing: 3px; font-family: 'Courier New', monospace; margin: .9rem 0 .3rem; }
        .vcard-details { display: flex; gap: 2rem; font-size: .78rem; opacity: .8; }
        .vcard-details span b { display: block; font-size: .65rem; opacity: .6; text-transform: uppercase; letter-spacing: .5px; margin-bottom: .1rem; }

        .vcard-footer { display: flex; justify-content: space-between; align-items: center; margin-top: .8rem; }
        .vcard-balance { font-size: .9rem; }
        .vcard-balance b { display: block; font-size: 1.35rem; font-weight: 700; }
        .vcard-balance small { font-size: .7rem; opacity: .65; }

        /* Card action buttons */
        .card-actions { display: flex; gap: .5rem; flex-wrap: wrap; margin-top: 1rem; }
        .card-actions form { display: flex; gap: .4rem; align-items: center; }
        .card-actions input[type="number"] {
            width: 90px; padding: .35rem .6rem; border-radius: 6px;
            border: 1px solid rgba(255,255,255,.35); background: rgba(255,255,255,.12);
            color: #fff; font-size: .8rem;
        }
        .card-actions input[type="number"]::placeholder { color: rgba(255,255,255,.5); }
        .btn-fund {
            padding: .35rem .85rem; border-radius: 6px; border: none; cursor: pointer;
            background: rgba(255,255,255,.25); color: #fff; font-size: .8rem; font-weight: 600;
            transition: background .2s;
        }
        .btn-fund:hover { background: rgba(255,255,255,.4); }
        .btn-terminate {
            padding: .35rem .85rem; border-radius: 6px; border: none; cursor: pointer;
            background: rgba(220,38,38,.5); color: #fff; font-size: .8rem; font-weight: 600;
            transition: background .2s;
        }
        .btn-terminate:hover { background: rgba(220,38,38,.8); }

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

    <!-- ══════════════════════════════════
         VIRTUAL CARDS SECTION
    ═══════════════════════════════════ -->
    <div class="cards-section">
        <h2 class="section-title">Virtual Cards</h2>

        <?php if ($cardMessage): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($cardMessage); ?></div>
        <?php endif; ?>
        <?php if ($cardError): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($cardError); ?></div>
        <?php endif; ?>

        <!-- Create new card -->
        <div class="create-card-panel">
            <form method="POST" style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
                <input type="hidden" name="action" value="create_card">
                <label for="card_type">Card Type:</label>
                <select name="card_type" id="card_type">
                    <option value="standard">Standard (limit $10,000)</option>
                    <option value="premium">Premium (limit $50,000)</option>
                </select>
                <button type="submit">+ Create New Card</button>
            </form>
        </div>

        <!-- Cards grid -->
        <?php if (empty($virtualCards)): ?>
            <div class="empty-state">No virtual cards yet. Create one above!</div>
        <?php else: ?>
            <div class="cards-grid">
                <?php foreach ($virtualCards as $card): ?>
                    <?php
                        $isPremium = $card['cardtype'] === 'premium';
                        // Format card number in groups of 4
                        $num = $card['cardnumber'];
                        $formatted = implode(' ', str_split($num, 4));
                    ?>
                    <div class="vcard <?php echo htmlspecialchars($card['cardtype']); ?>">
                        <div>
                            <div class="vcard-header">
                                <div class="vcard-type">SecureVault <?php echo ucfirst($card['cardtype']); ?></div>
                                <div class="vcard-badge">Virtual</div>
                            </div>
                            <div class="vcard-number"><?php echo htmlspecialchars($formatted); ?></div>
                            <div class="vcard-details">
                                <span><b>Expires</b><?php echo htmlspecialchars($card['expirydate']); ?></span>
                                <span><b>CVV</b><?php echo htmlspecialchars($card['cvv']); ?></span>
                                <span><b>Limit</b>$<?php echo number_format((float)$card['cardlimit'], 0); ?></span>
                            </div>
                        </div>

                        <div>
                            <div class="vcard-footer">
                                <div class="vcard-balance">
                                    <small>Card Balance</small>
                                    <b>$<?php echo number_format((float)$card['balance'], 2); ?></b>
                                </div>
                            </div>

                            <!-- Fund & Terminate actions -->
                            <div class="card-actions">
                                <!-- Fund card -->
                                <form method="POST">
                                    <input type="hidden" name="action" value="fund_card">
                                    <input type="hidden" name="card_id" value="<?php echo (int)$card['id']; ?>">
                                    <input type="number" name="amount" min="1" step="0.01"
                                           placeholder="Amount" required>
                                    <button type="submit" class="btn-fund">Fund</button>
                                </form>

                                <!-- Terminate card -->
                                <form method="POST" onsubmit="return confirm('Terminate this card? Balance will be returned to your account.');">
                                    <input type="hidden" name="action" value="terminate_card">
                                    <input type="hidden" name="card_id" value="<?php echo (int)$card['id']; ?>">
                                    <button type="submit" class="btn-terminate">Terminate</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <!-- END VIRTUAL CARDS -->

</div>

<footer class="footer">&copy; 2026 SecureVault Bank &mdash; <em>Training Environment Only</em></footer>

</body>
</html>