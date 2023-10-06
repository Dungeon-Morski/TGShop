<?php

namespace Classes;

class Product
{


    public $db;

    public function __construct($db)
    {


        $this->db = $db;
    }

    public function getProductData($id)
    {
        return $this->db->query('SELECT * FROM products WHERE id = :id', [':id' => $id]);

    }

    public function getData($id)
    {
        return $this->db->query('SELECT data FROM products WHERE id = :id', [':id' => $id]);
    }

    public function updateProductCount($count, $id)
    {
        return $this->db->query('UPDATE products SET count = :count WHERE id = :id', [':count' => $count, ':id' => $id]);
    }

    public function getProducts($product_id)
    {
        return $this->db->query("SELECT * FROM products WHERE id = {$product_id}");
    }

    public function getCategoryProducts($category)
    {
//        return $this->db->fetchAll("SELECT * FROM products WHERE category_id = {$category}");
        return $this->db->fetchAll("SELECT products.*, product_categories.title AS category_title FROM product_categories JOIN products ON product_categories.id = products.category_id WHERE products.category_id = {$category}");
    }

}