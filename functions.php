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
    return 'Not implemented';
}

function getTransactions($userId, $limit = 10) {
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
