<?php

    session_start();

    require_once __DIR__ . '/../conf/database.php';

    function db_connect()
    {
        global $DB_HOST, $DB_USER, $DB_PASS, $DB_NAME;

        $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, 7823);
        
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        return $conn;
    }

    function is_logged_in()
    {
        if (isset($_SESSION['user_id'])) {
            return true;
        }

        if (isset($_COOKIE['remember_me'])) {
            $token = $_COOKIE['remember_me'];
            $conn = db_connect();

            $stmt = $conn->prepare("SELECT user_id FROM remember_tokens WHERE token = ? AND expires_at > NOW()");
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                $_SESSION['user_id'] = $row['user_id'];
                $stmt->close();
                $conn->close();
                return true;
            }

            $stmt->close();
            $conn->close();
        }
        return false;
    }

    function generate_remember_token()
    {
        return bin2hex(random_bytes(32));
    }

    function set_remember_cookie($user_id)
    {
        $token = generate_remember_token();
        $expires = time() + (86400 * 30);
        setcookie('remember_me', $token, $expires, "/");

        $conn = db_connect();
        $expires_mysql = date('Y-m-d H:i:s', $expires);
        $stmt = $conn->prepare("INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE token = ?, expires_at = ?");

        $stmt->bind_param("issss", $user_id, $token, $expires_mysql, $token, $expires_mysql);
        $stmt->execute();
        $stmt->close();
        $conn->close();
    }

    function delete_remember_cookie()
    {
        if (isset($_COOKIE['remember_me'])) {
            $token = $_COOKIE['remember_me'];
            $conn = db_connect();
            
            $stmt = $conn->prepare("DELETE FROM remember_tokens WHERE token = ?");
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $stmt->close();
            $conn->close();
        }
        setcookie('remember_me', '', time() - 3600, "/");
    }
