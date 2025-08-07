<?php

$host = 'localhost';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password);

if ($conn->connect_errno) {
    echo "Fail to mysqlnect mysql" . $conn->connect_error;
    exit;
}


function create_database($conn)
{
    $sql = "CREATE DATABASE IF NOT EXISTS 
            `fragrance_hub`
            DEFAULT CHARACTER SET utf8mb4 
            COLLATE utf8mb4_general_ci";

    if ($conn->query($sql)) {
        return true;
    }
    return false;
}

create_database($conn);

function select_db($conn)
{
    if ($conn->select_db("fragrance_hub")) {
        return true;
    }
    return false;
}

select_db($conn);
create_table($conn);

function create_table($conn)
{
    //user table
    $user_sql = "CREATE TABLE IF NOT EXISTS `user` (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    user_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(100) NOT NULL,
    address VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";


    if ($conn->query($user_sql) === false) return false;

    //create member table
    $category_sql = "CREATE TABLE IF NOT EXISTS `category`
                    (category_id int AUTO_INCREMENT PRIMARY KEY,
                    category_name VARCHAR(50) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    )";

    if ($conn->query($category_sql) === false) return false;

    //create trainer table
    $product_sql = "CREATE TABLE IF NOT EXISTS `product`
                    (product_id int AUTO_INCREMENT PRIMARY KEY,
                    product_name VARCHAR(50) NOT NULL,
                    stock_count VARCHAR(100) NOT NULL UNIQUE,
                    sale_price VARCHAR(50) NOT NULL,
                    purchase_price VARCHAR(255) NOT NULL,
                    category_id VARCHAR(10),
                    description VARCHAR(200) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    )";

    if ($conn->query($product_sql) === false) return false;

    //attendance table
    $payment_method_sql = "CREATE TABLE IF NOT EXISTS `payment_method`
                (payment_method_id INT AUTO_INCREMENT PRIMARY KEY,
                method_name DATETIME NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )";

    if ($conn->query($payment_method_sql) === false) return false;

    //create discount table
    $discont_sql = "CREATE TABLE IF NOT EXISTS `discount`
                (discount_id INT AUTO_INCREMENT PRIMARY KEY,
                name_of_package VARCHAR(100) NOT NULL,
                percentage VARCHAR(100) NOT NULL ,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )";

    if ($conn->query($discont_sql) === false) return false;

    //discount details table
    $discont_detail_sql = "CREATE TABLE IF NOT EXISTS `discount_detail`
                (discount_detail_id INT AUTO_INCREMENT PRIMARY KEY,
                discount_id int NOT NULL,
                start_date DATE NOT NULL,
                end_date DATE NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )";

    if ($conn->query($discont_detail_sql) === false) return false;

    //payment table
    $payment_sql = "CREATE TABLE IF NOT EXISTS `payment`
                (payment_id INT AUTO_INCREMENT PRIMARY KEY,
                product_id int NOT NULL,
                user_id int NOT NULL,
                amount int NOT NULL,
                payment_date DATE NOT NULL,
                payment_method_id VARCHAR(30) NOT NULL,
                detail_id int NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )";

    if ($conn->query($payment_sql) === false) return false;


    // image table create
    $image_sql = "CREATE TABLE IF NOT EXISTS `image`
                (
                type ENUM('product','user') NOT NULL,
                target_id VARCHAR(100) NOT NULL ,
                img VARCHAR(100) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )";

    if ($conn->query($image_sql) === false) return false;
}
