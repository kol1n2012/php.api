<?php

namespace App\Server\Api;

use App\User;

trait Methods
{

    /**
     * @param array $data
     * @return void
     */
    private function getUsers(array $data = []): void
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
     * @param array $data
     * @return void
     */
    private function getUser(array $data = []): void
    {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':

                if (!@count($data)) $this->setError('ожидается корректно заполненные поля id', 415);

                if (!@$data['id']) $this->setError('ожидается корректно заполненные поля id', 415);

                $id = $data['id'];

                $users = @json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/users.json'), true) ?? [];

                if (!in_array($id, array_column($users, 'id'))) $this->setError('Пользователь с таким id не существует', 415);

                $users = array_filter($users, function ($user) use($id){
                    return $user['id'] === $id;
                });

                if($user = array_shift($users)){
                    $user = new User($user['id'],$user['name'],$user['email']);

                    $this->setMessage('Успешно');
                    $this->__response("$user");
                }else{
                    $this->setError('Пользователь не найден', 405);
                }

                break;
            default:
                $this->setError('Не корректно указан HTTP-метод api', 405);
                break;
        }
    }

    /**
     * @param array $data
     * @return void
     */
    private function addUser(array $data = []): void
    {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'POST':
                $errorMessage = 'ожидается корректно заполненные поля name, email';

                if (!@count($data)) $this->setError($errorMessage, 415);

                if (!@mb_strlen($data['name'])) $this->setError($errorMessage, 415);

                if (!@mb_strlen($data['email'])) $this->setError($errorMessage, 415);

                $name = $data['name'];

                $email = $data['email'];

                $users = @json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/users.json'), true) ?? [];

                if (in_array($email, array_column($users, 'email'))) $this->setError('Пользователь с таким email уже существует', 415);

                $user = new User(0, $name, $email);

                $users[] = $user->getValidData();

                file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/users.json', json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

                $this->setMessage('Успешно');
                $this->__response("$user");
                break;
            default:
                $this->setError('Не корректно указан HTTP-метод api', 405);
                break;
        }
    }

    /**
     * @param array $data
     * @return void
     */
    private function deleteUser(array $data = []): void
    {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'DELETE':
                $errorMessage = 'ожидается корректно заполненные поля id';
                if (!@count($data)) $this->setError($errorMessage, 415);

                if (!@$data['id']) $this->setError($errorMessage, 415);

                $id = $data['id'];

                $users = @json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/users.json'), true) ?? [];

                if (!in_array($id, array_column($users, 'id'))) $this->setError('Пользователь с таким id не существует', 415);

                $users = array_filter($users, function ($user) use($id){
                    return $user['id'] !== $id;
                });

                file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/users.json', json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

                $this->setMessage('Успешно');
                $this->__response([]);
                break;
            default:
                $this->setError('Не корректно указан HTTP-метод api', 405);
                break;
        }
    }

}