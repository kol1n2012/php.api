<?php

namespace App\HTTP;

final class Request
{
    /**
     * @var Request
     */
    protected static $_instance;

    /**
     * @var array
     */
    private array $data;

    /**
     * @var string
     */
    private string $method = "";

    private function __construct()
    {
        $this->setMethod();
        $this->setData($_REQUEST);
    }

    /**
     * @return Request
     */
    public static function getInstance(): Request
    {
        if (self::$_instance === null) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }

    /**
     * @return void
     */
    private function setMethod(): void
    {
        $this->method = $_SERVER['REQUEST_METHOD'];
    }

    /**
     * @param array $data
     * @return void
     */
    private function setData(array $data = []): void
    {
        foreach ($data as $key => $value) {
            $this->data[$key] = trim(htmlspecialchars($value, ENT_QUOTES));
        }
    }

    /**
     * @param string $name
     * @return mixed|void
     */
    public function __get(string $name = '')
    {
        if(!mb_strlen($name)) return;
        if(!isset($this->data[$name])) return;

        return @$this->data[$name];
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method ?? "";
    }

    /**
     * @return void
     */
    private function __clone(): void
    {
    }

    /**
     * @return void
     */
    private function __wakeup(): void
    {
    }

    public function __destruct()
    {
        $this->data = [];
    }
}
