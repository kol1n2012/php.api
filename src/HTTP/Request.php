<?php

namespace App\HTTP;

final class Request
{
    /**
     * @var Request
     */
    protected static $_instance;

    /**
     * @var array
     */
    private array $data;

    /**
     * @var string
     */
    private string $method = "";

    /**
     * @var string
     */
    private string $token = '';

    /**
     * @var string
     */
    private string $login = '';

    /**
     * @var string
     */
    private string $password = '';

    private function __construct()
    {
        $this->setMethod();

        if (@preg_match('/Bearer (.+)/', getallheaders()['Authorization'], $matches)) {
            if (@$matches[1]) $token = $matches[1];
        }

        $token = $token ?? '';
        $login = $_SERVER['PHP_AUTH_USER'] ?? '';
        $password = $_SERVER['PHP_AUTH_PW'] ?? '';
        $data = $_REQUEST ?? [];

        $this->setData($data);
        $this->setToken($token);
        $this->setLogin($login);
        $this->setPassword($password);
    }

    /**
     * @return Request
     */
    public static function getInstance(): Request
    {
        if (self::$_instance === null) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    /**
     * @return void
     */
    private function setMethod(): void
    {
        $this->method = $_SERVER['REQUEST_METHOD'];
    }

    /**
     * @param array $data
     * @return void
     */
    private function setData(array $data = []): void
    {
        foreach ($data as $key => $value) {
            $this->data[$key] = trim(htmlspecialchars($value, ENT_QUOTES));
        }
    }

    /**
     * @return bool
     */
    public function isToken(): bool
    {
        return mb_strlen($this->getToken()) ?? false;
    }


    public function checkToken(): bool
    {
        if (!$this->isToken()) return false;

        @session_start();

        $id = @session_id();

        $token = $this->getToken();

        $file = $_SERVER['DOCUMENT_ROOT'] . '/tmp/token/' . $id;

        if (!file_exists($file)) return false;

        if (file_get_contents($file) !== $token) return false;

        $file_datetime = \DateTimeImmutable::createFromFormat('U', (string)filemtime($file))->setTimezone(new \DateTimeZone('Europe/Moscow'));

        $date = new \DateTime();

        $date->modify('- 15 minutes');

        if ($file_datetime < $date) return false;

        $this->saveToken();

        return true;
    }

    /**
     * @param string $login
     * @return void
     */
    private function setLogin(string $login = ''): void
    {
        if (!mb_strlen($login)) return;

        $this->login = $login;
    }

    /**
     * @param string $password
     * @return void
     */
    private function setPassword(string $password = ''): void
    {
        if (!mb_strlen($password)) return;

        $this->password = $password;
    }

    /**
     * @param string $token
     * @return void
     */
    private function setToken(string $token = ''): void
    {
        $this->token = $token;
    }

    /**
     * @return string
     */
    public function saveToken(): string
    {
        @session_start();

        $api_token = getenv('API_TOKEN');

        $id = @session_id();

        $encoded_header = base64_encode('{"alg": "HS256","typ": "JWT"}');
        $encoded_payload = base64_encode('{"score": "12","name": "Crille"}');

        $header_and_payload_combined = $encoded_header . '.' . $encoded_payload;

        $secret_key = $api_token . $id;

        $signature = base64_encode(hash_hmac('sha256', $header_and_payload_combined, $secret_key, true));

        $token = $header_and_payload_combined . '.' . $signature;

        $filepath = $_SERVER['DOCUMENT_ROOT'] . '/tmp/token/' . $id;

        file_put_contents($filepath, $token);

        $this->setToken($token);

        return $token;
    }


    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method ?? "";
    }

    /**
     * @return string
     */
    private function getToken(): string
    {
        return $this->token ?? '';
    }

    /**
     * @return string
     */
    public function getLogin(): string
    {
        return $this->login ?? "";
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password ?? "";
    }

    /**
     * @param string $name
     * @return mixed|void
     */
    public function __get(string $name = '')
    {
        if (!mb_strlen($name)) return;
        if (!isset($this->data[$name])) return;

        return @$this->data[$name];
    }

    /**
     * @return void
     */
    private function __clone(): void
    {
    }

    /**
     * @return void
     */
    public function __wakeup(): void
    {
    }

    public function __destruct()
    {
        $this->data = [];
    }
}
