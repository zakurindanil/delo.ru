<?php
    session_start();
    header("Content-Type: application/json");

    require 'config.php';

    $data = json_decode(file_get_contents('php://input'), true);
    $email = trim($data['email'] ?? '');

    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['ok' => false, 'error' => 'Введите корректный email']);
        exit;
    }

    // Проверяем, существует ли пользователь
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // Не раскрываем существование email — но для учебного проекта можно вернуть ошибку
        echo json_encode(['ok' => false, 'error' => 'Пользователь с таким email не найден']);
        exit;
    }

    // Генерируем код
    $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $_SESSION['reset_code'] = $code;
    $_SESSION['reset_email'] = $email;
    $_SESSION['reset_expires'] = time() + 600; // 10 минут

    // Отправка письма
    $subject = 'Код восстановления пароля — ДЕЛО.РУ';
    $message = "Здравствуйте!\n\n"
             . "Ваш код для восстановления пароля: $code\n\n"
             . "Код действителен в течение 10 минут.\n"
             . "Если вы не запрашивали восстановление — просто проигнорируйте это письмо.\n\n"
             . "— ДЕЛО.РУ";
    $headers = "From: no-reply@delo.ru\r\n"
             . "Content-Type: text/plain; charset=utf-8\r\n";

    // В реальном проекте используйте PHPMailer/SMTP.
    // mail() часто отключён на хостингах и попадает в спам.
    @mail($email, $subject, $message, $headers);

    // ВАЖНО: для отладки на локалке возвращаем код в ответе.
    // В продакшене эту строку нужно УДАЛИТЬ.
    echo json_encode([
        'ok' => true,
        'message' => 'Код отправлен на почту',
        'debug_code' => $code // ← удалить в продакшене
    ]);