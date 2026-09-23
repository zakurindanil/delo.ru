<?php
    session_start();
    header("Content-Type: application/json");

    require 'config.php';

    if (empty($_SESSION['reset_verified']) || empty($_SESSION['reset_email'])) {
        echo json_encode(['ok' => false, 'error' => 'Доступ запрещён']);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $password = $data['password'] ?? '';

    if (strlen($password) < 6) {
        echo json_encode(['ok' => false, 'error' => 'Пароль должен быть не короче 6 символов']);
        exit;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE email = ?');
    $stmt->execute([$hash, $_SESSION['reset_email']]);

    // Чистим сессию восстановления
    unset(
        $_SESSION['reset_code'],
        $_SESSION['reset_email'],
        $_SESSION['reset_expires'],
        $_SESSION['reset_verified']
    );

    echo json_encode(['ok' => true, 'message' => 'Пароль успешно изменён']);