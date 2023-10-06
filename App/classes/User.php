<?php

namespace Classes;


class User
{

    public $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getUserData($chatId)
    {
        return $this->db->query('SELECT * FROM users WHERE chat_id = :chat_id', [':chat_id' => $chatId]);

    }

    public function updateBalance($balance, $chatId)
    {
        return $this->db->query('UPDATE users SET balance = :balance WHERE chat_id = :chat_id', [
            ':balance' => $balance,
            ':chat_id' => $chatId,
        ]);
    }

    public function getState($chatId)
    {
        return $this->db->query('SELECT * FROM bot_state WHERE chat_id = :chat_id', [':chat_id' => $chatId]);

    }

    public function createUser($chatId)
    {
        $this->db->insert('users', [
            'chat_id' => $chatId,
            'balance' => 0,
            'role' => 'user',
            'status' => 0,
        ]);
    }

    public function setState($state, $category_id, $messageId, $chatId)
    {
        $userState = $this->getState($chatId);
        if (empty($userState)) {
            $this->db->insert('bot_state', [
                'chat_id' => $chatId,
                'state' => $state,
                'category_id' => $category_id,
                'message_id' => $messageId,
            ]);

        } else if (!empty($userState)) {
            $this->db->update('bot_state', [
                'state' => $state,
                'category_id' => $category_id,
                'message_id' => $messageId,
            ], "chat_id = {$chatId}");
        }


    }


}