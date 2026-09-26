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
            WHERE c.judge_id = ? AND s.name IN ('Назначено', 'Отложено')
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