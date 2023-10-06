<?php

require 'vendor/autoload.php';
require 'App/keyboard.php';

use App\classes\Button;
use Classes\Bot;
use Classes\DataBase;
use Classes\Product;
use Classes\User;


$database = new DataBase(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$user = new User($database);
$bot = new Bot(TOKEN);
$product = new Product($database);


$update = $bot->processUpdate();

$chat_id = $update['message']['chat']['id'];
$msgId = $update['message']['message_id'];
$bot_state = null;


if (isset($chat_id)) {
    $bot_state = $user->getState($chat_id);
    if (empty($bot_state)) {
        $user->setState('start', null, $msgId, $chat_id);
        $user->createUser($chat_id);

    }
}


if (isset($update['callback_query'])) {

    $data = $update['callback_query']['data'];
    $msgId = $update['callback_query']['message']['message_id'];
    $chatId = $update['callback_query']['message']['chat']['id'];
    $username = $update['callback_query']['message']['chat']['username'];
    $isCategory_id = strpos($data, 'category_');
    $isProduct_id = strpos($data, 'product_');
    $category_id = substr($data, strpos($data, "_") + 1);
    $product_id = substr($data, strpos($data, "_") + 1);
    $buyProduct = strpos($data, 'buyProduct_');
    $bot_state = $user->getState($chatId);

    switch ($data) {
        case 'profile':

            $user = $user->getUserData($chatId);

            $buttons = Button::createButton();
            $buttons = Button::fillButton($buttons, '💵 Пополнить баланс', 'replenish');
            $buttons = Button::fillButton($buttons, 'ℹ️ Назад', 'unprofile');

            $caption = "🏷 Пользователь:  @{$username}" . PHP_EOL .
                "🏷 Ваш ID: {$chatId}" . PHP_EOL .
                "➖➖➖➖➖➖➖➖➖➖➖➖➖" . PHP_EOL .
                "🌎 Баланс: {$user['balance']} ₽" . PHP_EOL .
                "🌎 Кол-во покупок: 0";

            $bot->editMessageMedia($chatId, $msgId, json_encode([
                'type' => 'photo',
                'media' => 'https://dark-freelancer.site/bot1234.jpg',
                'caption' => $caption,
            ]), json_encode($buttons));

            break;
        case 'replenish':
            $buttons = Button::createButton();

            $bot->deleteMessage($chatId, $msgId);

            $bot->sendInlineButton($chatId, '💵 Введите сумму для пополнения средств:', json_encode($buttons));

            $user->setState('showCategories', null, $msgId, $chatId);

            break;
        case 'categories':

            $categories = $database->fetchAll('SELECT * FROM product_categories');

            $buttons = Button::createButton();

            foreach ($categories as $category) {
                $buttons = Button::fillButton($buttons, $category['title'], "category_" . $category['id']);

            }

            $bot->deleteMessage($chatId, $msgId);

            $bot->sendInlineButton($chatId, '📌 Выберите категорию:', json_encode($buttons));

            $user->setState('showCategories', null, $msgId, $chatId);

            break;
        case 'information':
            $bot->sendMessage($chatId, 'Тут какая-то инфа');
            $user->setState('information', null, $msgId, $chatId);

            break;
        case 'unprofile':

            $caption = '📌 Выберите действие:';
            $bot->editMessageMedia($chatId, $msgId, json_encode([
                'type' => 'photo',
                'media' => 'https://avatars.mds.yandex.net/i?id=2a0000018a8158bb368c34f2cdc4d8d635ad-219455-fast-images&n=13',
                'caption' => $caption,
            ]), $mainUserMenu);

            break;
        case $isCategory_id !== false:

            $user->setState('showProducts', $category_id, $msgId, $chatId);

            $products = $product->getCategoryProducts($category_id);

            $buttons = Button::createButton();

            foreach ($products as $product) {

                $buttons = Button::fillButton($buttons, "{$product['title']} | {$product['price']} ₽ | {$product['count']} шт", "product_" . $product['id']);

            }

            $buttons = Button::fillButton($buttons, 'Назад', 'backToCategories');
            $bot->editMessageText($chatId, $msgId, '📌 Категория ' . $products[0]['category_title']);
            $bot->editMessageReplyMarkup($chatId, $msgId, json_encode($buttons));


            break;
        case $isProduct_id !== false:


            $product = $product->getProductData($product_id);

            $user->setState('showProduct', $product['category_id'], $msgId, $chatId);
            $buttons = Button::createButton();
            $buttons = Button::fillButton($buttons, '💰 Купить товар', "buyProduct_" . $product_id);
            $buttons = Button::fillButton($buttons, 'Назад', "backToProducts");


            $text = '📌 Выберите действие:' . PHP_EOL .
                '➖➖➖➖➖➖➖➖➖➖➖➖➖' . PHP_EOL .
                "🏷 Название: {$product['title']}
💵 Стоимость: {$product['price']} руб
📦 Количество: {$product['count']} шт

📜 Описание:
{$product['description']}";
            $bot->editMessageText($chatId, $msgId, $text);
            $bot->editMessageReplyMarkup($chatId, $msgId, json_encode($buttons));
        case $buyProduct !== false:

            if ($bot_state['state'] === 'showProduct') {

                $userData = $user->getUserData($chatId);
                $productData = $product->getProductData($product_id);

                if ($userData['balance'] >= $productData['price']) {

                    $user->updateBalance($userData['balance'] - $productData['price'], $chatId);
                    $product->updateProductCount($productData['count'] - 1, $productData['id']);
                    $data = $product->getData($productData['id']);

                    $bot->editMessageText($chatId, $msgId, "🥰 Спасибо за покупку \n \n");
                    $bot->editMessageReplyMarkup($chatId, $msgId, null);

                    $bot->sendMessage($chatId, $data['data']);
                } else {
                    $bot->sendMessage($chatId, "Недостаточно средств на балансе");
                }
            }


            break;
        case 'backToCategories':

            $category_id = $database->query('SELECT category_id FROM bot_state WHERE chat_id = :chat_id', [':chat_id' => $chatId]);

            $category = $database->query('SELECT * FROM product_categories WHERE id = :id', [':id' => $category_id]);

            $user->setState('showProducts', $category, $msgId, $chatId);

            $categories = $database->fetchAll('SELECT * FROM product_categories');

            $buttons = Button::createButton();

            foreach ($categories as $category) {
                $buttons = Button::fillButton($buttons, $category['title'], "category_" . $category['id']);

            }

            $bot->deleteMessage($chatId, $msgId);

            $bot->sendInlineButton($chatId, '📌 Выберите категорию:', json_encode($buttons));

            break;
        case 'backToProducts':

            $user->setState('showProducts', $category_id, $msgId, $chatId);

            $products = $product->getCategoryProducts($bot_state['category_id']);

            $buttons = Button::createButton();

            foreach ($products as $product) {

                $buttons = Button::fillButton($buttons, $product['title'], "product_" . $product['id']);

            }

            $buttons = Button::fillButton($buttons, 'Назад', 'backToCategories');
            $bot->editMessageText($chatId, $msgId, '📌 Категория ' . $products[0]['category_title']);
            $bot->editMessageReplyMarkup($chatId, $msgId, json_encode($buttons));

    }

    $bot->answerCallbackQuery($update['callback_query']['id']);
} else if (isset($update['message'])) {

    $command = $update['message']['text'];
    $msgId = $update['message']['message_id'];

    switch ($command) {
        case '/start':
            $bot->sendPhoto($chat_id, "📌 Выберите действие:", 'https://avatars.mds.yandex.net/i?id=2a0000018a8158bb368c34f2cdc4d8d635ad-219455-fast-images&n=13',
                $mainUserMenu);

            $user->setState('start', null, $msgId, $chat_id);

            break;

        default:
            $bot->sendMessage($chat_id, 'Неизвестная команда');
            break;
    }
}


$fff = $database->fetchAll("SELECT products.*, product_categories.title AS category_title
FROM product_categories
JOIN products ON product_categories.id = products.category_id
WHERE products.category_id = 1");
echo '<pre>';

print_r($fff);
echo '</pre>';


