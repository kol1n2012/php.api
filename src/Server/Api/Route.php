<?php

namespace App\Server\Api;

class Route
{

    /**
     * @var array|string|string[]
     */
    private array|string $methods = [];

    /**
     * @var bool
     */
    private bool $auth = false;

    /**
     * @var array
     */
    private array $data = [];

    public function __construct(array|string $methods = [], bool $auth = false, array $data = [])
    {
        $this->setMethods($methods);
        $this->setAuth($auth);
        $this->setData($data);
    }

    /**
     * @param array|string $methods
     * @return void
     */
    private function setMethods(array|string $methods = []): void
    {
        if(!is_array($methods)) $methods = [$methods];

        if(!count($methods)) return;

        $this->methods = $methods;
    }

    /**
     * @param bool $auth
     * @return void
     */
    private function setAuth(bool $auth = false): void
    {
        $this->auth = $auth;
    }

    /**
     * @param array $data
     * @return void
     */
    private function setData(array $data = []): void
    {
        if(!count($data)) return;

        $this->data = $data;
    }


    /**
     * @return array
     */
    public function getMethods(): array
    {
        return $this->methods ?? [];
    }

    /**
     * @return bool
     */
    public function isAuth(): bool
    {
        return $this->auth ?? false;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data ?? [];
    }
}