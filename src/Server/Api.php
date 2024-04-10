<?php

namespace App\Server;

use App\HTTP\Response;
use JetBrains\PhpStorm\NoReturn;
use App\Server\Api\Route;
use App\Server\Api\Methods as ApiMethods;

class Api
{
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

    /**
     * @var string
     */
    private string $message = '';

    /**
     * @var bool
     */
    private bool $status = false;

    /**
     * @var array
     */
    private array $url = [];

    /**
     * @param string $login
     * @param string $password
     */
    public function __construct(string $login = '', string $password = '')
    {
        $this->setUrl();

        $this->setLogin($login);

        $this->setPassword($password);
    }

    /**
     * @return void
     */
    private function checkAuth(): void
    {
        $login = $this->getLogin();
        $password = $this->getPassword();

        if (!mb_strlen($login) && !mb_strlen($password)) $this->setError('Логин и пароль пустой');

        if (!mb_strlen($login)) $this->setError('Логин пустой');

        if ($login !== getenv('API_LOGIN')) $this->setError('Логин не верный');

        $this->setLogin($login);

        if (!mb_strlen($password)) $this->setError('Пароль пустой');

        if ($password !== getenv('API_PASSWORD')) $this->setError('Пароль не верный');

        $this->setToken();
    }

    /**
     * @return void
     */
    private function setUrl(): void
    {
        $path = parse_url($_SERVER['REQUEST_URI'])['path'];

        if (!mb_strlen($path)) return;

        $path = explode('/', $path);
        $path = array_filter($path, fn($p) => !in_array($p, ['api', '']));

        if (!count($path)) return;

        $path = array_values($path);

        if (!count($path)) return;

        $this->url = $path ?? [];
    }

    /**
     * @param string $output
     * @return void
     */
    private function __response(string $output = '[]'): void
    {
        $response = Response::getInstance();

        $response->setHeaders(['Content-Type' => 'application/json; charset=utf-8']);
        $response->setBody($output);
        $response->setStatus($this->getStatus());
        $response->setMessage($this->getMessage());
    }

    /**
     * @return bool
     */
    private function isToken(): bool
    {
        return mb_strlen($this->token) ?? false;
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
     * @return void
     */
    private function setToken(): void
    {
        $this->token = md5(getenv('API_TOKEN'));

        $this->setStatus(true);
    }

    /**
     * @param bool $status
     * @return void
     */
    private function setStatus(bool $status = false): void
    {
        $this->status = $status;
    }

    /**
     * @param array $routingList
     * @return void
     */
    public function routing(array $routingList = []): void
    {
        $url = $this->getUrl();

        $__method = @array_shift($url) ?? "";

        $__route = false;

        $data = [];

        foreach ($routingList as $method => $route) {

            if (false === $route instanceof Route) continue;

            if (str_contains($method, $__method)) {

                $__route = $route;

                $__additional = str_replace(sprintf("/%s", $__method), "", $method);
                $__additional = array_values(array_filter(@explode("/", $__additional) ?? []));

                if (count($__additional) && count($__additional) === count($url)) {
                    foreach ($__additional as $key => $item) {
                        $item = str_replace("%", "", $item);
                        if (in_array($item, ['id'])) {
                            $data[$item] = (int)$url[$key];
                        } else {
                            $data[$item] = $url[$key];
                        }
                    }
                }
            }
        }

        if ($__route) {
            if ($__route->isAuth()) $this->checkAuth();

            try {
                $data = array_merge($data, $__route->getData());

                if (count($data)) {
                    $this->{$__method}($data);
                } else {
                    $this->{$__method}();
                }

            } catch (\Throwable $e) {
                if (filter_var(@getenv('DEBUG'), FILTER_VALIDATE_BOOLEAN)) {
                    echo '<pre>';
                    var_dump($e);
                    echo '</pre>';
                }

                $this->setError('метод в api не найден', 418);
            }
        } else {
            $this->setError('Не корректно указан метод api', 418);
        }
    }

    /**
     * @param string $message
     * @return void
     */
    public function setMessage(string $message = ''): void
    {
        $this->message = $message;
    }

    /**
     * @param string $message
     * @param int $http_code
     * @return void
     */
    #[NoReturn] private function setError(string $message = '', int $http_code = 401): void
    {
        $response = Response::getInstance($http_code);

        $response->setHeaders(['Content-Type' => 'application/json; charset=utf-8']);
        $response->setMessage($message);

        die();
    }

    /**
     * @return array
     */
    private function getUrl(): array
    {
        return $this->url ?? [];
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token ?? '';
    }

    /**
     * @return bool
     */
    public function getStatus(): bool
    {
        return $this->status ?? false;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message ?? '';
    }

    /**
     * @return string
     */
    private function getLogin(): string
    {
        return $this->login ?? "";
    }

    /**
     * @return string
     */
    private function getPassword(): string
    {
        return $this->password ?? "";
    }


    use ApiMethods;
}