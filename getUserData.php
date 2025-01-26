<?php
header('Content-Type: application/json');

// Получаем user_id из GET-запроса
if (!isset($_GET['user_id'])) {
    echo json_encode(['error' => 'User ID is required']);
    exit;
}

$userId = $_GET['user_id'];

// Чтение данных из basa.json
$jsonData = file_get_contents('basa.json');
$users = json_decode($jsonData, true);

// Поиск пользователя в данных
foreach ($users as $user) {
    if ($user['user_id'] === $userId) {
        echo json_encode($user);
        exit;
    }
}

// Если пользователь не найден
echo json_encode(['error' => 'User not found']);
?>
