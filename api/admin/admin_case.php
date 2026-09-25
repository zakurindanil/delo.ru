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
                j.fio AS judgeName,
                j.email AS judgeEmail
            FROM cases c
            JOIN case_statuses s ON c.status_id = s.id
            JOIN case_types t ON c.type_id = t.id
            JOIN case_subtypes st ON c.subtype_id = st.id
            JOIN users u ON c.applicant_id = u.id
            LEFT JOIN users j ON c.judge_id = j.id
            ORDER BY c.created_at DESC
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
            c.id AS number,
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
            u.phone AS phone
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

    if ($action === 'send_reply') {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['ok' => false, 'error' => 'Не авторизован']);
            exit;
        }

        $case_id = (int)($_POST['case_id'] ?? 0);
        $text    = trim($_POST['text'] ?? '');

        if (!$case_id || !$text) {
            echo json_encode(['ok' => false, 'error' => 'Введите текст ответа']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT applicant_id FROM cases WHERE id = ?");
        $stmt->execute([$case_id]);
        $applicantId = $stmt->fetchColumn();

        if (!$applicantId) {
            echo json_encode(['ok' => false, 'error' => 'Дело не найдено']);
            exit;
        }

        $stmt = $pdo->prepare("
            INSERT INTO messages (case_id, sender_id, receiver_id, text)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$case_id, $_SESSION['user_id'], $applicantId, $text]);

        echo json_encode(['ok' => true]);
        exit;
    }

    if ($action === 'get_messages') {
        $case_id = (int)($_GET['case_id'] ?? 0);
    
        $stmt = $pdo->prepare("
            SELECT
                m.id,
                m.text,
                m.sender_id,
                m.created_at,
                u.fio  AS senderName,
                u.role AS senderRole
            FROM messages m
            JOIN users u ON m.sender_id = u.id
            WHERE m.case_id = ?
            ORDER BY m.created_at
        ");
        $stmt->execute([$case_id]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        echo json_encode(['ok' => true, 'messages' => $messages]);
        exit;
    }

    if ($action === 'get_judjes') {
        $stmt = $pdo->query("
            SELECT id, fio, email, phone
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