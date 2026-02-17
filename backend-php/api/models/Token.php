<?php
class Token
{
    private $conn;
    private $table_name = "api_tokens";

    public $id;
    public $user_id;
    public $token;
    public $expires_at;
    public $revoked;
    public $created_at;

    private $secret_key = "clave123456";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * Generar un token JWT simple
     */
    public function generateToken($user_id, $expires_in = 86400)
    {
        // Crear header
        $header = base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));

        // Crear payload
        $now = time();
        $expires_at = date('Y-m-d H:i:s', $now + $expires_in);
        
        $payload = base64_encode(json_encode([
            'user_id' => $user_id,
            'iat' => $now,
            'exp' => $now + $expires_in
        ]));

        // Crear signature
        $signature = base64_encode(hash_hmac('sha256', "$header.$payload", $this->secret_key, true));

        // Generar token
        $token = "$header.$payload.$signature";

        // Guardar en BD
        $this->user_id = $user_id;
        $this->token = $token;
        $this->expires_at = $expires_at;
        $this->revoked = false;

        if ($this->save()) {
            return [
                'access_token' => $token,
                'expires_at' => $expires_at,
                'expires_in' => $expires_in
            ];
        }
        return false;
    }

    /**
     * Guardar token en la BD
     */
    public function save()
    {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET user_id=:user_id, token=:token, expires_at=:expires_at, revoked=:revoked";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":token", $this->token);
        $stmt->bindParam(":expires_at", $this->expires_at);
        $stmt->bindParam(":revoked", $this->revoked, PDO::PARAM_BOOL);

        return $stmt->execute();
    }

    /**
     * Validar token
     */
    public function validateToken($token)
    {
        // Verificar que el token existe en BD, no ha expirado y no fue revocado
        $query = "SELECT user_id FROM " . $this->table_name . " 
                  WHERE token = :token 
                  AND expires_at > NOW() 
                  AND revoked = FALSE
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":token", $token);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Validar estructura del JWT
            if ($this->verifySignature($token)) {
                return $row['user_id'];
            }
        }

        return false;
    }

    /**
     * Verificar la firma del JWT
     */
    private function verifySignature($token)
    {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            return false;
        }

        $header = $parts[0];
        $payload = $parts[1];
        $signature = $parts[2];

        // Recalcular la firma
        $expected_signature = base64_encode(hash_hmac('sha256', "$header.$payload", $this->secret_key, true));

        return $signature === $expected_signature;
    }

    /**
     * Revocar token (logout)
     */
    public function revokeToken($token)
    {
        $query = "UPDATE " . $this->table_name . " 
                  SET revoked = TRUE 
                  WHERE token = :token";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":token", $token);

        return $stmt->execute();
    }

    /**
     * Obtener payload del token
     */
    public function getPayload($token)
    {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            return false;
        }

        $payload = json_decode(base64_decode($parts[1]), true);
        
        // Verificar que el token no ha expirado
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return false;
        }

        return $payload;
    }
}
?>
