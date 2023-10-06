<?php

namespace App\classes;

class Button
{
    public static $text = '';
    public static $callback_data = '';

    public function __construct($text, $callback_data)
    {

    }

    public static function createButton()
    {
        return ['inline_keyboard' => []];
    }

    public static function fillButton($inlineKeyboard, $text, $callback_data)
    {

        $button = [
            'text' => $text,
            'callback_data' => $callback_data
        ];
        $inlineKeyboard['inline_keyboard'][] = [$button];
        return $inlineKeyboard;
    }
}