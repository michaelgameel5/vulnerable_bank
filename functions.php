<?php


require_once __DIR__ . '/db_connect.php';

define('JWT_SECRET', 's3cr3t_k3y_vuln_bank_2026');
define('JWT_EXPIRY', 3600);

function base64UrlEncode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64UrlDecode($data) {
    return base64_decode(strtr($data, '-_', '+/'));
}

function jwtEncode($payload) {
    $header = base64UrlEncode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $payload['iat'] = time();
    $payload['exp'] = time() + JWT_EXPIRY;
    $payloadEncoded = base64UrlEncode(json_encode($payload));
    $signature = base64UrlEncode(hash_hmac('sha256', "$header.$payloadEncoded", JWT_SECRET, true));
    return "$header.$payloadEncoded.$signature";
}

function jwtDecode($token) {
    $parts = explode('.', $token);
    if (count($parts) !== 3) return false;

    list($header, $payload, $signature) = $parts;
    $validSignature = base64UrlEncode(hash_hmac('sha256', "$header.$payload", JWT_SECRET, true));

    if ($signature !== $validSignature) return false;

    $data = json_decode(base64UrlDecode($payload), true);
    if (!$data) return false;

    if (isset($data['exp']) && $data['exp'] < time()) return false;

    return $data;
}

function loginUser($username, $password) {
    global $conn;
    $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    $result = pg_query($conn, $query);
    if ($result && pg_num_rows($result) === 1) {
        $user = pg_fetch_assoc($result);
        $token = jwtEncode([
            'user_id'  => $user['id'],
            'username' => $user['username']
        ]);
        setcookie('jwt_token', $token, time() + JWT_EXPIRY, '/');
        return $user;
    }
    return false;
}

function registerUser($username, $password, $fullName, $email) {
    global $conn;
    $query = "INSERT INTO users (username, password, full_name, email) VALUES ('$username', '$password', '$fullName', '$email')";
    $result = pg_query($conn, $query);
    if ($result) {
        $user = getUserByUsername($username);
        if ($user) {
            $token = jwtEncode([
                'user_id'  => $user['id'],
                'username' => $user['username']
            ]);
            setcookie('jwt_token', $token, time() + JWT_EXPIRY, '/');
        }
        return $user;
    }
    return false;
}

function getUserById($userId) {
    global $conn;
    $query = "SELECT * FROM users WHERE id = $userId";
    $result = pg_query($conn, $query);
    if ($result && pg_num_rows($result) === 1) {
        return pg_fetch_assoc($result);
    }
    return false;
}

function getUserByUsername($username) {
    global $conn;
    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = pg_query($conn, $query);
    if ($result && pg_num_rows($result) === 1) {
        return pg_fetch_assoc($result);
    }
    return false;
}

function getBalance($userId) {
    global $conn;
    $query = "SELECT balance FROM users WHERE id = $userId";
    $result = pg_query($conn, $query);
    if ($result && pg_num_rows($result) === 1) {
        $row = pg_fetch_assoc($result);
        return $row['balance'];
    }
    return 0;
}

function updateProfile($userId, $fullName, $email, $password = '') {
    global $conn;
    $updateFields = "full_name = '$fullName', email = '$email'";
    if (!empty($password)) { # to prevent the risk of making the sign in passwordless.
        $updateFields .= ", password = '$password'";
    }
    $query = "UPDATE users SET $updateFields WHERE id = $userId";
    if (pg_query($conn, $query)) {
        return true;
    }
    return false;
}

function transferFunds($fromUserId, $toUsername, $amount, $description = '') {
    global $conn;
    if (!is_numeric($amount) || $amount < 0) {
        return "Invalid amount. Please enter a positive number.";
    }
    
    $amount = (float)$amount;
    $recipient = getUserByUsername($toUsername);
    if (!$recipient) {
        return "Recipient username not found.";
    }
    
    $toUserId = $recipient['id'];
    if ($fromUserId == $toUserId) {
        return "You cannot transfer funds to yourself.";
    }
    
    $senderBalance = getBalance($fromUserId);
    if ($senderBalance < $amount) {
        return "Insufficient funds. Your balance is \$$senderBalance.";
    }
    
    $newSenderBalance = $senderBalance - $amount;
    $updateSender = "UPDATE users SET balance = $newSenderBalance WHERE id = $fromUserId";
    if (!pg_query($conn, $updateSender)) {
        return "Transfer failed. Please try again.";
    }
    
    $recipientBalance = getBalance($toUserId);
    $newRecipientBalance = $recipientBalance + $amount;
    $updateRecipient = "UPDATE users SET balance = $newRecipientBalance WHERE id = $toUserId";
    if (!pg_query($conn, $updateRecipient)) {
        $rollback = "UPDATE users SET balance = $senderBalance WHERE id = $fromUserId";
        pg_query($conn, $rollback);
        return "Transfer failed. Please try again.";
    }
    
    $description = pg_escape_string($description); # Escape special characters to prevent SQL injection 😊
    $insertTransaction = "INSERT INTO transactions (from_user_id, to_user_id, amount, description) VALUES ($fromUserId, $toUserId, $amount, '$description')";
    if (!pg_query($conn, $insertTransaction)) {
        $rollbackSender = "UPDATE users SET balance = $senderBalance WHERE id = $fromUserId";
        $rollbackRecipient = "UPDATE users SET balance = $recipientBalance WHERE id = $toUserId";
        pg_query($conn, $rollbackSender);
        pg_query($conn, $rollbackRecipient);
        return "Transfer failed. Please try again.";
    }
    
    return true;
}
function requestLoan($amount, $userId) {
    global $conn;

    $loanQuery = "SELECT * FROM loans WHERE user_id = $userId AND status = 'pending'";
    $loanResult = pg_query($conn, $checkSql);

    if (pg_num_rows($loanResult) > 0) {
        return "Pending Loan Exists";
    }
        $query = "INSERT INTO loans (user_id, amount) VALUES ($userId, $amount)";
    $result = pg_query($conn, $query);

    if ($result) {
        return "Loan Requested Successfully";
    } else {
        return "Error submitting request";
    }
}    
function resetPass($userId, $newPass) {
    global $conn;

    $query = "SELECT password FROM users WHERE id = $userId";
    $result = pg_query($conn, $query);
    
    if ($result && pg_num_rows($result) > 0) {
        $row = pg_fetch_assoc($result);
        $oldPass = $row['password'];

            if ($oldPass == $newPass) {
            return "New Password Must Be Different From The Old Password";
            
        }
    }

    $updateQuery = "UPDATE users SET password = '$newPass' WHERE id = $userId";
    $updateResult = pg_query($conn, $updateQuery);

    if ($updateResult) {
        return "Password Updated Successfully";
    } else {
        return "Error Updating Password";
    }
}

function getTransactions($userId, $limit = 10) {
    global $conn;
    $query = "SELECT * FROM transactions WHERE from_user_id = $userId OR to_user_id = $userId ORDER BY created_at DESC LIMIT $limit";
    $result = pg_query($conn, $query);
    if ($result) {
        return pg_fetch_all($result);
    }
    return [];
}

function createVirtualCard($userId, $cardType = 'standard') {
    global $conn;

    $cardLimit = ($cardType === 'premium') ? 50000.00 : 10000.00;
    
    $cardNumber = generateCardNumber();
    $cvv = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
    $expiryDate = date('m/y', strtotime('+3 years'));

    $query = "INSERT INTO virtualCards (userId, cardNumber, cvv, expiryDate, cardLimit, cardType, balance) 
              VALUES ($userId, '$cardNumber', '$cvv', '$expiryDate', $cardLimit, '$cardType', 0.0)";

    if (pg_query($conn, $query)) {
        return [
            'cardNumber' => $cardNumber,
            'cvv'        => $cvv,
            'expiryDate' => $expiryDate,
            'cardLimit'  => $cardLimit,
            'cardType'   => $cardType
        ];
    }
    return false;
}

function fundCard($userId,$cardId, $amount) {
    global $conn;
    if (!is_numeric($amount) || $amount < 0) {
        return "Invalid amount. Please enter a positive number.";
    }
    $amount = (float)$amount;
    $card = getVirtualCardById($cardId);
    if ($card['balance'] + $amount > $card['cardLimit']) {
        return "Funding would exceed card limit.";
    }

    $user=getUserById($userId);
    if($user['balance']<$amount){
        return "Insufficient funds in main account.";
    }
    
    $newMainBalance = $user['balance']-$amount;
    $newCardBalance = $card['balance']+$amount;
    $query1 = "UPDATE users SET balance = $newMainBalance WHERE id = $userId";
    $query2 = "UPDATE virtualCards SET balance = $newCardBalance WHERE id = $cardId";

    if (pg_query($conn, $query1) && pg_query($conn, $query2)) {
        return true;
    }
    return false;
}

function terminateCard($userId, $cardId) {
    global $conn;
    $card = getVirtualCardById($cardId);
    $user = getUserById($userId);
    
    $query1 = "UPDATE users SET balance = balance + {$card['balance']} WHERE id = $userId";
    $query2 = "DELETE FROM virtualCards WHERE id = $cardId";
    if (pg_query($conn, $query1) && pg_query($conn, $query2)) {
        return true;
    }
    return false;
}

function getVirtualCardById($cardId) {
    global $conn;
    $query = "SELECT * FROM virtualCards WHERE id = $cardId";
    $result = pg_query($conn, $query);
    if ($result && pg_num_rows($result) === 1) {
        return pg_fetch_assoc($result);
    }
    return false;
}

function getVirtualCards($userId) {
    global $conn;
    $query = "SELECT * FROM virtualCards WHERE userId = $userId";
    $result = pg_query($conn, $query);
    if ($result) {
        return pg_fetch_all($result);
    }
    return [];
}

function generateCardNumber() {
    $prefix ='4000';
    $cardNumber = $prefix.str_pad(rand(0, 999999999999), 12, '0', STR_PAD_LEFT);
    return $cardNumber;
}

function payBill($userId, $billerId, $amount, $paymentMethod, $referenceNumber, $cardId=NULL){
    if (!is_numeric($amount) || $amount < 0) {
        return "Invalid amount. Please enter a positive number.";
    }

    global $conn;
    $amount = (float)$amount;
    $user = getUserById($userId);
    if($user['balance'] < $amount){
        return "Insufficient funds in main account.";
    }
    if ($paymentMethod == "balance") {
        $newBalance = $user['balance'] - $amount;
        $query = "UPDATE users SET balance = $newBalance WHERE id = $userId";
        if (pg_query($conn, $query)) {
            $query2 = "INSERT INTO billPayments (userId, billerId, amount, paymentMethod, cardId, referenceNumber) VALUES ($userId, $billerId, $amount, '$paymentMethod', NULL, '$referenceNumber')";
            if (pg_query($conn, $query2)) {
                return true;
            }
        }
    } else if ($paymentMethod == "card") {
        if (!$cardId) {
            return "Card ID is required for card payment.";
        }
        $card = getVirtualCardById($cardId);
        if (!$card || $card['balance'] < $amount) {
            return "Insufficient funds in virtual card.";
        }

        $newCardBalance = $card['balance'] - $amount;
        $queryCard = "UPDATE virtualCards SET balance = $newCardBalance WHERE id = $cardId";
        
        if (pg_query($conn, $queryCard)) {
            $query2 = "INSERT INTO billPayments (userId, billerId, amount, paymentMethod, cardId, referenceNumber) VALUES ($userId, $billerId, $amount, '$paymentMethod', '$cardId', '$referenceNumber')";
            if (pg_query($conn, $query2)) {
                return true;
            }
        }
    } else {
        return "Invalid payment method.";
    }
}
                        
function getBillers() {
    global $conn;
    $query = "SELECT * FROM billers WHERE isActive = TRUE";
    $result = pg_query($conn, $query);
    if ($result) {
        return pg_fetch_all($result);
    }
    return [];
}

function isLoggedIn() {
    $payload = getJwtPayload();
    return $payload !== false;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function currentUserId() {
    $payload = getJwtPayload();
    return $payload ? $payload['user_id'] : null;
}

function getJwtPayload() {
    $token = $_COOKIE['jwt_token'] ?? null;
    if (!$token) return false;
    return jwtDecode($token);
}

function currentUsername() {
    $payload = getJwtPayload();
    return $payload ? $payload['username'] : null;
}