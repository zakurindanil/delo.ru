<?php
    session_start();
    header("Content-Type: application/json");

    $data = json_decode(file_get_contents('php://input'), true);
    $code = trim($data['code'] ?? '');

    if (!isset($_SESSION['reset_code'], $_SESSION['reset_expires'])) {
        echo json_encode(['ok' => false, 'error' => 'Сессия истекла. Запросите код заново.']);
        exit;
    }

    if (time() > $_SESSION['reset_expires']) {
        unset($_SESSION['reset_code'], $_SESSION['reset_email'], $_SESSION['reset_expires']);
        echo json_encode(['ok' => false, 'error' => 'Код истёк. Запросите новый.']);
        exit;
    }

    if ($code !== $_SESSION['reset_code']) {
        echo json_encode(['ok' => false, 'error' => 'Неверный код']);
        exit;
    }

    // Код верный — разрешаем смену пароля
    $_SESSION['reset_verified'] = true;
    echo json_encode(['ok' => true]);