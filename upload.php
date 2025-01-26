<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jsonInput = file_get_contents("php://input");
    $data = json_decode($jsonInput, true);

    if ($data) {
        error_log(print_r($data, true)); // Логирование полученных данных для отладки

        $uploadDir = 'photo_user/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $response = [
            'success' => false,
            'message' => 'Ошибка обработки данных.'
        ];

        // Обработка загруженных изображений в формате base64
        $uploadedFiles = $data['images'] ?? [];
        $decodedFiles = [];

        foreach ($uploadedFiles as $base64Image) {
            list(, $base64Image) = explode(',', $base64Image);
            $imageData = base64_decode($base64Image);

            if ($imageData !== false) {
                $uniqueFileName = 'photo_' . uniqid() . '.png';
                $targetFilePath = $uploadDir . $uniqueFileName;

                if (file_put_contents($targetFilePath, $imageData)) {
                    $decodedFiles[] = $targetFilePath;
                }
            }
        }

        // Подготовка данных пользователя
        $userId = $data['user_id'];
        $userData = [
            'files' => $decodedFiles,
            'name' => $data['name'],
            'username' => $data['username'],
            'about' => $data['about'],
            'gender' => $data['gender'],
            'birthdate' => $data['birthdate'],
            'user_id' => $userId
        ];

        $jsonFile = 'basa.json';
        $jsonData = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];

        // Обновление или добавление записи пользователя
        $jsonData[$userId] = $userData; // Заменить старые данные новыми

        if (file_put_contents($jsonFile, json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
            $response['success'] = true;
            $response['message'] = ''; // Убираем сообщение о сохранении данных
        }

        header('Content-Type: application/json');
        echo json_encode($response);
    }
}

?>
