<?php

namespace App\Server;

use App\HTTP\Request;
use App\HTTP\Response;
use JetBrains\PhpStorm\NoReturn;
use App\Server\Api\Route;
use App\Server\Api\Methods as ApiMethods;

class Api
{

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

    public function __construct()
    {
        $this->setUrl();
    }

    /**
     * @param bool $isGetToken
     * @return void
     */
    private function checkAuth(bool $isGetToken = false): void
    {
        $request = Request::getInstance();

        if ($request->isToken()) {
            if (!$request->checkToken()) {
                $this->setError('Не действительный токен');
            }

            $this->setStatus(true);
        } else {

            $login = $request->getLogin();

            $password = $request->getPassword();

            if (!mb_strlen($login) && !mb_strlen($password)) $this->setError('Логин и пароль пустой');

            if (!mb_strlen($login)) $this->setError('Логин пустой');

            if ($login !== getenv('API_LOGIN')) $this->setError('Логин не верный');

            if (!mb_strlen($password)) $this->setError('Пароль пустой');

            if ($password !== getenv('API_PASSWORD')) $this->setError('Пароль не верный');

            if (!$isGetToken) {
                $this->setError('Требуется токен для получения доступа');
            }
        }

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

    use ApiMethods;

    /**
     * @return void
     */
    public function getToken(): void
    {
        $this->checkAuth(true);

        $request = Request::getInstance();

        $token = $request->saveToken();

        $this->setStatus(true);
        $this->setMessage('Успешно');

        $this->__response(json_encode(['token' => $token], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    function __destruct()
    {
        exit;
    }
}