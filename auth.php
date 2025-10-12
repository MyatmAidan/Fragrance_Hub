<?php
session_start();
require_once 'database/dbrequire.php';
require_once 'database/central_function.php';

class Auth
{
    private $conn;

    public function __construct($connection)
    {
        $this->conn = $connection;
    }

    // Register a new user
    public function register($name, $email, $password, $address = '', $phone = '')
    {
        // Check if email already exists
        $check_query = "SELECT user_id FROM user WHERE email = ?";
        $stmt = $this->conn->prepare($check_query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return ['success' => false, 'message' => 'Email already exists'];
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user
        $insert_query = "INSERT INTO user (user_name, email, password, address, phone, role) VALUES (?, ?, ?, ?, ?, 0)";
        $stmt = $this->conn->prepare($insert_query);
        $stmt->bind_param("sssss", $name, $email, $hashed_password, $address, $phone);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Registration successful'];
        } else {
            return ['success' => false, 'message' => 'Registration failed'];
        }
    }

    // Login user
    public function login($email, $password)
    {
        $query = "SELECT user_id, user_name, email, password, role FROM user WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_name'] = $user['user_name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['logged_in'] = true;

                return ['success' => true, 'message' => 'Login successful', 'user' => $user];
            } else {
                return ['success' => false, 'message' => 'Invalid password'];
            }
        } else {
            return ['success' => false, 'message' => 'User not found'];
        }
    }

    // Logout user
    public function logout()
    {
        session_destroy();
        return ['success' => true, 'message' => 'Logged out successfully'];
    }

    // Check if user is logged in
    public function isLoggedIn()
    {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    // Get current user data
    public function getCurrentUser()
    {
        if ($this->isLoggedIn()) {
            return [
                'user_id' => $_SESSION['user_id'],
                'user_name' => $_SESSION['user_name'],
                'user_email' => $_SESSION['user_email'],
                'user_role' => $_SESSION['user_role']
            ];
        }
        return null;
    }

    // Check if user is admin
    public function isAdmin()
    {
        return $this->isLoggedIn() && $_SESSION['user_role'] == 1;
    }

    // Require login for protected pages
    public function requireLogin($redirect_url = 'login.php')
    {
        if (!$this->isLoggedIn()) {
            header("Location: $redirect_url");
            exit();
        }
    }

    // Require admin access
    public function requireAdmin($redirect_url = 'login.php')
    {
        if (!$this->isAdmin()) {
            header("Location: $redirect_url");
            exit();
        }
    }

    // Update user profile
    public function updateProfile($user_id, $name, $email, $address = '', $phone = '')
    {
        // Check if email is being changed and if it already exists
        if ($email !== $_SESSION['user_email']) {
            $check_query = "SELECT user_id FROM user WHERE email = ? AND user_id != ?";
            $stmt = $this->conn->prepare($check_query);
            $stmt->bind_param("si", $email, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                return ['success' => false, 'message' => 'Email already exists'];
            }
        }

        $update_query = "UPDATE user SET user_name = ?, email = ?, address = ?, phone = ? WHERE user_id = ?";
        $stmt = $this->conn->prepare($update_query);
        $stmt->bind_param("ssssi", $name, $email, $address, $phone, $user_id);

        if ($stmt->execute()) {
            // Update session variables
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;

            return ['success' => true, 'message' => 'Profile updated successfully'];
        } else {
            return ['success' => false, 'message' => 'Profile update failed'];
        }
    }

    // Change password
    public function changePassword($user_id, $current_password, $new_password)
    {
        // Verify current password
        $query = "SELECT password FROM user WHERE user_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($current_password, $user['password'])) {
                // Update password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_query = "UPDATE user SET password = ? WHERE user_id = ?";
                $stmt = $this->conn->prepare($update_query);
                $stmt->bind_param("si", $hashed_password, $user_id);

                if ($stmt->execute()) {
                    return ['success' => true, 'message' => 'Password changed successfully'];
                } else {
                    return ['success' => false, 'message' => 'Password change failed'];
                }
            } else {
                return ['success' => false, 'message' => 'Current password is incorrect'];
            }
        } else {
            return ['success' => false, 'message' => 'User not found'];
        }
    }
}

// Initialize auth object
$auth = new Auth($conn);

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'login':
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $result = $auth->login($email, $password);
            echo json_encode($result);
            exit();

        case 'register':
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $address = $_POST['address'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $result = $auth->register($name, $email, $password, $address, $phone);
            echo json_encode($result);
            exit();

        case 'logout':
            $result = $auth->logout();
            echo json_encode($result);
            exit();

        case 'update_profile':
            if ($auth->isLoggedIn()) {
                $user_id = $_SESSION['user_id'];
                $name = $_POST['name'] ?? '';
                $email = $_POST['email'] ?? '';
                $address = $_POST['address'] ?? '';
                $phone = $_POST['phone'] ?? '';
                $result = $auth->updateProfile($user_id, $name, $email, $address, $phone);
                echo json_encode($result);
            } else {
                echo json_encode(['success' => false, 'message' => 'Not logged in']);
            }
            exit();

        case 'change_password':
            if ($auth->isLoggedIn()) {
                $user_id = $_SESSION['user_id'];
                $current_password = $_POST['current_password'] ?? '';
                $new_password = $_POST['new_password'] ?? '';
                $result = $auth->changePassword($user_id, $current_password, $new_password);
                echo json_encode($result);
            } else {
                echo json_encode(['success' => false, 'message' => 'Not logged in']);
            }
            exit();
    }
}
