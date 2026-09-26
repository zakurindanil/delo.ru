<?php
    session_start();
    header("Content-Type: application/json");

    require("../config.php");

    $action = $_GET["action"] ?? $_POST['action'] ?? '';

    if ($action === 'submit') {
        $type_id = (int)($_POST['type_id'] ?? 0);
        $subtype_id = (int)($_POST['subtype_id'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');

        if (!$type_id || !$subtype_id || !$comment) {
            echo json_encode(['ok' => false, 'error' => 'Заполните все поля']);
            exit;
        }

        $status_id = $pdo->query("SELECT id FROM case_statuses WHERE name = 'Новое' LIMIT 1")->fetchColumn();

        if (!$status_id) {
            echo json_encode(['ok' => false, 'error' => 'Статус «Новое» не найден']);
            exit;
        }

        $stmt = $pdo->prepare("
            INSERT INTO cases (applicant_id, type_id, subtype_id, status_id, comment)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$_SESSION['user_id'], $type_id, $subtype_id, $status_id, $comment]);

        $caseId = $pdo->lastInsertId();

        if (!empty($_FILES['files']['name'][0])) {
            $uploadDir = __DIR__ . '/../../resourses/files/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $files = $_FILES['files'];

            for ($i = 0; $i < count($files['name']); $i++) {
                if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;

                $ext      = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
                $fileName = uniqid('doc_') . '.' . $ext;

                if (move_uploaded_file($files['tmp_name'][$i], $uploadDir . $fileName)) {
                    $stmt = $pdo->prepare("
                        INSERT INTO documents (case_id, file_name, file_path, file_size, uploaded_by)
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $caseId,
                        $files['name'][$i],
                        'resourses/files/' . $fileName,
                        $files['size'][$i],
                        $_SESSION['user_id']
                    ]);
                }
            }
        }

        echo json_encode(['ok' => true, 'id' => $caseId]);
        exit;
    }

    if ($action === 'get_cases') {
        $stmt = $pdo->prepare("
            SELECT
                c.id AS id,
                s.name AS status,
                s.color AS statusColor,
                t.name AS type,
                st.name AS subType,
                c.comment AS comment
            FROM cases c
            JOIN case_statuses s  ON c.status_id  = s.id
            JOIN case_types    t  ON c.type_id    = t.id
            JOIN case_subtypes st ON c.subtype_id = st.id
            WHERE c.applicant_id = ?
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $cases = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['ok' => true, 'cases' => $cases]);
        exit;
    }

    if ($action === 'get_case') {

        $caseId = (int)($_GET['id'] ?? 0);

        $stmt = $pdo->prepare("
            SELECT id AS id, type_id, subtype_id, comment, admin_comment
            FROM cases
            WHERE id = ? AND applicant_id = ?
        ");
        $stmt->execute([$caseId, $_SESSION['user_id']]);
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

        echo json_encode(['ok' => true, 'case' => $case, 'files' => $files]);
        exit;
    }

    if ($action === 'update_case') {

        $case_id    = (int)($_POST['case_id']    ?? 0);
        $type_id    = (int)($_POST['type_id']    ?? 0);
        $subtype_id = (int)($_POST['subtype_id'] ?? 0);
        $comment    = trim($_POST['comment']     ?? '');

        if (!$type_id || !$subtype_id || !$comment) {
            echo json_encode(['ok' => false, 'error' => 'Заполните все поля']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT id FROM cases WHERE id = ? AND applicant_id = ?");
        $stmt->execute([$case_id, $_SESSION['user_id']]);
        if (!$stmt->fetch()) {
            echo json_encode(['ok' => false, 'error' => 'Дело не найдено']);
            exit;
        }

        $stmt = $pdo->prepare("
            UPDATE cases SET type_id = ?, subtype_id = ?, comment = ? WHERE id = ?
        ");
        $stmt->execute([$type_id, $subtype_id, $comment, $case_id]);

        if (!empty($_FILES['files']['name'][0])) {
            $uploadDir = __DIR__ . '/../../resourses/files/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $files = $_FILES['files'];

            for ($i = 0; $i < count($files['name']); $i++) {
                if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;

                $ext = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
                $fileName = uniqid('doc_') . '.' . $ext;

                if (move_uploaded_file($files['tmp_name'][$i], $uploadDir . $fileName)) {
                    $stmt = $pdo->prepare("
                        INSERT INTO documents (case_id, file_name, file_path, file_size, uploaded_by)
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $case_id,
                        $files['name'][$i],
                        'resourses/files/' . $fileName,
                        $files['size'][$i],
                        $_SESSION['user_id']
                    ]);
                }
            }
        }

        echo json_encode(['ok' => true]);
        exit;
    }

    if ($action === 'get_shedules') {
        $stmt = $pdo->prepare("
            SELECT
                h.id,
                h.hearing_date,
                h.hearing_time,
                c.id AS case_id,
                t.name AS typeCase,
                st.name AS subtypeCase,
                u.fio AS judgeName
            FROM hearings h
            JOIN cases c ON h.case_id = c.id
            JOIN case_types t ON c.type_id = t.id
            JOIN case_subtypes st ON c.subtype_id = st.id
            JOIN users u ON h.judge_id = u.id
            WHERE c.applicant_id = ?
            ORDER BY h.hearing_date ASC, h.hearing_time ASC
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $hearings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($hearings as &$h) {
            $h['date'] = date('d.m.Y', strtotime($h['hearing_date']))
                       . ' в ' . substr($h['hearing_time'], 0, 5);
        }

        echo json_encode(['ok' => true, 'hearings' => $hearings]);
        exit;
    }

    $types = $pdo->query("SELECT id, name FROM case_types ORDER BY name");
    $types = $types->fetchAll(PDO::FETCH_ASSOC);

    $subtypes = $pdo->query("SELECT id, type_id, name FROM case_subtypes ORDER BY name");
    $subtypes = $subtypes->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode ([
        "ok" => true,
        "types" => $types,
        "subtypes" => $subtypes
    ]);