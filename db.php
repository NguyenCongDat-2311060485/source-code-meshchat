<?php
$host     = "localhost";
$dbname   = "forum_db";
$username = "root";
$password = "";


$conn = new mysqli($host, $username, $password, $dbname);


if ($conn->connect_error) {
    die("❌ Kết nối thất bại: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");


function db_fetch_all(string $sql, string $types = "", array $params = []): array {
    global $conn;
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("❌ Lỗi prepare SQL: " . $conn->error . "<br>SQL: " . $sql);
    }
    if ($types && $params) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}


function db_fetch(string $sql, string $types = "", array $params = []): ?array {
    $rows = db_fetch_all($sql, $types, $params);
    return $rows[0] ?? null;
}

function db_execute($sql, $types = "", $params = []) {
    global $conn;
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("SQL ERROR: " . $conn->error . " | Query: " . $sql);
    }
    if ($types && $params) {
        $stmt->bind_param($types, ...$params);
    }
    return $stmt->execute();
}


function db_insert(string $sql, string $types = "", array $params = []): int {
    global $conn;
    $stmt = $conn->prepare($sql);
    if ($types && $params) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $id = $stmt->insert_id;
    $stmt->close();
    return $id;
}
function db_update(string $table, array $data, string $where, array $paramsWhere = []): int {
    global $conn;
    $fields = [];
    $values = [];

    foreach ($data as $col => $val) {
        $fields[] = "`$col`=?";
        $values[] = $val;
    }

    $sql = "UPDATE `$table` SET " . implode(",", $fields) . " WHERE $where";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("❌ Lỗi prepare SQL: " . $conn->error . "<br>SQL: " . $sql);
    }

    $types = str_repeat("s", count($values)) . str_repeat("s", count($paramsWhere));
    $stmt->bind_param($types, ...array_merge($values, $paramsWhere));
    $stmt->execute();

    $affected = $stmt->affected_rows;
    $stmt->close();
    return $affected;
}

function db_delete(string $sql, string $types = "", array $params = []): int {
    global $conn;
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("❌ Lỗi prepare SQL: " . $conn->error . "<br>SQL: " . $sql);
    }
    if ($types && $params) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    return $affected;
}

?>
