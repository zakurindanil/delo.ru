<?php
    session_start();
    header("Content-Type: application/json");

    require 'config.php';

    $data = json_decode(file_get_contents('php://input'), true);

    $login = trim($data['login'] ?? '');
    $password = $data['password'];

    if (!$login || !$password) {
        echo json_encode(['ok' => false, 'error'=> 'Заполните все поля']);
        exit;
    }

    $stmt = $pdo->prepare('SELECT id, role, fio, password_hash FROM users WHERE email = ?');
    $stmt->execute([$login]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['ok'=> false, 'error' => 'Неверный логин или пароль']);
        exit;
    }

    if (!password_verify($password, $user['password_hash'])) {
        echo json_encode(['ok'=> false, 'error' => 'Неверный логин или пароль']);
        exit;
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['fio'] = $user['fio'];

    echo json_encode([
        'ok' => true,
        'user_id' => $user['id'],
        'role' => $user['role'],
        'fio' => $user['fio']
    ]);

