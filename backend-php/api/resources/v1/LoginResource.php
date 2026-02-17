<?php

require_once '../config/database.php';
require_once '../models/User.php';
require_once '../models/Token.php';

class LoginResource
{
    private $db;
    private $user;
    private $token;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
        $this->token = new Token($this->db);
    }

    /**
     * POST /api/v1/login
     * Solicitar: (email o username) y password
     * Responder: access_token y expires_at
     */
    public function login()
    {
        header("Content-Type: application/json");

        $data = json_decode(file_get_contents("php://input"));

        // Validar que se proporcione credencial de acceso y password
        if ((empty($data->email) && empty($data->username)) || empty($data->password)) {
            http_response_code(400);
            echo json_encode([
                "message" => "Email o username, y contraseña son requeridos"
            ]);
            return;
        }

        // Buscar usuario por email o username
        if (!empty($data->email)) {
            $found = $this->user->findByEmail($data->email);
        } else {
            $found = $this->user->findByUsername($data->username);
        }

        if (!$found) {
            http_response_code(401);
            echo json_encode([
                "message" => "Credenciales inválidas"
            ]);
            return;
        }

        // Validar contraseña
        if (!$this->user->verifyPassword($data->password)) {
            http_response_code(401);
            echo json_encode([
                "message" => "Credenciales inválidas"
            ]);
            return;
        }

        // Generar token (válido por 24 horas)
        $token_data = $this->token->generateToken($this->user->id, 86400); // 86400 = 24 horas

        if ($token_data) {
            http_response_code(200);
            echo json_encode($token_data);
        } else {
            http_response_code(503);
            echo json_encode([
                "message" => "Error al generar el token"
            ]);
        }
    }

    /**
     * POST /api/v1/logout
     * Revocar token
     */
    public function logout()
    {
        header("Content-Type: application/json");

        // Obtener el token del header
        $headers = getallheaders();
        $token = null;

        if (isset($headers['Authorization'])) {
            if (preg_match('/Bearer\s+(.+)/i', $headers['Authorization'], $matches)) {
                $token = trim($matches[1]);
            }
        }

        if (!$token) {
            http_response_code(400);
            echo json_encode([
                "message" => "Token no proporcionado"
            ]);
            return;
        }

        if ($this->token->revokeToken($token)) {
            http_response_code(200);
            echo json_encode([
                "message" => "Sesión cerrada correctamente"
            ]);
        } else {
            http_response_code(503);
            echo json_encode([
                "message" => "Error al cerrar sesión"
            ]);
        }
    }
}
?>
