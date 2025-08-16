<?php
require_once 'db.php';

// Function to clear existing data
function clear_tables($conn)
{
    $tables = [
        'order',
        'image',
        'discount_details',
        'discount',
        'recepties',
        'payment_method',
        'product_band',
        'product',
        'brand',
        'user'
    ];

    foreach ($tables as $table) {
        $conn->query("DELETE FROM `$table`");
        $conn->query("ALTER TABLE `$table` AUTO_INCREMENT = 1");
    }
    echo "All tables cleared successfully.\n";
}

// Function to seed users
function seed_users($conn)
{
    $users = [
        ['user_name' => 'John Doe', 'email' => 'john@example.com', 'password' => password_hash('password123', PASSWORD_DEFAULT), 'address' => '123 Main St, City', 'phone' => '123-456-7890', 'role' => 0],
        ['user_name' => 'Jane Smith', 'email' => 'jane@example.com', 'password' => password_hash('password123', PASSWORD_DEFAULT), 'address' => '456 Oak Ave, Town', 'phone' => '098-765-4321', 'role' => 0],
        ['user_name' => 'Admin User', 'email' => 'admin@fragrancehub.com', 'password' => password_hash('admin123', PASSWORD_DEFAULT), 'address' => '789 Admin Blvd', 'phone' => '555-123-4567', 'role' => 1],
        ['user_name' => 'Mike Johnson', 'email' => 'mike@example.com', 'password' => password_hash('password123', PASSWORD_DEFAULT), 'address' => '321 Pine St, Village', 'phone' => '111-222-3333', 'role' => 0],
        ['user_name' => 'Sarah Wilson', 'email' => 'sarah@example.com', 'password' => password_hash('password123', PASSWORD_DEFAULT), 'address' => '654 Elm Rd, Borough', 'phone' => '444-555-6666', 'role' => 0]
    ];

    foreach ($users as $user) {
        $sql = "INSERT INTO user (user_name, email, password, address, phone, role) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $user['user_name'], $user['email'], $user['password'], $user['address'], $user['phone'], $user['role']);
        $stmt->execute();
    }
    echo "Users seeded successfully.\n";
}

// Function to seed brands based on real data
function seed_brands($conn)
{
    $brands = [
        ['brand_name' => 'Chanel'],
        ['brand_name' => 'Dior'],
        ['brand_name' => 'Gucci'],
        ['brand_name' => 'Versace'],
        ['brand_name' => 'Tom Ford'],
        ['brand_name' => 'Yves Saint Laurent'],
        ['brand_name' => 'Hermès'],
        ['brand_name' => 'Bvlgari'],
        ['brand_name' => 'Calvin Klein'],
        ['brand_name' => 'Paco Rabanne'],
        ['brand_name' => 'Louis Vuitton'],
        ['brand_name' => 'Perfume De Marly'],
        ['brand_name' => 'Xerjoff'],
        ['brand_name' => 'Jean Paul Gaultier'],
        ['brand_name' => 'Givenchy'],
        ['brand_name' => 'Dolce & Gabbana'],
        ['brand_name' => 'Replica']
    ];

    foreach ($brands as $brand) {
        $sql = "INSERT INTO brand (brand_name) VALUES (?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $brand['brand_name']);
        $stmt->execute();
    }
    echo "Brands seeded successfully.\n";
}

// Function to seed products based on real data
function seed_products($conn)
{
    $products = [
        ['product_name' => 'N°5 Eau de Parfum', 'description' => 'The legendary fragrance that revolutionized perfumery', 'gender' => 'Women'],
        ['product_name' => 'Bleu de Chanel', 'description' => 'A woody aromatic fragrance for the modern man', 'gender' => 'Men'],
        ['product_name' => 'Miss Dior', 'description' => 'A romantic and elegant fragrance', 'gender' => 'Women'],
        ['product_name' => 'Sauvage', 'description' => 'A powerful and fresh masculine fragrance', 'gender' => 'Men'],
        ['product_name' => 'Gucci Bloom', 'description' => 'A celebration of the authenticity, vitality and diversity of women', 'gender' => 'Women'],
        ['product_name' => 'Versace Eros', 'description' => 'A bold, seductive fragrance for the modern man', 'gender' => 'Men'],
        ['product_name' => 'Black Opium', 'description' => 'An addictive gourmand fragrance', 'gender' => 'Women'],
        ['product_name' => 'Terre d\'Hermès', 'description' => 'A sophisticated and natural fragrance', 'gender' => 'Men'],
        ['product_name' => 'Omnia Crystalline', 'description' => 'A fresh and light fragrance', 'gender' => 'Women'],
        ['product_name' => '1 Million', 'description' => 'A provocative and seductive masculine fragrance', 'gender' => 'Men'],
        ['product_name' => 'Dior Sauvage EDT', 'description' => 'dior sauvage edt perfume', 'gender' => 'male'],
        ['product_name' => 'YSL Y EDP', 'description' => 'It is famous among teenager due to that unique app...', 'gender' => 'male'],
        ['product_name' => 'Lover', 'description' => 'LV Lover', 'gender' => 'male'],
        ['product_name' => 'Oud Wood', 'description' => 'Tom Ford Oud Wood', 'gender' => 'male'],
        ['product_name' => 'N°5 Eau de Parfum', 'description' => 'The legendary fragrance that revolutionized perfumery', 'gender' => 'female'],
        ['product_name' => 'Bleu de Chanel', 'description' => 'A woody aromatic fragrance for the modern man', 'gender' => 'male'],
        ['product_name' => 'Miss Dior', 'description' => 'A romantic and elegant fragrance', 'gender' => 'female'],
        ['product_name' => 'Versace Eros', 'description' => 'A bold, seductive fragrance for the modern man', 'gender' => 'male'],
        ['product_name' => 'Imagination', 'description' => 'Imagination by Louis Vuitton is a Citrus Aromatic ...', 'gender' => 'male'],
        ['product_name' => 'D&G The One', 'description' => 'The One for men is a spicy, oriental perfume that ...', 'gender' => 'male'],
        ['product_name' => 'Jazz Club', 'description' => 'Jazz Club by Maison Martin Margiela is a Leather f...', 'gender' => 'male']
    ];

    foreach ($products as $product) {
        $sql = "INSERT INTO product (product_name, description, gender) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $product['product_name'], $product['description'], $product['gender']);
        $stmt->execute();
    }
    echo "Products seeded successfully.\n";
}

// Function to seed product_brand relationships based on real data
function seed_product_brands($conn)
{
    $product_brands = [
        [1, 1, 150.00, 50],   // Chanel N°5
        [2, 1, 120.00, 40],   // Chanel Bleu
        [3, 2, 140.00, 35],   // Dior Miss Dior
        [4, 2, 110.00, 45],   // Dior Sauvage
        [5, 3, 130.00, 30],   // Gucci Bloom
        [6, 4, 100.00, 55],   // Versace Eros
        [7, 2, 125.00, 25],   // YSL Black Opium
        [8, 7, 160.00, 20],   // Hermès Terre
        [9, 8, 90.00, 60],    // Bvlgari Omnia
        [10, 10, 95.00, 40],  // Paco Rabanne 1 Million
        [11, 2, 110.00, 70],  // Dior Sauvage EDT
        [12, 6, 140.00, 45],  // YSL Y EDP
        [18, 11, 500.00, 100], // Louis Vuitton Lover
        [19, 5, 180.00, 30],  // Tom Ford Oud Wood
        [20, 1, 150.00, 50],  // Chanel N°5 (duplicate)
        [21, 1, 120.00, 40],  // Chanel Bleu (duplicate)
        [22, 2, 140.00, 35],  // Dior Miss Dior (duplicate)
        [23, 4, 100.00, 55],  // Versace Eros (duplicate)
        [24, 11, 450.00, 25], // Louis Vuitton Imagination
        [25, 16, 120.00, 60], // D&G The One
        [26, 17, 95.00, 40]   // Replica Jazz Club
    ];

    foreach ($product_brands as $pb) {
        $sql = "INSERT INTO product_band (product_id, brand_id, price, Qty) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iidi", $pb[0], $pb[1], $pb[2], $pb[3]);
        $stmt->execute();
    }
    echo "Product-Brand relationships seeded successfully.\n";
}

// Function to seed discounts
function seed_discounts($conn)
{
    $discounts = [
        ['name_of_package' => 'Summer Sale'],
        ['name_of_package' => 'Holiday Special'],
        ['name_of_package' => 'New Customer Discount'],
        ['name_of_package' => 'Loyalty Reward'],
        ['name_of_package' => 'Seasonal Clearance']
    ];

    foreach ($discounts as $discount) {
        $sql = "INSERT INTO discount (name_of_package) VALUES (?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $discount['name_of_package']);
        $stmt->execute();
    }
    echo "Discounts seeded successfully.\n";
}

// Function to seed discount details
function seed_discount_details($conn)
{
    $discount_details = [
        [1, 10, '2024-06-01', '2024-08-31'], // Summer Sale
        [2, 20, '2024-12-01', '2024-12-31'], // Holiday Special
        [3, 25, '2024-01-01', '2024-12-31'], // New Customer Discount
        [4, 15, '2024-01-01', '2024-12-31'], // Loyalty Reward
        [5, 10, '2024-03-01', '2024-03-31']  // Seasonal Clearance
    ];

    foreach ($discount_details as $dd) {
        $sql = "INSERT INTO discount_details (discount_id, percentage, start_date, end_date) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiss", $dd[0], $dd[1], $dd[2], $dd[3]);
        $stmt->execute();
    }
    echo "Discount details seeded successfully.\n";
}

// Function to seed payment methods
function seed_payment_methods($conn)
{
    $payment_methods = [
        ['name_of_method' => 'Credit Card'],
        ['name_of_method' => 'Debit Card'],
        ['name_of_method' => 'PayPal'],
        ['name_of_method' => 'Apple Pay'],
        ['name_of_method' => 'Google Pay'],
        ['name_of_method' => 'Bank Transfer']
    ];

    foreach ($payment_methods as $pm) {
        $sql = "INSERT INTO payment_method (name_of_method) VALUES (?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $pm['name_of_method']);
        $stmt->execute();
    }
    echo "Payment methods seeded successfully.\n";
}

// Function to seed recepties (receipts)
function seed_recepties($conn)
{
    $recepties = [
        [1, '2024-01-15', 250.00],
        [2, '2024-01-20', 180.00],
        [1, '2024-02-05', 320.00],
        [4, '2024-02-10', 150.00],
        [5, '2024-02-15', 280.00]
    ];

    foreach ($recepties as $receipt) {
        $sql = "INSERT INTO recepties (user_id, date, total) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isd", $receipt[0], $receipt[1], $receipt[2]);
        $stmt->execute();
    }
    echo "Receipts seeded successfully.\n";
}

// Function to seed orders
function seed_orders($conn)
{
    $orders = [
        [1, 1, 1, 150.00, 1, 1], // Receipt 1, Product-Brand 1, Discount 1
        [1, 2, 2, 120.00, 1, 1], // Receipt 1, Product-Brand 2, Discount 2
        [2, 3, 3, 140.00, 1, 2], // Receipt 2, Product-Brand 3, Discount 3
        [2, 4, 4, 110.00, 1, 2], // Receipt 2, Product-Brand 4, Discount 4
        [3, 5, 5, 130.00, 2, 1], // Receipt 3, Product-Brand 5, Discount 5
        [3, 6, 1, 100.00, 1, 1], // Receipt 3, Product-Brand 6, Discount 1
        [4, 7, 2, 125.00, 1, 3], // Receipt 4, Product-Brand 7, Discount 2
        [5, 8, 3, 160.00, 1, 4], // Receipt 5, Product-Brand 8, Discount 3
        [5, 9, 4, 90.00, 2, 4],  // Receipt 5, Product-Brand 9, Discount 4
        [5, 10, 5, 95.00, 1, 5]  // Receipt 5, Product-Brand 10, Discount 5
    ];

    foreach ($orders as $order) {
        $sql = "INSERT INTO `order` (recepties_id, product_brand_id, discount_details_id, line_total, qty, payment_method_id) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiidii", $order[0], $order[1], $order[2], $order[3], $order[4], $order[5]);
        $stmt->execute();
    }
    echo "Orders seeded successfully.\n";
}

// Function to seed images based on real data and actual files
function seed_images($conn)
{
    $images = [
        // Brand logos (these are placeholder names since actual logo files aren't in uploads)
        ['type' => 'brand', 'target_id' => '1', 'img' => 'chanel_logo.png'],
        ['type' => 'brand', 'target_id' => '2', 'img' => 'dior_logo.png'],
        ['type' => 'brand', 'target_id' => '3', 'img' => 'gucci_logo.png'],
        ['type' => 'brand', 'target_id' => '4', 'img' => 'versace_logo.png'],
        ['type' => 'brand', 'target_id' => '5', 'img' => 'tomford_logo.png'],

        // Product images using actual files from uploads folder
        ['type' => 'product', 'target_id' => '1', 'img' => 'upload/20250812_153815_689b43c73bb19.jpg'],
        ['type' => 'product', 'target_id' => '2', 'img' => 'upload/20250812_154933_689b466d5cd1f.jpg'],
        ['type' => 'product', 'target_id' => '3', 'img' => 'upload/20250812_155141_689b46ed750d0.jpg'],
        ['type' => 'product', 'target_id' => '4', 'img' => 'sauvage.jpg'],
        ['type' => 'product', 'target_id' => '5', 'img' => 'upload/20250812_182910_689b6bd67b4ff.jpg'],
        ['type' => 'product', 'target_id' => '6', 'img' => 'upload/20250813_044921_689bfd319cfa5.jpg'],
        ['type' => 'product', 'target_id' => '7', 'img' => 'upload/20250813_121007_689c647fba253.jpg'],
        ['type' => 'product', 'target_id' => '8', 'img' => 'upload/20250813_121408_689c6570280ce.jpg'],
        ['type' => 'product', 'target_id' => '9', 'img' => 'upload/20250813_121604_689c65e45333d.jpg'],
        ['type' => 'product', 'target_id' => '10', 'img' => 'upload/20250813_133933_689c797552165.jpg'],
        ['type' => 'product', 'target_id' => '11', 'img' => 'upload/20250813_134928_689c7bc8084f6.jpg'],
        ['type' => 'product', 'target_id' => '12', 'img' => 'upload/20250813_135247_689c7c8f9795c.jpg'],
        ['type' => 'product', 'target_id' => '18', 'img' => 'upload/20250813_135636_689c7d749f3ce.jpg'],
        ['type' => 'product', 'target_id' => '19', 'img' => 'upload/20250813_135636_689c7d749f3ce.jpg'], // Using available image
        ['type' => 'product', 'target_id' => '20', 'img' => 'upload/20250813_135636_689c7d749f3ce.jpg'], // Using available image
        ['type' => 'product', 'target_id' => '21', 'img' => 'upload/20250813_135636_689c7d749f3ce.jpg'], // Using available image
        ['type' => 'product', 'target_id' => '22', 'img' => 'upload/20250813_135636_689c7d749f3ce.jpg'], // Using available image
        ['type' => 'product', 'target_id' => '23', 'img' => 'upload/20250813_135636_689c7d749f3ce.jpg'], // Using available image
        ['type' => 'product', 'target_id' => '24', 'img' => 'upload/20250813_135636_689c7d749f3ce.jpg'], // Using available image
        ['type' => 'product', 'target_id' => '25', 'img' => 'upload/20250813_135636_689c7d749f3ce.jpg'], // Using available image
        ['type' => 'product', 'target_id' => '26', 'img' => 'upload/20250813_135636_689c7d749f3ce.jpg']  // Using available image
    ];

    foreach ($images as $image) {
        $sql = "INSERT INTO image (type, target_id, img) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $image['type'], $image['target_id'], $image['img']);
        $stmt->execute();
    }
    echo "Images seeded successfully.\n";
}

// Main seeding function
function run_seeder($conn)
{
    echo "Starting database seeding with real data...\n";
    echo "==========================================\n";

    // Clear existing data
    clear_tables($conn);

    // Seed data in order of dependencies
    seed_users($conn);
    seed_brands($conn);
    seed_products($conn);
    seed_product_brands($conn);
    seed_discounts($conn);
    seed_discount_details($conn);
    seed_payment_methods($conn);
    seed_recepties($conn);
    seed_orders($conn);
    seed_images($conn);

    echo "==========================================\n";
    echo "Database seeding completed successfully!\n";
    echo "Note: This seeder now uses your real database structure and actual image files.\n";
}

// Run the seeder
run_seeder($conn);

$conn->close();
