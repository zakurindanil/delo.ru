<?php

    header('Content-Type: application/json');

    require 'config.php';

    $fio = trim($_POST['fio'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    // Аватар
    $avatarPath = null;

    if (!empty($_FILES['avatar']['name'])) {
        $file = $_FILES['avatar'];

        if (strpos($file['type'],'image/') !== 0) {
            echo json_encode(['ok' => false, 'error' => 'Только изображения']);
            exit;
        }

        
    }

    if (!$fio || !$email || !$password) {
        echo json_encode(['ok' => false, 'error' => 'Заполните все поля']);
        exit;
    }

    if (!preg_match('/^[А-Яа-яЁёA-Za-z\s\-]+$/u', $fio)) {
        echo json_encode(['ok'=> false, 'error' => 'ФИО содержит недопустимые символы']);
        exit;
    }

    if ($phone && !preg_match('/^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/', $phone)) {
        echo json_encode(['ok'=> false, 'error' => 'Телефон в формате +7 (999) 123-45-67']);
        exit;
    }

    if (strlen($phone) < 8) {
        echo json_encode(['ok'=> false, 'error' => 'Пароль минимум 8 символов']);
    }

    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['ok' => false, 'error' => 'Email уже занят']);
        exit;
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("
        INSERT INTO users (role, fio, email, phone, password_hash, avatar, created_account)
        VALUES ('applicant', ?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$fio, $email, $phone, $hash, null]);
    
    echo json_encode(['ok' => true, 'user_id' => $pdo->lastInsertId()]);