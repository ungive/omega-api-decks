<?php

namespace Http;


class JsonErrorResponse extends JsonResponse
{
    const ERROR_KEY = 'error';

    public function __construct(?string $message = null)
    {
        $this->success(false);
        $this->error($message);
    }

    public function success(bool $success): void
    {
        if ($success)
            throw new \InvalidArgumentException(
                "an error response cannot be successful");

        parent::success($success);
    }

    public function error(?string $message): void
    {
        $this->meta(self::ERROR_KEY, $message);
    }

    public function get_error(): ?string
    {
        return $this->get_meta(self::ERROR_KEY);
    }
}
