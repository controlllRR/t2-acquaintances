<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// Получаем данные о пользователе
$data = json_decode(file_get_contents('php://input'), true);
$currentUserId = $data['currentUserId'] ?? null;
$ownerUserId = $data['ownerUserId'] ?? null;
$matchesFilePath = 'matches.json';
$basaFilePath = 'basa.json';

if (!$currentUserId || !$ownerUserId) {
    echo json_encode(['error' => 'Отсутствуют требуемые данные']);
    exit;
}

try {
    // Загружаем пользователей из basa.json
    if (!file_exists($basaFilePath)) {
        throw new Exception('Файл basa.json не найден.');
    }
    $users = json_decode(file_get_contents($basaFilePath), true);

    // Загружаем существующие матчи
    $matches = [];
    if (file_exists($matchesFilePath)) {
        $matches = json_decode(file_get_contents($matchesFilePath), true);
    }

    // Функция обновления матчей
    function updateMatches($currentUserId, $ownerUserId, &$matches, $users) {
        $key = "$currentUserId-$ownerUserId";
        $reverseKey = "$ownerUserId-$currentUserId";

        $ownerUserInfo = $users[$ownerUserId] ?? null;
        $currentUserInfo = $users[$currentUserId] ?? null;

        // Проверяем, был ли уже установлен матч
        if (isset($matches[$key])) {
            return ['match' => false]; // Матч уже существует
        }

        // Если обратный матч ожидает взаимного ответа
        if (isset($matches[$reverseKey]) && $matches[$reverseKey]['status'] === 'ожидает взаимно') {
            // Обновляем статус взаимного матча
            $matches[$key] = [
                'current_user_id' => $currentUserId,
                'owner_user_id' => $ownerUserId,
                'status' => 'взаимно',
            ];
            $matches[$reverseKey]['status'] = 'взаимно';

            // Подготовка данных для отправки каждому пользователю
            $matchInfoForOwner = [
                'name' => $currentUserInfo['name'] ?? 'Имя не указано',
                'age' => calculateAge($currentUserInfo['birthdate']) ?? 'Возраст неизвестен',
                'description' => $currentUserInfo['about'] ?? 'Без описания',
                'telegram' => $currentUserInfo['telegram'] ?? 'Не указано',
            ];
            $matchInfoForCurrent = [
                'name' => $ownerUserInfo['name'] ?? 'Имя не указано',
                'age' => calculateAge($ownerUserInfo['birthdate']) ?? 'Возраст неизвестен',
                'description' => $ownerUserInfo['about'] ?? 'Без описания',
                'telegram' => $ownerUserInfo['telegram'] ?? 'Не указано',
            ];

            // Отправка сообщения
            sendTelegramMessage($matchInfoForOwner, $currentUserInfo['telegram']);
            sendTelegramMessage($matchInfoForCurrent, $ownerUserInfo['telegram']);

            return ['match' => true];
        }

        // Создаем новый матч
        $matches[$key] = [
            'current_user_id' => $currentUserId,
            'owner_user_id' => $ownerUserId,
            'status' => 'ожидает взаимно'
        ];

        return ['match' => false];
    }

    // Функция вычисления возраста
    function calculateAge($birthdate) {
        if (!$birthdate) return null;
        $birthDate = new DateTime($birthdate);
        $now = new DateTime();
        return $now->diff($birthDate)->y;
    }

    // Функция отправки сообщений через Telegram
    function sendTelegramMessage($matchInfo, $chatId) {
        $botToken = "8152176386:AAEYnkP9yOMcODbwNh1CbZMSWc2MHpke6bc"; // Замените на ваш токен

        // Формируем сообщение
        $message = "<b>У вас новый мэтч!</b>\n" .
                   "Вас лайкнул пользователь: {$matchInfo['name']}, {$matchInfo['age']} лет.\n" .
                   "Описание пользователя: {$matchInfo['description']}\n" .
                   "<b>Telegram пользователя</b>: {$matchInfo['telegram']}\n" .
                   "<b>Хорошего дня 😉</b>";

        $url = "https://api.telegram.org/bot$botToken/sendMessage?chat_id=$chatId&text=" . urlencode($message) . "&parse_mode=HTML";
        
        // Логирование URL перед запросом
        error_log("Отправка запроса к Telegram по URL: $url");

        $response = file_get_contents($url); // Отправка сообщения
        $responseData = json_decode($response, true);

        // Логирование ответа от Telegram
        error_log("Ответ от Telegram: " . json_encode($responseData));

        // Проверка ответа от Telegram
        if (isset($responseData['ok']) && $responseData['ok']) {
            return true; // Успешно отправлено
        } else {
            error_log("Ошибка при отправке сообщения в Telegram: " . json_encode($responseData)); // Логирование ошибки
            return false; // Ошибка отправки
        }
    }

    // Обновление матчей
    $result = updateMatches($currentUserId, $ownerUserId, $matches, $users);

    // Запись результатов в файл
    if (file_put_contents($matchesFilePath, json_encode($matches, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false) {
        echo json_encode($result);
    } else {
        throw new Exception('Ошибка при записи в файл matches.json');
    }

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}