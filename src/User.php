<?php

namespace App;

class User
{
    /**
     * @var int
     */
    private int $id = 0;

    /**
     * @var string
     */
    private string $name = '';

    /**
     * @var string
     */
    private string $email = '';

    /**
     * @param string $name
     * @param string $email
     * @param int $id
     */
    public function __construct(int $id = 0, string $name = '', string $email = '')
    {
        $this->setId($id);
        $this->setName($name);
        $this->setEmail($email);
    }

    /**
     * @param string $name
     * @return void
     */
    private function setName(string $name = ''): void
    {
        if (!mb_strlen($name)) return;

        $this->name = $name;
    }

    /**
     * @return void
     */
    private function setEmail(string $email = ''): void
    {
        if (!mb_strlen($email)) return;

        $this->email = $email;
    }

    /**
     * @param int $id
     * @return void
     */
    private function setId(int $id = 0): void
    {
        if (!$id) {
            $users = @json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/users.json'), true) ?? [];

            $id = (int)@end($users)['id'] ?? 0;

            $id++;
        }

        $this->id = $id;
    }

    /**
     * @return array
     */
    public function getValidData(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'email' => $this->getEmail(),
        ];
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id ?? 0;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name ?? '';
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email ?? '';
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string)json_encode($this->getValidData(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public function delete()
    {
        (new UserCollection(['filter' => ['!id' => $this->getId()]]))->__save();
    }
}