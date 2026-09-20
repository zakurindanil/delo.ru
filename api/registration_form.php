<?php

    header('Content-Type: application/json');

    require 'config.php';

    $data = json_decode(file_get_contents('php://input'), true);

    $fio = $data['fio'];
    $email = $data['email'];
    $phone = $data['phone'];
    $password = $data['password'];

    if (!$fio || !$email || !$password) {
        echo json_encode(['ok' => false, 'error' => 'Заполните все поля']);
        exit;
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