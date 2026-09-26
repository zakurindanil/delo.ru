<?php
    session_start();
    header("Content-Type: application/json");

    require("../config.php");

    $action = $_GET["action"] ?? $_POST['action'] ?? '';

    if ($action === 'get_cases') {
        $stmt = $pdo->prepare("
            SELECT
                c.id AS id,
                s.name AS status,
                s.color AS statusColor,
                t.name AS typeCase,
                st.name AS subtypeCase,
                c.comment AS comment,
                u.fio AS applicant,
                u.email AS email,
                u.phone AS phone,
                u.avatar AS avatar
            FROM cases c
            JOIN case_statuses s ON c.status_id = s.id
            JOIN case_types t ON c.type_id = t.id
            JOIN case_subtypes st ON c.subtype_id = st.id
            JOIN users u ON c.applicant_id = u.id
            WHERE s.name = 'Новое'
            ORDER BY c.created_at DESC
        ");
        $stmt->execute();
        $cases = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['ok' => true, 'cases' => $cases]);
        exit;
    }

    if ($action === 'get_cases_for_judje') {
        $stmt = $pdo->prepare("
            SELECT
                c.id AS id,
                s.name AS status,
                s.color AS statusColor,
                t.name AS typeCase,
                st.name AS subtypeCase,
                c.comment AS comment,
                u.fio AS applicant,
                u.email AS email,
                u.phone AS phone,
                u.avatar AS avatar
            FROM cases c
            JOIN case_statuses s ON c.status_id = s.id
            JOIN case_types t ON c.type_id = t.id
            JOIN case_subtypes st ON c.subtype_id = st.id
            JOIN users u ON c.applicant_id = u.id
            WHERE s.name = 'В работе'
              AND c.judge_id IS NULL
            ORDER BY c.created_at ASC
        ");
        $stmt->execute();
        $cases = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['ok' => true, 'cases' => $cases]);
        exit;
    }

    if ($action === 'get_case') {

        $caseId = (int)($_GET['id'] ?? 0);

        $stmt = $pdo->prepare("
        SELECT
            c.id AS id,
            c.type_id AS type_id,
            c.subtype_id AS subtype_id,
            c.comment AS comment,
            c.admin_comment AS admin_comment,
            t.name AS typeCase,
            st.name AS subtypeCase,
            s.name AS status,
            s.color AS statusColor,
            u.fio AS applicant,
            u.email AS email,
            u.phone AS phone,
            u.avatar AS avatar
        FROM cases c
        JOIN case_types t ON c.type_id = t.id
        JOIN case_subtypes st ON c.subtype_id = st.id
        JOIN case_statuses s ON c.status_id  = s.id
        JOIN users u ON c.applicant_id = u.id
        WHERE c.id = ?
        ");
        $stmt->execute([$caseId]);
        $case = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$case) {
            echo json_encode(['ok' => false, 'error' => 'Дело не найдено']);
            exit;
        }

        $stmt = $pdo->prepare("
            SELECT id, file_name, file_path, file_size
            FROM documents
            WHERE case_id = ?
            ORDER BY uploaded_at
        ");
        $stmt->execute([$caseId]);
        $files = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'ok'    => true,
            'case'  => $case,
            'files' => $files
        ]);
        exit;
    }

    if ($action === 'save_reply') {

        $case_id = (int)($_POST['case_id'] ?? 0);
        $text = trim($_POST['text'] ?? '');
        $statusName = trim($_POST['decision']  ?? '');

        if (!$statusName) {
            echo json_encode(['ok' => false, 'error' => 'Выберите решение']);
            exit;
        }

        if (!$case_id || !$text) {
            echo json_encode(['ok' => false, 'error' => 'Введите текст ответа']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT id FROM case_statuses WHERE name = ? LIMIT 1");
        $stmt->execute([$statusName]);
        $statusId = $stmt->fetchColumn();

        if (!$statusId) {
            echo json_encode(['ok' => false, 'error' => 'Неверный статус']);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE cases SET admin_comment = ?, status_id = ? WHERE id = ?");
        $stmt->execute([$text, $statusId, $case_id]);

        echo json_encode(['ok' => true]);
        exit;
    }

    if ($action === 'get_judjes') {
        $stmt = $pdo->query("
            SELECT id, fio, email, phone, avatar
            FROM users
            WHERE role = 'judge'
            ORDER BY fio
        ");
        $judjes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['ok' => true, 'judjes' => $judjes]);
        exit;
    }

    if ($action === 'assign_judje') {
        $case_id  = (int)($_POST['case_id'] ?? 0);
        $judge_id = (int)($_POST['judge_id'] ?? 0);

        if (!$case_id || !$judge_id) {
            echo json_encode(['ok' => false, 'error' => 'Выберите дело и судью']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ? AND role = 'judge'");
        $stmt->execute([$judge_id]);
        if (!$stmt->fetch()) {
            echo json_encode(['ok' => false, 'error' => 'Судья не найден']);
            exit;
        }

        $stmt = $pdo->prepare("
            UPDATE cases
            SET judge_id = ?,
                status_id = (SELECT id FROM case_statuses WHERE name = 'Назначено' LIMIT 1)
            WHERE id = ?
        ");
        $stmt->execute([$judge_id, $case_id]);

        echo json_encode(['ok' => true]);
        exit;
    }

    if ($action === 'update_judje') {
        $id = (int)($_POST['id'] ?? 0);
        $fio = trim($_POST["fio"] ?? '');
        $email = trim($_POST["email"] ?? '');
        $phone = trim($_POST["phone"] ?? '');
    
        if (!preg_match('/^[А-Яа-яЁёA-Za-z\s\-]+$/u', $fio)) {
            echo json_encode(['ok' => false, 'error' => 'ФИО содержит недопустимые символы']);
            exit;
        }
    
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['ok' => false, 'error' => 'Некорректный email']);
            exit;
        }
    
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $id]);
        if ($stmt->fetch()) {
            echo json_encode(['ok' => false, 'error' => 'Email уже занят']);
            exit;
        }
    
        if (!preg_match('/^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/', $phone)) {
            echo json_encode(['ok' => false, 'error' => 'Телефон в формате +7 (999) 123-45-67']);
            exit;
        }
    
        $stmt = $pdo->prepare("UPDATE users SET fio = ?, email = ?, phone = ? WHERE id = ? AND role = 'judge'");
        $stmt->execute([$fio, $email, $phone, $id]);
    
        echo json_encode(['ok' => true]);
    }

    if ($action == 'delete_judje') {
        $id = (int)($_POST['id'] ?? 0);

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM cases WHERE judge_id = ?");
        $stmt->execute([$id]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            echo json_encode(['ok' => false, 'error' => "У судьи $count дел. Сначала переназначьте их."]);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'judge'");
        $stmt->execute([$id]);

        echo json_encode(['ok' => true]);
        exit;
    }