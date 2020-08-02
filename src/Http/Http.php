<?php

namespace Http;

use \Http\Serializer\ResponseSerializer;
use \Http\Serializer\JsonResponseSerializer;


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
            if (strtoupper($method) === strtoupper($_SERVER['REQUEST_METHOD']))
                return;

        http_response_code(Http::METHOD_NOT_ALLOWED);
        die;
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

            $message = "parameter '$name' is required";
            self::set_json_error_response(Http::BAD_REQUEST, $message);
        }

        return $_GET[$name];
    }

    public static function set_response(Response $response,
                                        ResponseSerializer $serializer): void
    {
        $content_type = $serializer->get_content_type();
        header('Content-Type: ' . $content_type);
        echo $serializer->serialize($response);
        Http::exit($response->code);
    }

    public static function set_json_error_response(int $code,
                                                   string $message): void
    {
        $response = new ErrorResponse($code, $message);
        $serializer = new JsonResponseSerializer();
        self::set_response($response, $serializer);
    }

    public static function exit(int $code = Http::OK)
    {
        http_response_code($code);
        exit;
    }
}
