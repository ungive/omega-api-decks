<?php

namespace Http;

use \Http\JsonResponseSerializer;


class Http
{
    const OK = 200;

    const BAD_REQUEST = 400;
    const NOT_FOUND = 404;
    const METHOD_NOT_ALLOWED = 405;

    const INTERNAL_SERVER_ERROR = 500;

    private static string $response_serializer_type
        = JsonResponseSerializer::class;

    public static function allow_methods(string ...$methods): void
    {
        foreach ($methods as $method)
            if ($method === $_SERVER['REQUEST_METHOD'])
                return;

        self::response_code(self::METHOD_NOT_ALLOWED);
        self::close();
    }

    public static function allow_method(string $method): void
    {
        self::allow_methods($method);
    }

    public static function get_query_parameter(string $name,
                                               bool $required = true): ?string
    {
        if (!isset($_GET[$name])) {
            if (!$required) return null;

            $message = "query parameter '$name' is required";
            self::fail($message, self::BAD_REQUEST);
        }

        return $_GET[$name];
    }

    public static function response_code(?int $response_code = null): int
    {
        return http_response_code($response_code);
    }

    public static function header(string $name, $value, bool $replace = true,
                                  int $http_response_code = null): void
    {
        header("$name: $value", $replace, $http_response_code);
    }

    public static function set_response(Response $response): void
    {
        $serializer = new self::$response_serializer_type();
        $content_type = $serializer->content_type();
        self::header('Content-Type', $content_type);

        echo $serializer->serialize($response);
        Http::close($response->code);
    }

    public static function fail(string $message,
                                int $code = self::INTERNAL_SERVER_ERROR): void
    {
        get_logger('http')->alert("failed with: $message");

        $response = new ErrorResponse($code, $message);
        self::set_response($response);
    }

    public static function close(?int $code = null)
    {
        if ($code !== null)
            self::response_code($code);
        exit;
    }
}
