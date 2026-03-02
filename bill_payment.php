<?php
require_once __DIR__ . '/functions.php';
requireLogin();

$userId       = currentUserId();
$user         = getUserById($userId);
$balance      = getBalance($userId);
$billers      = getBillers();
$virtualCards = getVirtualCards($userId);

$billMessage = '';
$billError   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'pay_bill') {
    $billerId        = (int)$_POST['biller_id'];
    $amount          = $_POST['amount'];
    $paymentMethod   = $_POST['payment_method'];
    $referenceNumber = trim($_POST['reference_number'] ?? '');
    $cardId          = NULL;

    if ($paymentMethod === 'card') {
        $cardNumber  = trim($_POST['card_number'] ?? '');
        $matchedCard = null;
        foreach ($virtualCards as $vc) {
            if ($vc['cardnumber'] === $cardNumber) {
                $matchedCard = $vc;
                break;
            }
        }
        if (!$matchedCard) {
            $billError = "Card number not found among your virtual cards.";
        } else {
            $cardId = (int)$matchedCard['id'];
        }
    }

    if (!$billError) {
        $result = payBill($userId, $billerId, $amount, $paymentMethod, $referenceNumber, $cardId);
        if ($result === true) {
            $billMessage  = "Bill paid successfully!";
            $balance      = getBalance($userId);
            $virtualCards = getVirtualCards($userId);
        } else {
            $billError = is_string($result) ? $result : "Payment failed. Please try again.";
        }
    }
}

function getBillPayments($userId) {
    global $conn;
    $query = "SELECT bp.*, b.name AS biller_name
              FROM billPayments bp
              JOIN billers b ON bp.billerid = b.id
              WHERE bp.userid = $userId
              ORDER BY bp.createdat DESC
              LIMIT 20";
    $result = pg_query($conn, $query);
    if ($result) return pg_fetch_all($result);
    return [];
}

$billHistory = getBillPayments($userId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill Payments — SecureVault Bank</title>
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
        .navbar nav a:hover, .navbar nav a.active { color: var(--gold); }

        .container { max-width: 960px; margin: 2.5rem auto; padding: 0 2rem; }
        .page-header { margin-bottom: 2rem; }
        .page-header h1 { font-size: 1.5rem; color: var(--navy); }
        .page-header p  { font-size: .9rem; color: #666; margin-top: .25rem; }

        .balance-strip { background: linear-gradient(135deg, var(--navy), var(--blue)); color: #fff; border-radius: 12px; padding: 1.2rem 2rem; box-shadow: var(--shadow); margin-bottom: 2rem; }
        .balance-strip .label  { font-size: .85rem; opacity: .7; }
        .balance-strip .amount { font-size: 1.8rem; font-weight: 700; }

        .alert { padding: .85rem 1.2rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: .9rem; }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert-error   { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

        .form-card { background: #fff; border-radius: 12px; padding: 2rem 2.5rem; box-shadow: var(--shadow); margin-bottom: 3rem; }
        .form-card h2 { font-size: 1.1rem; color: var(--blue); margin-bottom: 1.5rem; padding-bottom: .75rem; border-bottom: 1px solid #eee; }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; }
        @media (max-width: 600px) { .form-grid { grid-template-columns: 1fr; } }

        .form-group { display: flex; flex-direction: column; gap: .35rem; }
        .form-group.full { grid-column: 1 / -1; }
        .form-group label { font-size: .85rem; font-weight: 600; color: #444; }
        .form-group select,
        .form-group input[type="text"],
        .form-group input[type="number"] { padding: .65rem .9rem; border: 1px solid #d1d5db; border-radius: 8px; font-size: .9rem; color: #333; transition: border-color .2s; background: #fafafa; }
        .form-group select:focus,
        .form-group input:focus { outline: none; border-color: var(--gold); background: #fff; }

        .method-group { display: flex; gap: 1.5rem; margin-top: .2rem; }
        .method-option { display: flex; align-items: center; gap: .5rem; cursor: pointer; }
        .method-option input[type="radio"] { accent-color: var(--gold); width: 16px; height: 16px; cursor: pointer; }
        .method-option span { font-size: .9rem; color: #333; }

        #card-number-group { display: none; }
        #card-number-group.visible { display: flex; }

        .btn-pay { margin-top: 1.5rem; padding: .75rem 2.5rem; border: none; border-radius: 8px; background: var(--gold); color: var(--navy); font-size: 1rem; font-weight: 700; cursor: pointer; transition: background .2s; }
        .btn-pay:hover { background: var(--gold-light); }

        .section-title { font-size: 1.15rem; color: var(--blue); margin-bottom: 1rem; }
        .tx-table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: var(--shadow); }
        .tx-table th { background: var(--navy); color: var(--gold); text-align: left; padding: .85rem 1rem; font-size: .8rem; text-transform: uppercase; letter-spacing: .5px; }
        .tx-table td { padding: .8rem 1rem; border-bottom: 1px solid #eee; font-size: .88rem; }
        .tx-table tr:last-child td { border-bottom: none; }
        .tx-table tr:hover td { background: #fafafa; }
        .badge { display: inline-block; padding: .2rem .65rem; border-radius: 20px; font-size: .75rem; font-weight: 600; }
        .badge-balance { background: #dbeafe; color: #1e40af; }
        .badge-card    { background: #f3e8ff; color: #6b21a8; }
        .amount-out { color: #dc2626; font-weight: 600; }
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
        <a href="bill_payment.php" class="active">Bill Payment</a>
        <a href="profile.php">Profile</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="container">

    <div class="page-header">
        <h1>Bill Payments</h1>
        <p>Pay your bills quickly and securely from your balance or a virtual card.</p>
    </div>

    <div class="balance-strip">
        <div class="label">Account Balance</div>
        <div class="amount">$<?php echo number_format((float)$balance, 2); ?></div>
    </div>

    <?php if ($billMessage): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($billMessage); ?></div>
    <?php endif; ?>
    <?php if ($billError): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($billError); ?></div>
    <?php endif; ?>

    <div class="form-card">
        <h2>New Bill Payment</h2>
        <form method="POST">
            <input type="hidden" name="action" value="pay_bill">
            <div class="form-grid">

                <div class="form-group">
                    <label for="biller_id">Biller</label>
                    <select name="biller_id" id="biller_id" required>
                        <option value="" disabled selected>— Select a biller —</option>
                        <?php foreach ($billers as $biller): ?>
                            <option value="<?php echo (int)$biller['id']; ?>">
                                <?php echo htmlspecialchars($biller['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="amount">Amount ($)</label>
                    <input type="number" name="amount" id="amount" min="0.01" step="0.01" placeholder="0.00" required>
                </div>

                <div class="form-group">
                    <label for="reference_number">Reference / Account Number</label>
                    <input type="text" name="reference_number" id="reference_number" placeholder="e.g. ACC-00123456">
                </div>

                <div class="form-group">
                    <label>Payment Method</label>
                    <div class="method-group">
                        <label class="method-option">
                            <input type="radio" name="payment_method" value="balance" id="method-balance" checked onchange="toggleCardInput()">
                            <span>Account Balance</span>
                        </label>
                        <label class="method-option">
                            <input type="radio" name="payment_method" value="card" id="method-card" onchange="toggleCardInput()">
                            <span>Virtual Card</span>
                        </label>
                    </div>
                </div>

                <div class="form-group full" id="card-number-group">
                    <label for="card_number">Card Number</label>
                    <input type="text" name="card_number" id="card_number"
                           placeholder="Enter your 16-digit virtual card number"
                           maxlength="16" pattern="\d{16}">
                    <?php if (!empty($virtualCards)): ?>
                        <small style="color:#666;margin-top:.3rem;font-size:.8rem;">
                            Your cards:
                            <?php foreach ($virtualCards as $i => $vc): ?>
                                <code style="cursor:pointer;color:var(--blue);text-decoration:underline dotted;"
                                      onclick="document.getElementById('card_number').value='<?php echo $vc['cardnumber']; ?>'">
                                    <?php echo implode(' ', str_split($vc['cardnumber'], 4)); ?>
                                </code><?php echo ($i < count($virtualCards) - 1) ? ', ' : ''; ?>
                            <?php endforeach; ?>
                        </small>
                    <?php else: ?>
                        <small style="color:#999;margin-top:.3rem;font-size:.8rem;">
                            You have no virtual cards. <a href="dashboard.php" style="color:var(--blue);">Create one first.</a>
                        </small>
                    <?php endif; ?>
                </div>

            </div>

            <button type="submit" class="btn-pay">Pay Bill</button>
        </form>
    </div>

    <h2 class="section-title">Payment History</h2>

    <?php if (empty($billHistory)): ?>
        <div class="empty-state">No bill payments yet.</div>
    <?php else: ?>
        <table class="tx-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Biller</th>
                    <th>Reference</th>
                    <th>Method</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($billHistory as $bp): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($bp['createdat']); ?></td>
                        <td><?php echo htmlspecialchars($bp['biller_name']); ?></td>
                        <td><?php echo htmlspecialchars($bp['referencenumber'] ?? '—'); ?></td>
                        <td>
                            <?php if ($bp['paymentmethod'] === 'card'): ?>
                                <span class="badge badge-card">Virtual Card</span>
                            <?php else: ?>
                                <span class="badge badge-balance">Balance</span>
                            <?php endif; ?>
                        </td>
                        <td class="amount-out">-$<?php echo number_format((float)$bp['amount'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

</div>

<footer class="footer">&copy; 2026 SecureVault Bank &mdash; <em>Training Environment Only</em></footer>

<script>
function toggleCardInput() {
    const cardGroup = document.getElementById('card-number-group');
    const cardInput = document.getElementById('card_number');
    const isCard    = document.getElementById('method-card').checked;
    if (isCard) {
        cardGroup.classList.add('visible');
        cardInput.setAttribute('required', 'required');
    } else {
        cardGroup.classList.remove('visible');
        cardInput.removeAttribute('required');
        cardInput.value = '';
    }
}
</script>
</body>
</html>