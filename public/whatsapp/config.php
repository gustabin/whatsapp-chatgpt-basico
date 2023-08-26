
<?php
require __DIR__ . '/../../vendor/autoload.php';

class Config
{
    public $apiKeyChatgpt;
    public $tokenWa;
    public $telefonoIDWa;

    public function __construct()
    {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../..');
        $dotenv->load();

        $this->apiKeyChatgpt = $_ENV['API_KEY_CHATGPT'];
        $this->tokenWa = $_ENV['TOKEN_WA'];
        $this->telefonoIDWa = $_ENV['TELEFONO_ID_WA'];
    }
}
