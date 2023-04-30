<?php

require __DIR__ . '/../vendor/autoload.php';

header("Access-Control-Allow-Origin: *"); //! Only for development purposes, really bad practice to use '*' must specify who/where can access this resource/api
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");


$router = new \Bramus\Router\Router();

use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/../.env');


class Helper
{
    public static function setResponse(int $status, mixed $response): array
    {
        http_response_code($status);
        return ["response_code" => http_response_code(), "response_data" => $response];
    }

    public static function setQueryPlaceholderParameters(array $arr, string $placeholder): string
    {
        return implode(",", array_fill(0, count($arr), $placeholder));
    }

    public static function sanitizeString(string $input): string
    {
        $input = trim($input);
        $input = stripslashes($input);
        $input = htmlspecialchars($input);
        return $input;
    }

    public static function getRequestBody(): array
    {
        // // Get the content type of the request.
        // $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        // // If the request body is JSON, decode it and return the resulting array.
        // if (strpos($contentType, 'application/json') !== false) {
        //     $data = json_decode(file_get_contents('php://input'), true);
        // }
        // // Otherwise, return the request body as a form data array.
        // else {
        //     $data = $_POST;
        // }

        if (empty(json_decode(file_get_contents("php://input"), true))) {
            $data = $_POST;
        } else {
            $data = json_decode(file_get_contents("php://input"), true);
        }

        // If the resulting data is not an array, return an empty array.
        return is_array($data) ? $data : [];
    }

    public static function buildParameterPlaceholders(array $values, string $placeholder): string
    {
        return implode(',', array_fill(0, count($values), $placeholder));
    }

    private $DB_HOST;
    private $DB_PORT;
    private $DB_NAME;
    private $DB_USER;
    private $DB_PASS;

    private $pdo;

    public function __construct()
    {
        $this->DB_HOST = $_ENV['DB_HOST'];
        $this->DB_PORT = $_ENV['DB_PORT'];
        $this->DB_NAME = $_ENV['DB_NAME'];
        $this->DB_USER = $_ENV['DB_USER'];
        $this->DB_PASS = $_ENV['DB_PASS'];


        $this->pdo = new PDO(
            "mysql:host=" . $this->DB_HOST . ";port=" . $this->DB_PORT . ";dbname=" . $this->DB_NAME,
            $this->DB_USER,
            $this->DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_PERSISTENT => true
            ]
        );
    }
    public function connect()
    {
        return $this->pdo;
    }

    public function __destruct()
    {
        $this->pdo = null;
    }
}


$helper = new Helper;

$router->set404(function () {
    http_response_code(404);
});

$router->mount('/api', function () use ($helper, $router) {

    $router->get('/test', function () use ($helper) {
        echo json_encode($helper->setResponse(200, "OK"));
    });

    $router->post('/test_post', function () use ($helper) {
        echo json_encode($helper->setResponse(201, $helper->getRequestBody()));
    });

    $router->post('/submit_post', function () use ($helper) {
        $request_body = $helper->getRequestBody();

        $insert_keys = implode(",", array_keys($request_body));
        $execute_params = array_values($request_body);
        $query_placeholders = $helper->buildParameterPlaceholders(array_keys($request_body), "?");


        $query_insert_payload = "INSERT INTO submit_tbl($insert_keys) VALUES($query_placeholders)";
        $stmt_insert_payload = $helper->connect()->prepare($query_insert_payload);
        $stmt_insert_payload->execute($execute_params);


        echo json_encode($helper->setResponse(201, "Created"));
    });
});


$router->run();
