<?php
$config = [
    'host'     => 'localhost',
    'dbname'   => 'xxxxx',
    'user'     => 'xxxxx',
    'password' => 'xxxxx'
];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        
        $pdo = new PDO(
            "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4",
            $config['user'],
            $config['password']
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        
        $dataList = explode("\n", trim($_POST['data']));
        $dataList = array_map('trim', $dataList);

        
        $stmt = $pdo->prepare("INSERT INTO account (data) VALUES (?)");
        $pdo->beginTransaction();
        foreach ($dataList as $data) {
            if (!empty($data)) {
                $stmt->execute([$data]);
            }
        }
        $pdo->commit();

        $message = "成功插入 " . count($dataList) . " 条数据！";
    } catch (PDOException $e) {
        $message = "数据库错误: " . $e->getMessage();
    } catch (Exception $e) {
        $message = "系统错误: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>插入数据</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
        }
        textarea {
            width: 100%;
            height: 200px;
            padding: 10px;
            font-size: 16px;
            margin-bottom: 10px;
        }
        button {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        .message {
            margin-top: 20px;
            padding: 10px;
            background-color: #f2f2f2;
            border-left: 4px solid #4CAF50;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>插入数据</h1>
    <form method="POST">
        <textarea name="data" placeholder="每行输入一条数据，支持多行输入"></textarea>
        <button type="submit">提交</button>
    </form>

    <?php if (!empty($message)): ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
</div>
</body>
</html>
