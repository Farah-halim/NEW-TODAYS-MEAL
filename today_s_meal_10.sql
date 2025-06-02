-- Users Table
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    u_name VARCHAR(100) NOT NULL,
    mail VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    reset_token VARCHAR(255) DEFAULT NULL,
    reset_token_expiry DATETIME DEFAULT NULL,
    u_role ENUM('admin', 'external_user', 'delivery_man') NOT NULL
);

-- Admin Table
CREATE TABLE admin (
    user_id INT PRIMARY KEY,
    cat_id INT DEFAULT NULL,
    last_login TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (cat_id) REFERENCES cloud_kitchen_owner (user_id) ON DELETE SET NULL
);

-- External User
CREATE TABLE external_user (
    user_id INT PRIMARY KEY,
    address VARCHAR(255),
    ext_role ENUM('customer', 'cloud_kitchen_owner') NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Customer
CREATE TABLE customer (
    user_id INT PRIMARY KEY,
    gender ENUM('Male', 'Female') NOT NULL,
    BOD DATE NOT NULL,
    status ENUM('active','suspended','blocked') DEFAULT 'active',
    is_subscribed BOOLEAN DEFAULT FALSE,
    last_login TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    registration_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES external_user(user_id) ON DELETE CASCADE
);

-- Cloud Kitchen Owner
CREATE TABLE cloud_kitchen_owner (
    user_id INT PRIMARY KEY,
    start_year YEAR(4) NOT NULL,
    c_n_id VARCHAR(50) NOT NULL,
    status ENUM('active','suspended','blocked') DEFAULT 'active',
    orders_count INT DEFAULT 0,
    address TEXT DEFAULT NULL,
    business_name VARCHAR(255) NOT NULL,
    registration_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    years_of_experience ENUM('Beginner (0-1 years)', 'Intermediate (2-3 years)', 'Advanced (4-5 years)', 'Expert (6+ years)') NOT NULL,
    customized_orders BOOLEAN DEFAULT 0,
    average_rating DECIMAL(3,2) DEFAULT 0,
    is_approved BOOLEAN DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES external_user(user_id) ON DELETE CASCADE
);

-- Delivery Man
CREATE TABLE delivery_man (
    user_id INT PRIMARY KEY,
    d_n_id VARCHAR(20) UNIQUE,
    d_license VARCHAR(50) NOT NULL UNIQUE,
    d_zone VARCHAR(255) NOT NULL,
    status ENUM('online','offline') DEFAULT 'offline',
    current_status ENUM('free','busy') DEFAULT 'free',
    is_approved BOOLEAN DEFAULT 0,
    registration_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Cloud Kitchen Specialist Category
CREATE TABLE cloud_kitchen_specialist_category (
    cloud_kitchen_id INT NOT NULL,
    cat_id INT NOT NULL,
    PRIMARY KEY (cloud_kitchen_id, cat_id),
    FOREIGN KEY (cloud_kitchen_id) REFERENCES cloud_kitchen_owner(user_id),
    FOREIGN KEY (cat_id) REFERENCES category(cat_id)
);

-- Category
CREATE TABLE category (
    cat_id INT PRIMARY KEY AUTO_INCREMENT,
    c_name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT DEFAULT NULL,
    category_photo VARCHAR(255) DEFAULT NULL,
    created_by INT DEFAULT NULL,
    cloud_kitchens_count INT DEFAULT 0,
    FOREIGN KEY (created_by) REFERENCES admin(user_id)
);

-- Subcategory
CREATE TABLE sub_category (
    subcat_id INT PRIMARY KEY AUTO_INCREMENT,
    subcat_name VARCHAR(100) NOT NULL,
    admin_id INT NOT NULL,
    parent_cat_id int(11) NOT NULL,
    FOREIGN KEY (parent_cat_id) REFERENCES category(cat_id),
    FOREIGN KEY (admin_id) REFERENCES admin (user_id)
);

-- Dietary Tags
CREATE TABLE dietary_tags (
    tag_id INT PRIMARY KEY AUTO_INCREMENT,
    tag_name VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE caterer_tags (
    user_id INT NOT NULL,
    tag_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES cloud_kitchen_owner (user_id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES dietary_tags (tag_id) ON DELETE CASCADE
);

-- Meals
CREATE TABLE meals (
    meal_id INT PRIMARY KEY AUTO_INCREMENT,
    cloud_kitchen_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    photo VARCHAR(255) DEFAULT NULL,
    stock_quantity INT NOT NULL DEFAULT 0,
    visible BOOLEAN DEFAULT TRUE,
    status ENUM('out of stock', 'low stock', 'available') DEFAULT 'out of stock',
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (cloud_kitchen_id) REFERENCES cloud_kitchen_owner(user_id)
);

CREATE TABLE meal_dietary_tag (
    meal_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (meal_id, tag_id),
    FOREIGN KEY (meal_id) REFERENCES meals(meal_id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES dietary_tags(tag_id) ON DELETE CASCADE
);

-- Meal Category and Subcategory Relations
CREATE TABLE meal_category (
    meal_id INT NOT NULL,
    cat_id INT NOT NULL,
    PRIMARY KEY (meal_id, cat_id),
    FOREIGN KEY (meal_id) REFERENCES meals(meal_id),
    FOREIGN KEY (cat_id) REFERENCES category(cat_id)
);

CREATE TABLE meal_subcategory (
    meal_id INT NOT NULL,
    subcat_id INT NOT NULL,
    PRIMARY KEY (meal_id, subcat_id),
    FOREIGN KEY (meal_id) REFERENCES meals(meal_id),
    FOREIGN KEY (subcat_id) REFERENCES sub_category(subcat_id)
);

CREATE TABLE cart (
    cart_id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT NOT NULL,
    cloud_kitchen_id INT NOT NULL,  
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customer(user_id),
    FOREIGN KEY (cloud_kitchen_id) REFERENCES cloud_kitchen_owner(user_id)
);

CREATE TABLE cart_items (
    cart_item_id INT PRIMARY KEY AUTO_INCREMENT,
    cart_id INT NOT NULL,
    meal_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (cart_id) REFERENCES cart(cart_id),
    FOREIGN KEY (meal_id) REFERENCES meals(meal_id)
);

-- Orders
CREATE TABLE orders (
    order_id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT NOT NULL,
    cloud_kitchen_id INT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ord_type ENUM('customized', 'normal', 'scheduled') NOT NULL,
    delivery_type ENUM('all_at_once', 'daily_delivery') DEFAULT NULL,
    delivery_date TIMESTAMP NULL,
    delivery_zone VARCHAR(255) NOT NULL,
    order_status ENUM('pending', 'preparing', 'ready_for_pickup', 'in_transit', 'delivered', 'cancelled') DEFAULT 'pending';
    FOREIGN KEY (customer_id) REFERENCES customer(user_id),
    FOREIGN KEY (cloud_kitchen_id) REFERENCES cloud_kitchen_owner(user_id)
);

-- Customized Order
CREATE TABLE customized_order (
    order_id INT PRIMARY KEY,
    customer_id INT NOT NULL,
    kitchen_id INT NOT NULL,
    budget_min DECIMAL(10,2) NOT NULL,
    budget_max DECIMAL(10,2) NOT NULL,
    chosen_amount DECIMAL(10,2) DEFAULT NULL,
    status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
    ord_description TEXT NOT NULL,
    img_reference VARCHAR(255) DEFAULT NULL,
    people_servings INT NOT NULL,
    preferred_completion_date DATE NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customer(user_id) ON DELETE CASCADE,
    FOREIGN KEY (kitchen_id) REFERENCES cloud_kitchen_owner(user_id) ON DELETE CASCADE
);

-- Order Content
CREATE TABLE order_content (
    order_content_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    meal_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (meal_id) REFERENCES meals(meal_id)
);

-- Order Packages
CREATE TABLE order_packages (
    package_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    package_name VARCHAR(255) NOT NULL,
    delivery_date DATE NOT NULL,
    package_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id)
);

CREATE TABLE meals_in_each_package (
    package_meal_id INT PRIMARY KEY AUTO_INCREMENT,
    package_id INT NOT NULL,
    meal_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (package_id) REFERENCES order_packages(package_id),
    FOREIGN KEY (meal_id) REFERENCES meals(meal_id)
);

-- Delivery Details
CREATE TABLE delivery_details (
    delivery_id INT PRIMARY KEY AUTO_INCREMENT,
    d_location TEXT NOT NULL,
    p_method ENUM('cash','visa') NOT NULL,
    delivery_date_and_time DATETIME NOT NULL,
    d_status ENUM('delivered','not_delivered') NOT NULL,
    ord_id INT,
    user_id INT,
    FOREIGN KEY (ord_id) REFERENCES orders (order_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES delivery_man (user_id) ON DELETE SET NULL
);

-- Payment Details
CREATE TABLE payment_details (
    payment_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT,
    total_ord_price DECIMAL(10,2) NOT NULL,
    delivery_fees DECIMAL(10,2) NOT NULL,
    website_revenue DECIMAL(10,2) NOT NULL,
    total_payment DECIMAL(10,2) NOT NULL,
    p_date_time DATETIME NOT NULL,
    p_method ENUM('cash', 'visa') NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id)
);

-- Reviews
CREATE TABLE reviews (
    review_no INT PRIMARY KEY AUTO_INCREMENT,
    stars TINYINT UNSIGNED NOT NULL,
    order_id INT NOT NULL,
    cloud_kitchen_id INT NOT NULL,
    customer_id INT NOT NULL,
    review_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (cloud_kitchen_id) REFERENCES cloud_kitchen_owner(user_id),
    FOREIGN KEY (customer_id) REFERENCES customer(user_id)
);

-- Complaints
CREATE TABLE complaints (
    id INT PRIMARY KEY AUTO_INCREMENT,
    subject VARCHAR(255) DEFAULT NULL,
    message TEXT DEFAULT NULL,
    status ENUM('pending','resolved') DEFAULT 'pending',
    `customer_id` int(11) DEFAULT NULL,
  `kitchen_id` int(11) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (customer_id) REFERENCES customer(user_id) ON DELETE SET NULL,
    FOREIGN KEY (kitchen_id) REFERENCES cloud_kitchen_owner(user_id) ON DELETE SET NULL
);

-- Delivery Subscriptions
CREATE TABLE delivery_subscriptions (
    subscription_id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT NOT NULL UNIQUE,
    plan_duration ENUM('1_month', '6_months', '12_months') NOT NULL,
    start_date DATE NOT NULL DEFAULT CURRENT_DATE,
    end_date DATE NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
    FOREIGN KEY (customer_id) REFERENCES customer(user_id) ON DELETE CASCADE
);

