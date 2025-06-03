
<?php
/**
 * Database connection handler for XAMPP (MariaDB)
 */

/**
 * Create necessary database tables if they don't exist
 *
 * @param PDO $db Database connection
 */
function create_tables($db) {
    try {
        // Create Users table
        $db->exec("
            INSERT INTO users (username, password, name, email, phone) 
            VALUES ('john', '" . password_hash('password123', PASSWORD_DEFAULT) . "', 'John Doe', 'john@example.com', '555-123-4567')
        ");
    } catch (PDOException $e) {
        error_log('Error creating tables: ' . $e->getMessage());
    }
}

// Database connection details for XAMPP
$db_host = 'localhost';
$db_name = 'delivery_platform';
$db_user = 'root';
$db_pass = '';

// Attempt to establish connection
try {
    $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
    $db = new PDO($dsn, $db_user, $db_pass);
    
    // Set PDO to throw exceptions on error
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set default fetch mode to associative array
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Use prepared statements
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
    // Create tables if they don't exist
    create_tables($db);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Database query functions
function db_query($sql, $params = []) {
    global $db;
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Database query error: " . $e->getMessage());
        return [];
    }
}

function db_query_single($sql, $params = []) {
    global $db;
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Database query error: " . $e->getMessage());
        return false;
    }
}

function db_execute($sql, $params = []) {
    global $db;
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    } catch (PDOException $e) {
        error_log("Database execution error: " . $e->getMessage());
        return false;
    }
}

function db_last_insert_id() {
    global $db;
    return $db->lastInsertId();
}