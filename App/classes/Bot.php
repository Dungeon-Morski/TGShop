<?php

namespace Classes;
class Bot
{
    public $token;
    public $apiUrl = 'https://api.telegram.org/bot';

    public function __construct($token)
    {
        $this->token = $token;
    }


    public function processUpdate()
    {
        $update = $this->getUpdate();

        return $update;
    }

    public function getUpdate()
    {
        $content = file_get_contents('php://input');
        return json_decode($content, true);
    }

//    public function processCommand($message)
//    {
//        $command = $message['text'];
//
//    }

//    public function processInlineButton($callbackQuery)
//    {
//        $data = $callbackQuery['data'];
//
//
//    }

    public function sendMessage($chatId, $text)
    {
        $params = [
            'chat_id' => $chatId,
            'text' => $text
        ];

        $this->sendRequest('sendMessage', $params);
    }

    public function editMessage($chatId, $messageId, $text)
    {
        $params = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text
        ];

        $this->sendRequest('editMessageText', $params);
    }

    public function editMessageText($chatId, $messageId, $text)
    {
        $params = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text

        ];

        $this->sendRequest('editMessageText', $params);
    }

    public function editMessageReplyMarkup($chatId, $messageId, $reply_markup)
    {
        $params = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'reply_markup' => $reply_markup

        ];

        $this->sendRequest('editMessageReplyMarkup', $params);
    }

    public function editMessageCaption($chatId, $messageId, $caption, $inlineKeyboard)
    {
        $params = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'caption' => $caption,
            'reply_markup' => $inlineKeyboard
        ];

        $this->sendRequest('editMessageCaption', $params);
    }

    public function editMessageMedia($chatId, $messageId, $media, $inlineKeyboard)
    {
        $params = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'media' => $media,
            'reply_markup' => $inlineKeyboard
        ];

        $this->sendRequest('editMessageMedia', $params);
    }

    public function deleteMessage($chatId, $messageId)
    {
        $params = [
            'chat_id' => $chatId,
            'message_id' => $messageId,

        ];

        $this->sendRequest('deleteMessage', $params);
    }

    public function sendPhoto($chatId, $caption, $photo, $inlineKeyboard)
    {
        $params = [
            'chat_id' => $chatId,
            'photo' => $photo,
            'caption' => $caption,
            'reply_markup' => $inlineKeyboard
        ];

        $this->sendRequest('sendPhoto', $params);
    }

    public function sendInlineButton($chatId, $text, $inlineKeyboard)
    {
        $params = [
            'chat_id' => $chatId,
            'text' => $text,
            'reply_markup' => $inlineKeyboard
        ];

        $this->sendRequest('sendMessage', $params);
    }

    public function answerCallbackQuery($callbackQueryId)
    {
        $params = [
            'callback_query_id' => $callbackQueryId
        ];

        $this->sendRequest('answerCallbackQuery', $params);
    }

    public function sendRequest($method, $params)
    {
        $url = $this->apiUrl . $this->token . '/' . $method;
        $options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($params),
            ],
        ];
        $context = stream_context_create($options);
        file_get_contents($url, false, $context);
    }


}