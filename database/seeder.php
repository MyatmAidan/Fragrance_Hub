<?php
require_once 'dbrequire.php';

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
        'product_brand',
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
    $brands = ['Chanel', 'Dior', 'Gucci', 'Versace', 'Tom Ford', 'Yves Saint Laurent', 'Hermès', 'Bvlgari', 'Calvin Klein', 'Paco Rabanne', 'Louis Vuitton', 'Perfume De Marly', 'Xerjoff', 'Jean Paul Gaultier', 'Givenchy', 'Dolce & Gabbana', 'Replica'];
    $map = [];
    $stmt = $conn->prepare("INSERT INTO brand (brand_name) VALUES (?)");
    foreach ($brands as $b) {
        $stmt->bind_param("s", $b);
        $stmt->execute();
        $map[$b] = (int)$conn->insert_id;
    }
    echo "Brands seeded successfully.\n";
    return $map;
}

// Function to seed products based on real data
function seed_products($conn)
{
    $rows = [
        ['N°5 Eau de Parfum', 'The legendary fragrance that revolutionized perfumery', 'Female'],
        ['Bleu de Chanel', 'A woody aromatic fragrance for the modern man', 'Male'],
        ['Miss Dior', 'A romantic and elegant fragrance', 'Female'],
        ['Sauvage', 'A powerful and fresh masculine fragrance', 'Male'],
        ['Gucci Bloom', 'A celebration of the vitality and diversity of women', 'Female'],
        ['Versace Eros', 'A bold, seductive fragrance for the modern man', 'Male'],
        ['Black Opium', 'An addictive gourmand fragrance', 'Female'],
        ['Terre d\'Hermès', 'A sophisticated and natural fragrance', 'Male'],
        ['Omnia Crystalline', 'A fresh and light fragrance', 'Female'],
        ['1 Million', 'A provocative and seductive masculine fragrance', 'Male'],
        ['Dior Sauvage EDT', 'Dior Sauvage Eau de Toilette perfume', 'Male'],
        ['YSL Y EDP', 'Famous among teenagers for its unique scent', 'Male'],
        ['Lover', 'Louis Vuitton Lover fragrance', 'Male'],
        ['Oud Wood', 'Tom Ford Oud Wood fragrance', 'Male'],
        ['Imagination', 'Imagination by Louis Vuitton, a Citrus Aromatic scent', 'Male'],
        ['D&G The One', 'A spicy, oriental perfume for men', 'Male'],
        ['Jazz Club', 'Jazz Club by Maison Margiela, a Leather fragrance', 'Male']
    ];

    $map = [];
    $stmt = $conn->prepare("INSERT INTO product (product_name,description,gender) VALUES (?,?,?)");
    foreach ($rows as [$n, $d, $g]) {
        $stmt->bind_param("sss", $n, $d, $g);
        $stmt->execute();
        $map[$n] = (int)$conn->insert_id;
    }
    echo "Products seeded successfully.\n";
    return $map;
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
        [13, 11, 500.00, 100], // Louis Vuitton Lover
        [14, 5, 180.00, 30],  // Tom Ford Oud Wood
        [15, 1, 150.00, 50],  // Chanel N°5 (duplicate)
        [16, 1, 120.00, 40],  // Chanel Bleu (duplicate)
        [17, 2, 140.00, 35],  // Dior Miss Dior (duplicate)
        [18, 4, 100.00, 55],  // Versace Eros (duplicate)
        [19, 11, 450.00, 25], // Louis Vuitton Imagination
        [20, 16, 120.00, 60], // D&G The One
        [21, 17, 95.00, 40]   // Replica Jazz Club
    ];

    $product_brand_map = [];
    foreach ($product_brands as $index => $pb) {
        $sql = "INSERT INTO product_brand (product_id, brand_id, price, Qty) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iidi", $pb[0], $pb[1], $pb[2], $pb[3]);
        $stmt->execute();
        $product_brand_map[$index + 1] = (int)$conn->insert_id;
    }
    echo "Product-Brand relationships seeded successfully.\n";
    return $product_brand_map;
}

// Function to seed discounts

function seed_discounts($conn)
{
    $names = ['Summer Sale', 'Holiday Special', 'New Customer Discount', 'Loyalty Reward', 'Seasonal Clearance'];
    $stmt = $conn->prepare("INSERT INTO `discount` (name_of_package) VALUES (?)");
    foreach ($names as $n) {
        $stmt->bind_param("s", $n);
        $stmt->execute();
    }
    echo "Discounts seeded successfully.\n";
}

function seed_discount_details($conn)
{
    $rows = [
        [1, 10, '2024-06-01', '2024-08-31'],
        [2, 20, '2024-12-01', '2024-12-31'],
        [3, 25, '2024-01-01', '2024-12-31'],
        [4, 15, '2024-01-01', '2024-12-31'],
        [5, 10, '2024-03-01', '2024-03-31']
    ];
    $stmt = $conn->prepare("INSERT INTO `discount_details` (discount_id, percentage, start_date, end_date) VALUES (?,?,?,?)");
    foreach ($rows as [$did, $pct, $s, $e]) {
        $stmt->bind_param("iiss", $did, $pct, $s, $e);
        $stmt->execute();
    }
    echo "Discount details seeded successfully.\n";
}

function seed_payment_methods($conn)
{
    $names = ['Credit Card', 'Debit Card', 'PayPal', 'Apple Pay', 'Google Pay', 'Bank Transfer'];
    $stmt = $conn->prepare("INSERT INTO `payment_method` (name_of_method) VALUES (?)");
    foreach ($names as $n) {
        $stmt->bind_param("s", $n);
        $stmt->execute();
    }
    echo "Payment methods seeded successfully.\n";
}

function seed_recepties($conn)
{
    $rows = [
        [1, '2024-01-15', 250.00],
        [2, '2024-01-20', 180.00],
        [1, '2024-02-05', 320.00],
        [4, '2024-02-10', 150.00],
        [5, '2024-02-15', 280.00]
    ];
    $stmt = $conn->prepare("INSERT INTO `recepties` (user_id, date, total) VALUES (?,?,?)");
    foreach ($rows as [$uid, $date, $total]) {
        $stmt->bind_param("isd", $uid, $date, $total);
        $stmt->execute();
    }
    echo "Receipts seeded successfully.\n";
}

// Function to seed orders
function seed_orders($conn, $product_brand_map)
{
    // Use the actual product_brand_id values from the mapping
    $rows = [
        [1, $product_brand_map[1], 1, 150.00, 1, 1],   // Chanel N°5
        [1, $product_brand_map[2], 2, 120.00, 1, 1],   // Chanel Bleu
        [2, $product_brand_map[3], 3, 140.00, 1, 2],   // Dior Miss Dior
        [2, $product_brand_map[4], 4, 110.00, 1, 2],   // Dior Sauvage
        [3, $product_brand_map[5], 5, 130.00, 2, 1],   // Gucci Bloom
        [3, $product_brand_map[6], 1, 100.00, 1, 1],   // Versace Eros
        [4, $product_brand_map[7], 2, 125.00, 1, 3],   // YSL Black Opium
        [5, $product_brand_map[8], 3, 160.00, 1, 4],   // Hermès Terre
        [5, $product_brand_map[9], 4, 90.00, 2, 4],    // Bvlgari Omnia
        [5, $product_brand_map[10], 5, 95.00, 1, 5]    // Paco Rabanne 1 Million
    ];
    $stmt = $conn->prepare("INSERT INTO `order` (recepties_id, product_brand_id, discount_details_id, line_total, qty, payment_method_id) VALUES (?,?,?,?,?,?)");
    foreach ($rows as [$rid, $pbid, $ddid, $lt, $qty, $pm]) {
        $stmt->bind_param("iiidii", $rid, $pbid, $ddid, $lt, $qty, $pm);
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
    $product_brand_map = seed_product_brands($conn);
    seed_discounts($conn);
    seed_discount_details($conn);
    seed_payment_methods($conn);
    seed_recepties($conn);
    seed_orders($conn, $product_brand_map);
    seed_images($conn);

    echo "==========================================\n";
    echo "Database seeding completed successfully!\n";
    echo "Note: This seeder now uses your real database structure and actual image files.\n";
}


// Run the seeder
run_seeder($conn);

$conn->close();
