<?php

$host = 'localhost';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password);

if ($conn->connect_errno) {
    echo "Fail to connect mysql: " . $conn->connect_error;
    exit;
}

function create_database($conn)
{
    $sql = "CREATE DATABASE IF NOT EXISTS 
            `fragrance_hub`
            DEFAULT CHARACTER SET utf8mb4 
            COLLATE utf8mb4_general_ci";

    return $conn->query($sql);
}

create_database($conn);

function select_db($conn)
{
    return $conn->select_db("fragrance_hub");
}

select_db($conn);
create_table($conn);

function create_table($conn)
{
    $queries = [

        // User Table
        "CREATE TABLE IF NOT EXISTS `user` (
            user_id INT AUTO_INCREMENT PRIMARY KEY,
            user_name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            address VARCHAR(255),
            phone VARCHAR(50),
            role INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",

        // Brand Table
        "CREATE TABLE IF NOT EXISTS `brand` (
            brand_id INT AUTO_INCREMENT PRIMARY KEY,
            brand_name VARCHAR(100) NOT NULL,
            deleted_at DATETIME,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",

        // Product Table
        "CREATE TABLE IF NOT EXISTS `product` (
            product_id INT AUTO_INCREMENT PRIMARY KEY,
            product_name VARCHAR(100) NOT NULL,
            description TEXT,
            gender VARCHAR(10),
            deleted_at DATETIME,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",

        // Product_Band Table (acts as product-brand pivot with price and qty)
        "CREATE TABLE IF NOT EXISTS `product_band` (
            product_id INT NOT NULL,
            brand_id INT NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            Qty INT NOT NULL,
            deleted_at DATETIME,
            PRIMARY KEY (product_id, brand_id),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",

        // Discount Table
        "CREATE TABLE IF NOT EXISTS `discount` (
            discount_id INT AUTO_INCREMENT PRIMARY KEY,
            name_of_package VARCHAR(100) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",

        // Discount Details Table
        "CREATE TABLE IF NOT EXISTS `discount_details` (
            discount_details_id INT AUTO_INCREMENT PRIMARY KEY,
            discount_id INT NOT NULL,
            start_date DATE NOT NULL,
            end_date DATE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",

        // Recepties Table
        "CREATE TABLE IF NOT EXISTS `recepties` (
            recepties_id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            date DATE NOT NULL,
            total DECIMAL(10,2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",

        // Payment Method Table
        "CREATE TABLE IF NOT EXISTS `payment_method` (
            payment_method_id INT AUTO_INCREMENT PRIMARY KEY,
            name_of_method VARCHAR(100) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",

        // Order Table
        "CREATE TABLE IF NOT EXISTS `order` (
            order_id INT AUTO_INCREMENT PRIMARY KEY,
            recepties_id INT NOT NULL,
            product_brand_id INT NOT NULL,
            discount_details_id INT,
            line_total DECIMAL(10,2) NOT NULL,
            qty INT NOT NULL,
            payment_method_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",

        // Image Table
        "CREATE TABLE IF NOT EXISTS `image`
                (
                type ENUM('brand','product') NOT NULL,
                target_id VARCHAR(100) NOT NULL ,
                img VARCHAR(100) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )"
    ];

    foreach ($queries as $sql) {
        if ($conn->query($sql) === false) {
            echo "Error creating table: " . $conn->error;
            return false;
        }
    }
}
