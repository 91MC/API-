<?php
header('Content-Type: application/json');

// 数据库配置
$config = [
    'host'     => 'localhost',
    'dbname'   => 'sauth',
    'user'     => 'a123456!',
    'password' => 'a123456!'
];

try {
    // 连接数据库
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4",
        $config['user'],
        $config['password']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 新增连接检查
    if (!$pdo) {
        throw new Exception('Database connection failed');
    }

    $pdo->beginTransaction();

    // 获取客户端IP（考虑代理情况）
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';

    // 检查IP是否被封禁（status = 1）
    $stmt = $pdo->prepare("SELECT status FROM user_data WHERE IP = ? ORDER BY get_time DESC LIMIT 1");
    $stmt->execute([$ip]);
    $statusResult = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($statusResult && $statusResult['status'] == 1) {
        // 如果IP被封禁，直接返回封禁状态
        echo json_encode([
//            'status'  => 1,
            'message' => 'IP被封禁，无法获取数据'
        ]);
        exit;
    }

    // 检查IP是否已存在
    $stmt = $pdo->prepare("SELECT ID, count FROM user_data WHERE IP = ?");
    $stmt->execute([$ip]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($userData) {
        // 如果IP已存在，更新count字段
        $newCount = $userData['count'] + 1;
        $pdo->prepare("UPDATE user_data SET count = ?, get_time = NOW() WHERE ID = ?")
            ->execute([$newCount, $userData['ID']]);
    } else {
        // 如果IP不存在，插入新记录
        $pdo->prepare("INSERT INTO user_data (IP, get_time, count) VALUES (?, NOW(), 1)")
            ->execute([$ip]);
    }

    // 获取并删除一条数据
    $stmt = $pdo->query("SELECT * FROM account WHERE is_deleted = 0 ORDER BY ID ASC LIMIT 1 FOR UPDATE");
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($data) {
        // 标记为已删除
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
