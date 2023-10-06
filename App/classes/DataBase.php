<?php

namespace Classes;


use PDO;
use PDOException;

class DataBase
{
    public $host;
    public $username;
    public $password;
    public $database;
    public $pdo;

    public function __construct($host, $username, $password, $database)
    {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;

        try {
            $this->pdo = new PDO("mysql:host={$this->host};dbname={$this->database}", $this->username, $this->password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Ошибка подключения к базе данных: " . $e->getMessage());
        }
    }

    public function query($sql, $params = array())
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data;
        } catch (PDOException $e) {
            die("Ошибка выполнения запроса: " . $e->getMessage());
        }
    }


    public function fetchAll($sql, $params = array())
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $data;
        } catch (PDOException $e) {
            die("Ошибка выполнения запроса: " . $e->getMessage());
        }
    }

    public function fetchOne($sql, $params = array())
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function insert($table, $data)
    {
        $columns = implode(", ", array_keys($data));
        $values = ":" . implode(", :", array_keys($data));

        $sql = "INSERT INTO $table ($columns) VALUES ($values)";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($data);
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            die("Ошибка при вставке данных: " . $e->getMessage());
        }
    }

    public function update($table, $data, $where)
    {
        $set = '';
        foreach ($data as $key => $value) {
            $set .= "$key=:$key, ";
        }
        $set = rtrim($set, ', ');

        $sql = "UPDATE $table SET $set WHERE $where";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($data);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            die("Ошибка при обновлении данных: " . $e->getMessage());
        }
    }

    public function delete($table, $where, $params = array())
    {
        $sql = "DELETE FROM $table WHERE $where";

        try {
            $stmt = $this->query($sql, $params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            die("Ошибка при удалении данных: " . $e->getMessage());
        }
    }
}



