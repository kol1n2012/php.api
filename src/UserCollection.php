<?php

namespace App;

class UserCollection extends CollectionFile
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

        //TODO order collection
        if ($order = @$data['order']) {
            $this->setOrder($order);
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
        return (string)json_encode($this->getCollection(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param array $user
     * @return User
     */
    protected function __convert(array $user = []): User
    {
        return new User($user['id'], $user['name'], $user['email']);
    }
}