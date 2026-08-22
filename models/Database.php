<?php
class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    public $conn;

    public function __construct() {
        $this->createTables();
    }

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }

    public function createTables() {
        try {
            $conn = $this->getConnection();
            
            // Users table
            $sql = "CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                first_name VARCHAR(100) NOT NULL,
                last_name VARCHAR(100) NOT NULL,
                email VARCHAR(255) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                phone VARCHAR(20),
                address TEXT,
                role ENUM('homeowner', 'technician', 'admin') NOT NULL,
                profession VARCHAR(100),
                certification_file VARCHAR(255),
                status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            
            $conn->exec($sql);
            
            // Services table
            $sql = "CREATE TABLE IF NOT EXISTS services (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                description TEXT,
                image VARCHAR(255),
                price_range VARCHAR(100),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            
            $conn->exec($sql);
            
            // Insert sample services
            $this->insertSampleServices();

            // Email verifications table
            $sql = "CREATE TABLE IF NOT EXISTS email_verifications (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                token VARCHAR(128) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                verified_at TIMESTAMP NULL,
                UNIQUE KEY token_unique (token),
                CONSTRAINT fk_email_verifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )";
            $conn->exec($sql);
            
        } catch(PDOException $exception) {
            echo "Error creating tables: " . $exception->getMessage();
        }
    }
    
    private function insertSampleServices() {
    $conn = $this->getConnection();
    
    $check = $conn->query("SELECT COUNT(*) FROM services");
    if ($check->fetchColumn() == 0) {
        $services = [
            ['Plumbing', 'Fix leaks, install fixtures, drain cleaning', 'plumbing.jpg', '$50-$150'],
            ['Electrical', 'Wiring, outlets, lighting installation', 'electrical.jpg', '$75-$200'],
            ['HVAC', 'Heating, ventilation, air conditioning', 'hvac.jpg', '$100-$500'],
            ['Painting', 'Interior and exterior painting', 'painting.jpg', '$200-$800'],
            ['Cleaning', 'Deep cleaning, move in/out cleaning', 'cleaning.jpg', '$100-$300'],
            ['Landscaping', 'Lawn care, gardening, hardscaping', 'landscaping.jpg', '$80-$250'],
            ['Appliance Repair', 'Fix refrigerators, washers, ovens', 'appliance.jpg', '$75-$200'],
            ['Roofing', 'Repair, replacement, maintenance', 'roofing.jpg', '$300-$1000'],
            ['Handyman', 'Multiple small repair tasks', 'handyman.jpg', '$60-$100']
        ];
        
        $stmt = $conn->prepare("INSERT INTO services (name, description, image, price_range) VALUES (?, ?, ?, ?)");
        
        foreach ($services as $service) {
            $stmt->execute($service);
        }
    }
}
}
?>