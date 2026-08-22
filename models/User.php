<?php
class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $first_name;
    public $last_name;
    public $email;
    public $password;
    public $phone;
    public $address;
    public $profile_photo;
    public $subcity;
    public $woreda;
    public $role;
    public $profession;
    public $certification_file;
    public $residence_id_file;
    public $bank_account;
    public $tele_birr;
    public $status;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function register() {
        $query = "INSERT INTO " . $this->table_name . " 
                 SET first_name=:first_name, last_name=:last_name, email=:email, 
                 password=:password, phone=:phone, address=:address, profile_photo=:profile_photo,
                 subcity=:subcity, woreda=:woreda, role=:role, profession=:profession, 
                 certification_file=:certification_file, residence_id_file=:residence_id_file,
                 bank_account=:bank_account, tele_birr=:tele_birr, status=:status";
        
        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->subcity = htmlspecialchars(strip_tags($this->subcity));
        $this->woreda = htmlspecialchars(strip_tags($this->woreda));
        $this->profession = htmlspecialchars(strip_tags($this->profession));
        $this->bank_account = htmlspecialchars(strip_tags($this->bank_account));
        $this->tele_birr = htmlspecialchars(strip_tags($this->tele_birr));

        // Set status based on role
        $this->status = ($this->role == 'technician') ? 'pending' : 'approved';

        // Bind values
        $stmt->bindParam(":first_name", $this->first_name);
        $stmt->bindParam(":last_name", $this->last_name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":profile_photo", $this->profile_photo);
        $stmt->bindParam(":subcity", $this->subcity);
        $stmt->bindParam(":woreda", $this->woreda);
        $stmt->bindParam(":role", $this->role);
        $stmt->bindParam(":profession", $this->profession);
        $stmt->bindParam(":certification_file", $this->certification_file);
        $stmt->bindParam(":residence_id_file", $this->residence_id_file);
        $stmt->bindParam(":bank_account", $this->bank_account);
        $stmt->bindParam(":tele_birr", $this->tele_birr);
        $stmt->bindParam(":status", $this->status);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function login() {
        $query = "SELECT id, first_name, last_name, email, password, role, status, profile_photo 
                 FROM " . $this->table_name . " 
                 WHERE email = ? LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->email);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Check password directly (not hashed as requested)
            if($this->password === $row['password']) {
                $this->id = $row['id'];
                $this->first_name = $row['first_name'];
                $this->last_name = $row['last_name'];
                $this->role = $row['role'];
                $this->status = $row['status'];
                $this->profile_photo = $row['profile_photo'];
                return ['success' => true];
            } else {
                return ['success' => false, 'message' => 'Invalid password.'];
            }
        }
        
        return ['success' => false, 'message' => 'Email not found.'];
    }

    public function emailExists() {
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->email);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}
?>