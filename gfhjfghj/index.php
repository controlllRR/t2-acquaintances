<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Поздравляем!</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
</head>
<body>
    <div class="container">
        <div class="chat-bubbles">
            <div class="bubble bubble-gray">
                <span class="typing-dots">...</span>
            </div>
            <div class="bubble bubble-pink">
                <span class="typing-dots">...</span>
                <span class="notification">1</span>
            </div>
        </div>
        <h1 class="congratulation">ПОЗДРАВЛЯЕМ!</h1>
        <p class="message">Ты всех оценил. Нажми «Назад» для выхода.</p>
        <button class="back-button" onclick="closeApp()">НАЗАД</button>
    </div>
    <script src="assets/js/script.js"></script>
</body>
</html> 