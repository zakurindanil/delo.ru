<?php
    session_start();

    header('Content-Type: application/json');

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['ok' => false, 'error' => 'Не авторизован']);
        exit;
    }

    require 'config.php';

    $stmt = $pdo->prepare('SELECT fio, role, email, phone, avatar FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);


    echo json_encode(['ok' => true, 'user' => $user]);