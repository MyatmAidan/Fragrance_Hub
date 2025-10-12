# Fragrance Hub - User Authentication System

## Overview
This is a complete user authentication system for the Fragrance Hub e-commerce application. The system includes user registration, login, profile management, and role-based access control.

## Features

### Authentication System
- **User Registration**: Users can create accounts with email, password, name, address, and phone
- **User Login**: Secure login with email and password
- **Password Security**: Passwords are hashed using PHP's `password_hash()` function
- **Session Management**: Secure session handling with proper logout functionality
- **Role-Based Access**: Different access levels for regular users and administrators

### User Interface
- **User Dashboard**: Personalized dashboard showing order history and account statistics
- **Product Browsing**: Browse and search products with filtering options
- **Shopping Cart**: Add products to cart and manage quantities
- **Order History**: View past orders with detailed information
- **Profile Management**: Update personal information and change password

### Admin Interface
- **Admin Dashboard**: Administrative dashboard for managing the system
- **Product Management**: Create and manage products and brands
- **User Management**: View and manage user accounts
- **Profile Management**: Admin profile management

## File Structure

```
Fragrance_Hub/
├── auth.php                 # Main authentication class and API endpoints
├── login.php               # User login page
├── register.php            # User registration page
├── index.php               # Main entry point with role-based redirects
├── user/                   # User interface files
│   ├── dashboard.php       # User dashboard
│   ├── products.php        # Product browsing page
│   ├── orders.php          # Order history page
│   ├── profile.php         # User profile management
│   ├── cart.php            # Shopping cart functionality
│   └── order_details.php   # Order details modal content
├── admin/                  # Admin interface files
│   ├── index.php           # Admin dashboard
│   ├── profile.php         # Admin profile management
│   └── layout/header.php   # Admin header with authentication
├── database/
│   ├── dbrequire.php       # Database connection and table creation
│   ├── central_function.php # Database helper functions
│   └── seeder.php          # Database seeding with test data
└── assets/                 # CSS, JS, and image assets
```

## Database Schema

### User Table
- `user_id`: Primary key
- `user_name`: User's full name
- `email`: Unique email address
- `password`: Hashed password
- `address`: User's address
- `phone`: User's phone number
- `role`: User role (0 = regular user, 1 = admin)
- `created_at`, `updated_at`: Timestamps

### Cart Table
- `cart_id`: Primary key
- `user_id`: Foreign key to user table
- `product_id`: Foreign key to product table
- `brand_id`: Foreign key to brand table
- `quantity`: Number of items
- `created_at`, `updated_at`: Timestamps

## Installation & Setup

1. **Database Setup**:
   ```bash
   # Run the database setup
   php database/dbrequire.php
   
   # Seed the database with test data
   php database/seeder.php
   ```

2. **Default Admin Account**:
   - Email: `admin@fragrancehub.com`
   - Password: `admin123`

3. **Test User Accounts**:
   - Email: `john@example.com` / Password: `password123`
   - Email: `jane@example.com` / Password: `password123`

## Usage

### For Users
1. **Registration**: Visit `/register.php` to create a new account
2. **Login**: Visit `/login.php` to sign in
3. **Dashboard**: After login, users are redirected to `/user/dashboard.php`
4. **Browse Products**: Visit `/user/products.php` to view available products
5. **Manage Cart**: Add products to cart and manage quantities
6. **View Orders**: Check order history at `/user/orders.php`
7. **Update Profile**: Manage account information at `/user/profile.php`

### For Administrators
1. **Admin Login**: Use admin credentials to login
2. **Admin Dashboard**: Access admin panel at `/admin/index.php`
3. **Manage Products**: Create and manage products and brands
4. **User Management**: View and manage user accounts
5. **Profile Management**: Update admin profile at `/admin/profile.php`

## Security Features

- **Password Hashing**: All passwords are securely hashed using PHP's `password_hash()`
- **Session Security**: Proper session management with secure logout
- **SQL Injection Prevention**: All database queries use prepared statements
- **XSS Protection**: User input is properly escaped using `htmlspecialchars()`
- **Role-Based Access**: Different access levels for users and administrators
- **Authentication Required**: All protected pages require valid authentication

## API Endpoints

The `auth.php` file provides the following API endpoints:

- `POST auth.php?action=login` - User login
- `POST auth.php?action=register` - User registration
- `POST auth.php?action=logout` - User logout
- `POST auth.php?action=update_profile` - Update user profile
- `POST auth.php?action=change_password` - Change user password

## Technologies Used

- **Backend**: PHP 7.4+
- **Database**: MySQL
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **UI Framework**: Material Dashboard 3
- **Security**: PHP password hashing, prepared statements

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## Future Enhancements

- Email verification for new accounts
- Password reset functionality
- Two-factor authentication
- Social media login integration
- Advanced order management
- Payment gateway integration
- Product reviews and ratings
- Wishlist functionality

## Support

For technical support or questions about the authentication system, please refer to the code documentation or contact the development team.
