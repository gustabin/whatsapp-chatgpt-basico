<?php
require __DIR__ . '/../../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../..');
$dotenv->load();
// $databaseUrl = $_ENV['DATABASE_URL'];
// $components = parse_url($databaseUrl);
// $dbName = ltrim($components['path'], '/');

$dbDatabase = $_ENV['DB_DATABASE'];
$dbHost = $_ENV['DB_HOST'];
$dbUser = $_ENV['DB_USER'];
$dbPass = $_ENV['DB_PASS'];

// Crear la conexión
$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbDatabase);
// $conn = new mysqli($components['host'], $components['user'], $components['pass'], $dbName);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
// Establecer el conjunto de caracteres UTF-8
mysqli_set_charset($conn, "utf8");
