<?php

namespace Http\Serializer;

use \Http\Response;


class JsonResponseSerializer implements ResponseSerializer
{
    public function serialize(Response $response): string
    {
        $ordered = [];

        // make sure code and type come first.
        $ordered['code'] = $response->code;
        $ordered['type'] = $response->type;

        $reflection = new \ReflectionClass($response);

        foreach ($reflection->getProperties() as $property)
            if ($property->class === $reflection->name) {
                $name = $property->name;
                $ordered[$property->name] = $response->$name;
            }

        return json_encode($ordered, JSON_PRETTY_PRINT);
    }

    public function get_content_type(): string
    {
        return 'application/json';
    }
}
