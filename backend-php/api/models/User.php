<?php
class User
{
    private $conn;
    private $table_name = "api_users";

    public $id;
    public $username;
    public $email;
    public $password_hash;
    public $status;
    public $created_at;
    public $updated_at;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET username=:username, email=:email, password_hash=:password_hash, status=:status";

        $stmt = $this->conn->prepare($query);

        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->status = 'ACTIVE';
        
        // Hash de la contraseña (MD5 para prueba)
        $hashed_password = md5($this->password_hash);

        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password_hash", $hashed_password);
        $stmt->bindParam(":status", $this->status);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function read()
    {
        $query = "SELECT id, username, email, status, created_at, updated_at 
                  FROM " . $this->table_name . " 
                  WHERE status = 'ACTIVE'
                  ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readOne()
    {
        $query = "SELECT id, username, email, status, created_at, updated_at 
                  FROM " . $this->table_name . " 
                  WHERE id = :id 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->username = $row['username'];
            $this->email = $row['email'];
            $this->status = $row['status'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        return false;
    }

    /**
     * Buscar usuario por email para validar credenciales
     */
    public function findByEmail($email)
    {
        $query = "SELECT id, username, email, password_hash, status, created_at, updated_at 
                  FROM " . $this->table_name . " 
                  WHERE LOWER(TRIM(email)) = LOWER(TRIM(:email))
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->id = $row['id'];
            $this->username = $row['username'];
            $this->email = $row['email'];
            $this->password_hash = $row['password_hash'];
            $this->status = $row['status'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        return false;
    }

    /**
     * Buscar usuario por username para validar credenciales
     */
    public function findByUsername($username)
    {
        $query = "SELECT id, username, email, password_hash, status, created_at, updated_at 
                  FROM " . $this->table_name . " 
                  WHERE username = :username AND status = 'ACTIVE'
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->id = $row['id'];
            $this->username = $row['username'];
            $this->email = $row['email'];
            $this->password_hash = $row['password_hash'];
            $this->status = $row['status'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        return false;
    }

    /**
     * Validar la contraseña contra el hash almacenado (MD5 para prueba)
     */
    public function verifyPassword($plain_password)
    {
        return md5($plain_password) === $this->password_hash;
    }

    public function update()
    {
        $query = "UPDATE " . $this->table_name . " 
                  SET username = :username, email = :email 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
    public function delete()
    {
        // Soft delete - cambiar status a INACTIVE
        $query = "UPDATE " . $this->table_name . " WHERE id = :id SET status = 'INACTIVE'";

        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>