<?php

class AuthMiddleware
{
    private $db;
    private $token;

    public function __construct($db)
    {
        $this->db = $db;
        require_once '../models/Token.php';
        $this->token = new Token($db);
    }

    /**
     * Obtener token del header Authorization
     */
    public function getTokenFromHeader()
    {
        $headers = getallheaders();

        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
            
            // Formato: Bearer {token}
            if (preg_match('/Bearer\s+(.+)/i', $authHeader, $matches)) {
                return trim($matches[1]);
            }
        }

        return null;
    }

    /**
     * Validar que el token sea válido
     * Retorna el user_id si es válido, false si no
     */
    public function validateRequest()
    {
        $token = $this->getTokenFromHeader();

        if (!$token) {
            http_response_code(401);
            echo json_encode(['message' => 'Token no proporcionado']);
            exit;
        }

        $user_id = $this->token->validateToken($token);

        if (!$user_id) {
            http_response_code(401);
            echo json_encode(['message' => 'Token inválido o expirado']);
            exit;
        }

        return $user_id;
    }

    /**
     * Validation request sin salir (solo devuelve resultado)
     */
    public function validate()
    {
        $token = $this->getTokenFromHeader();

        if (!$token) {
            return false;
        }

        return $this->token->validateToken($token);
    }
}
?>
