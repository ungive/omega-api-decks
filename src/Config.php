<?php

class Config
{
    private static array $config = [];
    private static array $env_defaults = [];

    public static function get(string $key)
    {
        if (!isset(self::$config[$key]))
            throw new \Exception("key '$key' does not exist in configuration");

        return self::$config[$key];
    }

    public static function set(string $key, $value): void
    {
        self::$config[$key] = $value;
    }

    public static function set_all(array $values): void
    {
        foreach ($values as $key => $value)
            self::set($key, $value);
    }

    public static function get_env(string $name): string
    {
        if (($value = getenv($name)) !== false)
            return $value;

        if (isset(self::$env_defaults[$name]))
            return self::$env_defaults[$name];

        throw new \Exception("environment variable $name is not set");
    }

    public static function put_env(string $name, string $value): void
    {
        if (putenv("$name=$value") === false)
            throw new \Exception("failed to set environment variable $name");
    }

    public static function require_env(string $name, ?string $default = null): void
    {
        if (getenv($name) === false && $default === null)
            throw new \Exception("required environment variable $name is not set");

        self::$env_defaults[$name] = $default;
    }
}
