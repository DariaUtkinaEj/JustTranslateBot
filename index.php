<?php
// https://github.com/dejurin/php-google-translate-for-free

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/TelegramBot.php';

use \Dejurin\GoogleTranslateForFree;

$token = '5635091494:AAGj69SyAu6SH6X7pCBwSJXM_gKReL4Qurg';

$telegram = new TelegramBot($token);

$update = $telegram->getWebhookUpdates();


file_put_contents(__DIR__ . '/logs.txt', print_r($update, 1), FILE_APPEND);

$chat_id = $update['message']['chat']['id'] ?? '';
$text = $update['message']['text'] ?? '';

if ($text == '/start') {
    $data = get_chat_id($chat_id);
    if (empty($data)) {
        add_chat_id($chat_id, $update['message']['chat']['first_name'], 'en');
        $check = 'en';
    } else {
        $check = $data['lang'];
    }

    $response = $telegram->sendMessage([
        'chat_id' => $chat_id,
        'text' => "Оставьте отмеченный язык для перевода с него или выберите другой",
        'reply_markup' => $telegram->replyKeyboardMarkup([
            'inline_keyboard' => get_keyboard($check),
        ])
    ]);
} elseif (isset($update['callback_query']['message'])) {

    foreach ($update['callback_query']['message']['reply_markup']['inline_keyboard'][0] as $item) {
        if ($item['text'] == $update['callback_query']['data']) {

            update_chat($update['callback_query']['message']['chat']['id'], $update['callback_query']['data']);

            $response = $telegram->answerCallbackQuery([
                'callback_query_id' => $update['callback_query']['id'],
                /*'text' => "Язык перевода изменен на {$update['callback_query']['data']}",
                'show_alert' => false,*/
            ]);

            $response = $telegram->sendMessage([
                'chat_id' => $update['callback_query']['message']['chat']['id'],
                'text' => "Можете вводить слово для перевода с выбранного языка",
                'reply_markup' => $telegram->replyKeyboardMarkup([
                    'inline_keyboard' => get_keyboard($update['callback_query']['data']),
                ])
            ]);

            break;
        }
    }

    $response = $telegram->answerCallbackQuery([
        'callback_query_id' => $update['callback_query']['id'],
        'text' => "Это уже активный язык",
        'show_alert' => false,
    ]);

} elseif (!empty($text)) {
    $data = get_chat_id($chat_id);
    $source = ($data['lang'] == 'en') ? 'en' : 'ru';
    $target = ($data['lang'] == 'ru') ? 'en' : 'ru';
    $attempts = 5;

    $tr = new GoogleTranslateForFree();
    $result = $tr->translate($source, $target, $text, $attempts);

    if ($result) {
        $response = $telegram->sendMessage([
            'chat_id' => $chat_id,
            'text' => $result,
        ]);
    } else {
        $response = $telegram->sendMessage([
            'chat_id' => $chat_id,
            'text' => 'Упс... я не смог перевести это...',
        ]);
    }
} else {
    $response = $telegram->sendMessage([
        'chat_id' => $chat_id,
        'text' => 'Это бот-переводчик, поэтому он ожидает от вас текст для перевода...',
    ]);
}


function get_keyboard($lang)
{
    return [
        [
            ['text' => $lang == 'en' ? 'en 🗸' : 'en', 'callback_data' => 'en'],
            ['text' => $lang == 'ru' ? 'ru 🗸' : 'ru', 'callback_data' => 'ru'],
        ]
    ];
}