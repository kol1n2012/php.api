<?php

namespace App\Model;

use App\Entities\User;
use App\Model\Sourse\File as FromFile;


class Users extends FromFile
{
    /**
     * @var array
     */
    protected array $filter = [];

    /**
     * @var array
     */
    protected array $sort = [];

    /**
     * @var array
     */
    protected array $order = [];

    /**
     * @var array
     */
    protected array $collection = [];

    /**
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->setCollection('users.json');

        if ($filter = @$data['filter']) {
            $this->setFilter($filter);
        }

        //TODO select collection
        if ($select = @$data['select']) {
            $this->setSelect($select);
        }

        //TODO sort collection
        if ($sort = @$data['sort']) {
            $this->setSort($sort);
        }

    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $collection = $this->getCollection();

        $collection = implode(',', $collection);

        return "[$collection]";
    }

    /**
     * @param array $user
     * @return User
     */
    protected function __convert(array $user = []): User
    {
        return new User($user['id'], $user['name'], $user['email']);
    }

    /**
     * @param string $email
     * @return bool
     */
    public function checkEmail(string $email = ''): bool
    {
        $mailList = array_map(fn($user) => $user->getEmail(), $this->getCollection());

        if (in_array($email, $mailList)) $return = true;

        return $return ?? false;
    }

    /**
     * @param User $user
     * @return void
     */
    public function add(User $user = new User): void
    {
        $this->collection[] = $user;

        $this->__save();
    }

    public function delete(User $user = new User): void
    {
        $this->setFilter(['!id' => $user->getId()]);

        $this->__save();
    }
}