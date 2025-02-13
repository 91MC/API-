<?php
header('Content-Type: application/json');
$config = [
    'host'     => 'localhost',
    'dbname'   => 'xxxxxx',
    'user'     => 'xxxxxx',
    'password' => 'xxxxxx'
];

try {
    
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4",
        $config['user'],
        $config['password']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    
    if (!$pdo) {
        throw new Exception('Database connection failed');
    }

    $pdo->beginTransaction();

    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';

    $stmt = $pdo->prepare("SELECT status FROM user_data WHERE IP = ? ORDER BY get_time DESC LIMIT 1");
    $stmt->execute([$ip]);
    $statusResult = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($statusResult && $statusResult['status'] == 1) {
        echo json_encode([
            'message' => 'IP被封禁，无法获取数据'
        ]);
        exit;
    }

    $stmt = $pdo->prepare("SELECT ID, count FROM user_data WHERE IP = ?");
    $stmt->execute([$ip]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($userData) {
        $newCount = $userData['count'] + 1;
        $pdo->prepare("UPDATE user_data SET count = ?, get_time = NOW() WHERE ID = ?")
            ->execute([$newCount, $userData['ID']]);
    } else {
        $pdo->prepare("INSERT INTO user_data (IP, get_time, count) VALUES (?, NOW(), 1)")
            ->execute([$ip]);
    }

    $stmt = $pdo->query("SELECT * FROM account WHERE is_deleted = 0 ORDER BY ID ASC LIMIT 1 FOR UPDATE");
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($data) {
        $pdo->prepare("UPDATE account SET is_deleted = 1 WHERE ID = ?")->execute([$data['ID']]);

        $pdo->commit();
        echo json_encode([
            'status' => 0,
            'data'   => $data['data']
        ]);
    } else {
        $pdo->commit();
        echo json_encode([
            'status'  => 2,
            'message' => '没有可用数据'
        ]);
    }

} catch (PDOException $e) {
    if ($pdo && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode([
        'status'  => 3,
        'message' => '数据库错误: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status'  => 4,
        'message' => '系统错误: ' . $e->getMessage()
    ]);
}
