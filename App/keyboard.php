<?php

$mainUserMenu = json_encode(
    array(
        'inline_keyboard' => array(
            array(
                array(
                    'text' => '👤Профиль',
                    'callback_data' => 'profile'
                ),
                array(
                    'text' => '🛍 Товары',
                    'callback_data' => 'categories'
                ),

            ),
            array(
                array(
                    'text' => 'ℹ️ Информация',
                    'callback_data' => 'information'
                ),
            )
        )
    )
);