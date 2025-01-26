# -*- coding: utf-8 -*-

import telebot
from telebot import types

TOKEN = '8152176386:AAEYnkP9yOMcODbwNh1CbZMSWc2MHpke6bc'  # Подставьте ваш токен
bot = telebot.TeleBot(TOKEN)

@bot.message_handler(commands=['start'])
def send_welcome(message):
    user_first_name = message.from_user.first_name
    user_id = message.from_user.id
    username = message.from_user.username if message.from_user.username else "user"

    unique_url = f"https://123-231-21312-3213123-231.ru/index.html?user_id={user_id}&username={username}"  

    welcome_text = 'Приветствую, {} (@{})!\nЯ тебе помогу найти твою вторую половинку 👩‍❤️‍👨'.format(
        user_first_name, username if username != "user" else user_first_name
    )

    markup = types.InlineKeyboardMarkup()
    btn_find_partner = types.InlineKeyboardButton(
        "Найти половинку", 
        web_app=types.WebAppInfo(unique_url)
    )
    markup.add(btn_find_partner)

    bot.send_message(message.chat.id, welcome_text, reply_markup=markup)

# Обработчик callback-данных
@bot.callback_query_handler(func=lambda call: True)
def query_handler(call):
    bot.answer_callback_query(call.id)

# Запуск бота
bot.polling(none_stop=True)
