<?php
    session_start();
    header("Content-Type: application/json");

    require("config.php");

    $user_id = $_SESSION["user_id"];
    $fio = trim($_POST["fio"] ?? '');
    $email = trim($_POST["email"] ?? '');
    $phone = trim($_POST["phone"] ?? '');
    $password = $_POST["password"] ?? '';

    if (!preg_match('/^[А-Яа-яЁёA-Za-z\s\-]+$/u', $fio)) {
        echo json_encode(['ok' => false, 'error' => 'ФИО содержит недопустимые символы']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['ok' => false, 'error' => 'Некорректный email']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $user_id]);
    if ($stmt->fetch()) {
        echo json_encode(['ok' => false, 'error' => 'Email уже занят']);
        exit;
    }

    if (!preg_match('/^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/', $phone)) {
        echo json_encode(['ok' => false, 'error' => 'Телефон в формате +7 (999) 123-45-67']);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE users SET fio = ?, email = ?, phone = ? WHERE id = ?");
    $stmt->execute([$fio, $email, $phone, $user_id]);

    if ($password) {
        if (strlen($password) < 8) {
            echo json_encode(['ok' => false, 'error' => 'Пароль минимум 8 символов']);
            exit;
        }
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $stmt->execute([$hash, $user_id]);
    }

    if (!empty($_FILES['avatar']['name'])) {
        $uploadDir = __DIR__ . '/../resourses/img/avatars/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $ext      = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $fileName = uniqid('avatar_') . '.' . $ext;

        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadDir . $fileName)) {
            $avatarPath = '../resourses/img/avatars/' . $fileName;
            $stmt = $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?");
            $stmt->execute([$avatarPath, $user_id]);
        }
    }

    echo json_encode(['ok' => true]);