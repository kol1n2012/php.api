<?php

namespace App\Model;

use App\Entities\User;

class Users extends Collection
{
    /**
     * @param array $query
     */
    public function __construct(array $query = [])
    {
        $this->setEntity('users');

        $sourse = getenv('DATA_SOURSE_DRIVER') ?? 'file';

        $this->setSourse($sourse);

        parent::__construct($query);
    }

    /**
     * @param string $email
     * @return bool
     */
    public function checkEmail(string $email = ''): bool
    {
        $mailList = array_map(fn($user) => $user->getEmail(), $this->getStorage()->getCollection());

        if (in_array($email, $mailList)) $return = true;

        return $return ?? false;
    }

    /**
     * @param array $data
     * @return User
     */
    public static function convert(array $data = []): User
    {
        $user = new User;
        $user->convert($data);

        return $user;
    }
}