<?php

namespace App\HTTP;

final class Response
{
    /**
     * @var Response
     */
    protected static $_instance;

    /**
     * @var int
     */
    protected int $code;

    /**
     * @var string
     */
    protected string $body;

    /**
     * @var array
     */
    protected array $headers;

    /**
     * @var string
     */
    private string $message = '';

    /**
     * @var bool
     */
    private bool $status = false;

    private function __construct()
    {
    }

    /**
     * @param int $code
     * @return Response
     */
    public static function getInstance(int $code = 200): Response
    {
        if (self::$_instance === null) {
            self::$_instance = new self;
        }

        if ($code) {
            self::$_instance->setCode($code);
        }

        return self::$_instance;
    }

    /**
     * @return void
     */
    private function send(): void
    {
        $output = $this->getBody();

        foreach ($this->getHeaders() as $header => $value) {
            header("$header: $value");
        }

        $result = json_encode(['status' => $this->getStatus(), 'message' => $this->getMessage(), 'result' => 'json_valid'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $result = str_replace("\"json_valid\"", '%s', $result);

        http_response_code($this->getCode());

        echo sprintf($result, $output);
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

    /**
     * @param int $code
     * @return void
     */
    private function setCode(int $code = 200): void
    {
        $this->code = $code;
    }

    /**
     * @param array $headers
     * @return void
     */
    public function setHeaders(array $headers = []): void
    {
        $this->headers = $headers;
    }

    /**
     * @param string $body
     * @return void
     */

    public function setBody(string $body = '[]'): void
    {
        $this->body = $body;
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
     * @param bool $status
     * @return void
     */
    public function setStatus(bool $status = false): void
    {
        $this->status = $status;
    }

    /**
     * @return int
     */
    private function getCode(): int
    {
        return $this->code ?? 200;
    }

    /**
     * @return array
     */
    private function getHeaders(): array
    {
        return $this->headers ?? [];
    }

    /**
     * @return string
     */
    private function getBody(): string
    {
        return $this->body ?? '[]';
    }

    /**
     * @return bool
     */
    private function getStatus(): bool
    {
        return $this->status ?? false;
    }

    /**
     * @return string
     */
    private function getMessage(): string
    {
        return $this->message ?? '';
    }

    public function __destruct()
    {
        $this->send();
    }

}