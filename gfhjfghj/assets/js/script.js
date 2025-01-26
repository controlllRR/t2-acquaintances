let tg = window.Telegram.WebApp;

// Инициализация Telegram Mini App
document.addEventListener('DOMContentLoaded', function() {
    tg.expand();
    tg.ready();
});

// Функция для закрытия приложения
function closeApp() {
    tg.close();
} 