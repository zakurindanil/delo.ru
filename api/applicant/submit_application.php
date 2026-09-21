<?php
    header("Content-Type: application/json");

    require("../config.php");

    $types = $pdo->query("SELECT id, name FROM case_types ORDER BY name");
    $types = $types->fetchAll(PDO::FETCH_ASSOC);

    $subtypes = $pdo->query("SELECT id, type_id, name FROM case_subtypes ORDER BY name");
    $subtypes = $subtypes->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode ([
        "types" => $types,
        "subtypes" => $subtypes
    ]);