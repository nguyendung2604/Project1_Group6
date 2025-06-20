# WirelessWorld - Electronics Store Website

A complete e-commerce website where you can browse, search, and buy electronics online.

## What This Website Does

- **Browse Products**: View smartphones, tablets, laptops, and accessories
- **Search & Filter**: Find products by brand, category, price range, and specs
- **Shopping Cart**: Add items to cart and manage quantities
- **User Accounts**: Register, login, and manage your profile
- **Place Orders**: Complete purchases with shipping information
- **Admin Panel**: Manage products, users, and orders (for admins)

## Installation Guide

### Requirements
- XAMPP (includes Apache, MySQL, PHP)
- A web browser

### Step 1: Install XAMPP
1. Download XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Install it on your computer
3. Start Apache and MySQL from XAMPP Control Panel

### Step 2: Copy Website Files
1. Copy all project files to this folder:
   ```
   /Applications/XAMPP/xamppfiles/htdocs/wirelessworld/
   ```

### Step 3: Create Database
1. Open your browser and go to: `http://localhost/phpmyadmin`
2. Create a new database called `project`
3. Click "Import" tab
4. Choose the file: `wirelessworld.sql`
5. Click "Import" button

### Step 4: Open the Website
1. Open your browser
2. Go to: `http://localhost/wirelessworld/`
3. The website should now work!

## Test Accounts

You can use these accounts to test the website:

### Admin Account
- **Username**: `admin`
- **Email**: `admin@gmail.com`
- **Password**: 123456
- **What you can do**: Everything - manage users, products, orders



The website uses 9 main tables:

### 1. Users Table
Stores customer and admin account information

### 2. Products Table  
Stores all product information with detailed specs (RAM, storage, camera, etc.)

### 3. Product Images Table
Stores multiple photos for each product

### 4. Categories Table
Organizes products into categories (Smartphone, Tablets, Laptop, etc.)

### 5. Brands Table
Stores brand information (Samsung, Apple, Xiaomi, etc.)

### 6. Carts Table
Stores shopping cart information for each user

### 7. Cart Items Table
Stores individual items added to shopping carts

### 8. Orders Table
Stores completed order information with shipping details

### 9. Order Items Table
Stores individual items in each completed order

## Sample Data Included

The database comes with:

### Products Available:
**Smartphones:**
- Samsung Galaxy S25 Ultra ($1,200)
- Samsung Galaxy S24 ($900)
- Samsung Galaxy A56 5G ($370)
- Samsung Galaxy Z Fold6 ($1,800)
- iPhone 15 Pro Max ($1,300)
- iPhone 15 ($930)
- iPhone 14 ($800)
- iPhone 13 ($730)
- Xiaomi 15 5G ($1,020)
- Xiaomi 14T ($730)
- Redmi 13x ($290)

**Tablets:**
- iPad Pro 11 ($1,000)
- iPad Mini ($600)
- iPad Air ($720)

### Brands Available:
Samsung, Apple, Xiaomi, Huawei, Nokia, Motorola, Sony, LG, OnePlus, Realme

### Categories Available:
Smartphone, Tablets, Laptop, Accessories, Wearable, Smart Home, Audio

## Key Features

- **Product Catalog**: Detailed product information with specs (screen size, RAM, storage, camera, battery)
- **Multiple Images**: Each product has 4+ high-quality images
- **Shopping Cart**: Add/remove items, update quantities
- **User Registration**: Secure account creation with password encryption
- **Order Management**: Track order status (pending/delivered)
- **Admin Controls**: Full product and user management
- **Responsive Design**: Works on desktop and mobile devices

## Troubleshooting

If you have problems:
1. Make sure XAMPP is running (Apache + MySQL)
2. Check if the database `project` was created correctly
3. Make sure all files are in the right folder
4. Verify the image folder has proper permissions
5. Check that product images are in the `/image/` directory

## Technical Details

- **Language**: PHP, HTML, CSS, JavaScript
- **Database**: MySQL/MariaDB 10.4.32
- **PHP Version**: 8.2.12
- **Server**: Apache (included in XAMPP)
- **Security**: Password hashing with bcrypt
- **Database Engine**: InnoDB with foreign key constraints

## File Requirements

Make sure these image files exist in `/image/` folder:
- Galaxy S25 series: `galaxy-s25-1.png` to `galaxy-s25-4.png`
- iPhone series: `iphone-15-den-1.webp`, etc.
- Xiaomi series: `xiaomi-15-1.jpg`, etc.
- iPad series: `ipad-air-1.webp`, etc.