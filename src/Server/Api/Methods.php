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
    private function addUser(array $data = []): void
    {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'POST':
                if (!@count($data)) $this->setError('ожидается корректно заполненные поля userName, userEmail', 415);

                if (!@mb_strlen($data['userName'])) $this->setError('ожидается корректно заполненные поля userName, userEmail', 415);

                if (!@mb_strlen($data['userEmail'])) $this->setError('ожидается корректно заполненные поля userName, userEmail', 415);

                $name = $data['userName'];

                $email = $data['userEmail'];

                $users = @json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/users.json'), true) ?? [];

                if (in_array($email, array_column($users, 'userEmail'))) $this->setError('Пользователь с таким email уже существует', 415);

                $newUser = new User($name, $email);
                $newUser = $newUser->getValidData();

                $users[] = $newUser;

                file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/users.json', json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

                $this->setMessage('Успешно');
                $this->__response($newUser);
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
                if (!@count($data)) $this->setError('ожидается корректно заполненные поля user_id', 415);

                if (!@$data['user_id']) $this->setError('ожидается корректно заполненные поля user_id', 415);

                $id = $data['user_id'];

                $users = @json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/users.json'), true) ?? [];

                if (!in_array($id, array_column($users, 'user_id'))) $this->setError('Пользователь с таким user_id не существует', 415);

                $users = array_filter($users, function ($user) use($id){
                    return $user['user_id'] !== $id;
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