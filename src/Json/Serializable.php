<?php

namespace Json;


abstract class Serializable implements \JsonSerializable
{
    /**
    * Serializes the current instance to a value that should
    * be passed as a value to the json_encode() function
    * @return mixed the value that is to be serialized
    */
    protected abstract function json_serialize();

    /**
    * Deserializes the given output from json_decode() and converts
    * it to an instance of the class implementing this method.
    * @param mixed $value the result of json_decode()
    * @return mixed an instance of the implementing class
    */
    protected abstract static function json_deserialize($value);

    public function jsonSerialize()
    {
        return $this->json_serialize();
    }

    public function to_json(int $options = 0, int $depth = 1 << 9): string
    {
        $result = $this->json_serialize();
        return json_encode($result, $options, $depth);
    }

    public static function from_json(string $json, int $depth = 1 << 9,
                                     int $options = 0)
    {
        $options |= JSON_THROW_ON_ERROR;
        $value = json_decode($json, true, $depth, $options);
        $class = get_called_class();

        // $reflection = new \ReflectionClass($class);

        // $constructor = $reflection->getConstructor();
        // $with_constructor = $constructor === null
        //     || $constructor->getNumberOfParameters() === 0;

        // $instance = $with_constructor
        //     ? $reflection->newInstance()
        //     : $reflection->newInstanceWithoutConstructor();

        // $instance = $reflection->newInstanceWithoutConstructor();

        return $class::json_deserialize($value);
    }
}
