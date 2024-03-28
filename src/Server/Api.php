<?php

namespace App\Server;

use JetBrains\PhpStorm\NoReturn;

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
     * @param string $login
     * @param string $password
     */
    public function __construct(string $login = '', string $password = '')
    {
        if (!mb_strlen($login) && !mb_strlen($password)) $this->setError('Логин и пароль пустой');

        if (!mb_strlen($login)) $this->setError('Логин пустой');

        if ($login !== getenv('API_LOGIN')) $this->setError('Логин не верный');

        $this->setLogin($login);

        if (!mb_strlen($password)) $this->setError('Пароль пустой');

        if ($password !== getenv('API_PASSWORD')) $this->setError('Пароль не верный');

        $this->setPassword($password);

        $this->setToken();
    }

    /**
     * @param bool|array $result
     * @return void
     */
    private function __response(bool|array $result = false): void
    {
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode(['status' => $this->getStatus(), 'message' => $this->getMessage(), 'result' => $result], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
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
     * @param array $methodList
     * @return void
     */
    public function setMethod(array $methodList = []): void
    {
        if (!count($methodList)) $this->setError('Не корректно указан метод api', 418);

        $__method = '';

        foreach ($methodList as $key => $method) {
            $__method .= $key ? ucfirst($method) : lcfirst($method);
        }

        try {
            $this->{$__method}();
        } catch (\Throwable $e) {
            $this->setError('Не корректно указано название метода api', 418);
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
        http_response_code($http_code);
        $this->setMessage($message);
        $this->setStatus(false);
        $this->__response();
        die();
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
     * @return void
     */
    private function syncUsers(): void
    {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $users = @json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/users.json'), true) ?? [];
                $this->setMessage('Успешно');
                $this->__response($users);
                break;
            default:
                $this->setError('Не корректно указан HTTP-метод api', 405);
                break;
        }
    }

    /**
     * @return void
     */
    private function uploadUsers(): void
    {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'POST':
                if (!@count($_REQUEST)) $this->setError('ожидается корректно заполненные поля userName, userEmail', 415);

                if (!@mb_strlen($_REQUEST['userName'])) $this->setError('ожидается корректно заполненные поля userName, userEmail', 415);

                if (!@mb_strlen($_REQUEST['userEmail'])) $this->setError('ожидается корректно заполненные поля userName, userEmail', 415);

                $fileName = $_SERVER['DOCUMENT_ROOT'] . '/users.json';

                $users = @json_decode(file_get_contents($fileName), true) ?? [];
                $userId = (int)end($users)['user_id'] ?? 0;
                $userId++;

                $newUser = [
                    'user_id' => $userId,
                    'userName' => $_REQUEST['userName'],
                    'userEmail' => $_REQUEST['userEmail'],
                ];

                $users[] = $newUser;

                file_put_contents($fileName, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

                $this->setMessage('Успешно');
                $this->__response($newUser);
                break;
            default:
                $this->setError('Не корректно указан HTTP-метод api', 405);
                break;
        }
    }
}