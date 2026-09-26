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
                u.fio AS applicant,
                h.hearing_date,
                h.hearing_time
            FROM cases c
            JOIN case_statuses s ON c.status_id = s.id
            JOIN case_types t ON c.type_id = t.id
            JOIN case_subtypes st ON c.subtype_id = st.id
            JOIN users u ON c.applicant_id = u.id
            LEFT JOIN hearings h ON h.case_id = c.id
            WHERE c.judge_id = ? AND s.name IN ('В производстве', 'Отложено')
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $cases = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($cases as &$c) {
            if ($c['hearing_date']) {
                $c['date'] = date('d.m.Y', strtotime($c['hearing_date'])) . ' в ' . substr($c['hearing_time'], 0, 5);
            } else {
                $c['date'] = 'Не назначено';
            }
            unset($c['hearing_date'], $c['hearing_time']);
        }

        echo json_encode(['ok' => true, 'cases' => $cases]);
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
                u.fio AS applicant
            FROM hearings h
            JOIN cases c ON h.case_id = c.id
            JOIN case_types t ON c.type_id = t.id
            JOIN case_subtypes st ON c.subtype_id = st.id
            JOIN users u ON c.applicant_id = u.id
            WHERE h.judge_id = ?
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

    if ($action === 'create_hearing') {

        $case_id = (int)($_POST['case_id'] ?? 0);
        $date    = trim($_POST['date']    ?? '');
        $time    = trim($_POST['time']    ?? '');

        if (!$date || !$time) {
            echo json_encode(['ok' => false, 'error' => 'Заполните дату и время']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT id FROM hearings WHERE case_id = ?");
        $stmt->execute([$case_id]);
        $existing = $stmt->fetchColumn();

        if ($existing) {
            $stmt = $pdo->prepare("UPDATE hearings SET hearing_date = ?, hearing_time = ? WHERE case_id = ?");
            $stmt->execute([$date, $time, $case_id]);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO hearings (case_id, judge_id, hearing_date, hearing_time)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$case_id, $_SESSION['user_id'], $date, $time]);
        }

        echo json_encode(['ok' => true]);
        exit;
    }