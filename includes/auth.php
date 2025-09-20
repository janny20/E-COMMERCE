<?php
class Auth {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    // Register new user
    public function register($username, $email, $password, $user_type = 'customer', $additional_data = []) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        try {
            $this->conn->beginTransaction();
            // Insert user
            $query = "INSERT INTO users (username, email, password, user_type) VALUES (:username, :email, :password, :user_type)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":username", $username);
            $stmt->bindParam(":email", $email);
            $stmt->bindParam(":password", $hashed_password);
            $stmt->bindParam(":user_type", $user_type);
            if($stmt->execute()) {
                $user_id = $this->conn->lastInsertId();
                // Create user profile
                $query = "INSERT INTO user_profiles (user_id) VALUES (:user_id)";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":user_id", $user_id);
                $stmt->execute();
                // If vendor, create vendor profile
                if($user_type == 'vendor') {
                    $business_name = $additional_data['business_name'] ?? '';
                    $query = "INSERT INTO vendors (user_id, business_name) VALUES (:user_id, :business_name)";
                    $stmt = $this->conn->prepare($query);
                    $stmt->bindParam(":user_id", $user_id);
                    $stmt->bindParam(":business_name", $business_name);
                    $stmt->execute();
                }
                $this->conn->commit();
                return true;
            }
        } catch(PDOException $e) {
            $this->conn->rollBack();
            error_log("Registration error: " . $e->getMessage());
            echo "Registration error: " . $e->getMessage(); // Show error for debugging
        }
        return false;
    }
    
    // Login user
    public function login($email, $password) {
        $query = "SELECT u.id, u.username, u.password, u.user_type, up.avatar, up.first_name 
                  FROM users u
                  LEFT JOIN user_profiles up ON u.id = up.user_id
                  WHERE u.email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        
        if($stmt->rowCount() == 1) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if(password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                // Use first name for display if available, otherwise username
                $_SESSION['username'] = !empty($row['first_name']) ? $row['first_name'] : $row['username'];
                $_SESSION['user_type'] = $row['user_type'];
                $_SESSION['avatar'] = $row['avatar']; // Can be null
                return true;
            }
        }
        return false;
    }
    
    // Check if user is logged in
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    // Redirect user
    public function redirect($url) {
        header("Location: $url");
        exit();
    }
    
    // Logout user
    public function logout() {
        session_destroy();
        unset($_SESSION['user_id']);
        unset($_SESSION['username']);
        unset($_SESSION['user_type']);
        return true;
    }
    
    // Check if user is admin
    public function isAdmin() {
        return isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin';
    }
    
    // Check if user is vendor
    public function isVendor() {
        return isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'vendor';
    }
    
    // Check if user is customer
    public function isCustomer() {
        return isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'customer';
    }
    
    // Get user data
    public function getUserData($user_id) {
        $query = "SELECT u.*, up.first_name, up.last_name, up.phone, up.address, up.city, up.state, up.country, up.zip_code, up.avatar 
                  FROM users u 
                  LEFT JOIN user_profiles up ON u.id = up.user_id 
                  WHERE u.id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        
        if($stmt->rowCount() == 1) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return false;
    }
    
    // Add getters for last logged-in user
    public function getUserId() {
        return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    }
    public function getUsername() {
        return isset($_SESSION['username']) ? $_SESSION['username'] : null;
    }
    public function getUserType() {
        return isset($_SESSION['user_type']) ? $_SESSION['user_type'] : null;
    }
}
?>