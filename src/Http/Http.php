<?php

use Http\JsonErrorResponse;
use Http\JsonResponse;
use Json\Json;


class Http
{
    const OK = 200;

    const BAD_REQUEST = 400;
    const NOT_FOUND = 404;
    const METHOD_NOT_ALLOWED = 405;

    const INTERNAL_SERVER_ERROR = 500;

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

    // public static function expect_query_parameter_count(int $count): void
    // {
    //     assert($count >= 0, "query parameter count cannot be less than 0");

    //     if (count($_GET) === $count)
    //         return;

    //     self::fail("too many query parameters, $count expected", self::BAD_REQUEST);
    // }

    // public static function allow_query_parameters(string ...$names): void
    // {
    //     $allowed = [];
    //     foreach ($names as $name)
    //         $allowed[$name] = true;

    //     $disallowed_name = null;

    //     foreach (array_keys($_GET) as $name)
    //         if (!isset($allowed[$name])) {
    //             $disallowed_name = $name;
    //             break;
    //         }

    //     if ($disallowed_name === null)
    //         return;

    //     self::fail("unrecognized query parameter '$disallowed_name'", self::BAD_REQUEST);
    // }

    // public static function get_query_parameter_names(): \Generator
    // {
    //     foreach (array_keys($_GET) as $name)
    //         yield $name;
    // }

    // public static function get_first_query_parameter_name(): string
    // {
    //     if (($name = self::get_query_parameter_names()->current()) !== null)
    //         return $name;

    //     throw new \Exception("there are no query parameters");
    // }

    public static function get_query_parameter(string $name, bool $required = true, $default = null): ?string
    {
        if (!isset($_GET[$name])) {
            if (!$required) return $default;

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

    public static function send(JsonResponse $response, int $code = self::OK): void
    {
        if ($response instanceof JsonErrorResponse)
            get_logger('http')->alert("failed with: " . $response->get_error());

        if (Http::get_query_parameter('pretty', false) !== null)
            $response->options(JSON_PRETTY_PRINT);

        self::header('Content-Type', $response::mime_type());
        echo $response->to_json();
        self::close($code);
    }

    public static function fail(string $message,
                                int $code = self::INTERNAL_SERVER_ERROR,
                                array $extra_meta = [],
                                array $extra_data = []): void
    {
        $response = new JsonErrorResponse($message);

        foreach ($extra_meta as $key => $value) $response->meta($key, $value);
        foreach ($extra_data as $key => $value) $response->data($key, $value);

        self::send($response, $code);
    }

    public static function close(?int $code = null)
    {
        if ($code !== null)
            self::response_code($code);
        exit;
    }
}
