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

function getTransactions($userId, $limit = 10) {
    global $conn;
    $query = "SELECT * FROM transactions WHERE from_user_id = $userId OR to_user_id = $userId ORDER BY created_at DESC LIMIT $limit";
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
