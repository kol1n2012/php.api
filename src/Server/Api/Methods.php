<?php

namespace App\Server\Api;

use App\HTTP\Request;
use App\User;
use App\UserCollection;

trait Methods
{

    /**
     * @param array $data
     * @return void
     */
    private function getUsers(array $data = []): void
    {
        switch ((Request::getInstance())->getMethod()) {
            case 'GET':
                $users = new UserCollection();

                $this->setStatus(true);
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
        switch ((Request::getInstance())->getMethod()) {
            case 'GET':

                if (!@count($data)) $this->setError('ожидается корректно заполненные поля id', 415);

                if (!@$data['id']) $this->setError('ожидается корректно заполненные поля id', 415);

                $id = $data['id'];

                $users = new UserCollection(['filter' => ['id' => $id]]);

                if (count($users = $users->getCollection())) {
                    $user = array_shift($users);

                    $this->setStatus(true);
                    $this->setMessage('Успешно');
                    $this->__response("$user");
                } else {
                    $this->setError('Пользователь с таким id не существует', 415);
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
        switch ((Request::getInstance())->getMethod()) {
            case 'POST':
                $errorMessage = 'ожидается корректно заполненные поля name, email';

                if (!@count($data)) $this->setError($errorMessage, 415);

                if (!@mb_strlen($data['name'])) $this->setError($errorMessage, 415);

                if (!@mb_strlen($data['email'])) $this->setError($errorMessage, 415);

                $name = $data['name'];

                $email = $data['email'];

                $users = new UserCollection();

                if ($users->checkEmail($email)) $this->setError('Пользователь с таким email уже существует', 415);

                $user = new User(0, $name, $email);

                $users->add($user);

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
        switch ((Request::getInstance())->getMethod()) {
            case 'DELETE':
                $errorMessage = 'ожидается корректно заполненные поля id';
                if (!@count($data)) $this->setError($errorMessage, 415);

                if (!@$data['id']) $this->setError($errorMessage, 415);

                $id = $data['id'];

                $users = new UserCollection(['filter' => ['id' => $id]]);

                if (count($users = $users->getCollection())) {
                    $user = array_shift($users);

                    (new UserCollection())->delete($user);

                    $this->setMessage('Успешно');
                    $this->__response("$user");
                } else {
                    $this->setError('Пользователь с таким id не существует', 415);
                }
                break;
            default:
                $this->setError('Не корректно указан HTTP-метод api', 405);
                break;
        }
    }

}