<?php

namespace Json;

use Exception;

use function Utility\array_depth;


abstract class Serializable implements \JsonSerializable
{
    protected abstract static function json_template(): Template;

    public function jsonSerialize()
    {
        return $this->json_serialize();
    }

    public function json_serialize(?int &$depth = null)
    {
        return self::serialize($this, $this->json_template(), $depth);
    }

    public function to_json(int $options = 0): string
    {
        $template = $this->json_template();

        $depth = null;
        $value = $template->is_valid()
            ? self::serialize($this, $template, $depth)
            : null;

        return json_encode($value, $options, $depth);
    }

    public static function from_json(string $json, int $depth = 1 << 9): self
    {
        $class = get_called_class();
        $reflection = new \ReflectionClass($class);

        $object = json_decode($json, false, $depth, JSON_THROW_ON_ERROR);
        $template = $class::json_template();
        $instance = $reflection->newInstanceWithoutConstructor();

        return self::deserialize($object, $template, $instance);
    }

    private static function deserialize($context, Template $template,
                                        object &$result): object
    {
        $result_class = new \ReflectionClass(get_class($result));

        if ($template instanceof Property) {
            $template = new Template([ $template ]);
            $context  = (object)[ $context ];
        }

        foreach ($template->template as $name => $property) {

            if (is_array($property)) {
                $sub_template = new Template($property);
                $result = self::deserialize($context->$name, $sub_template, $result);
                continue;
            }

            self::check_property($property);

            $key   = $property->name;
            $value = $context->$name;

            if (is_object($value)) {
                $type = $result_class->getProperty($key)->getType();
                $property_class = $type ? $type->getName() : stdClass::class;

                $property_reflection = new \ReflectionClass($property_class);
                $instance = $property_reflection->newInstanceWithoutConstructor();

                if ($property->has_template())
                    $value = self::deserialize($value, $property, $instance);
                else if (is_subclass_of($property_class, self::class)) {
                    $value = self::deserialize($value, $instance->json_template(), $instance);
                }
                else
                    $value = self::cast($value, $property_class);
            }

            $result->$key = $value;
        }

        return $result;
    }

    private static function serialize(object $context, Template $template,
                                      ?int &$depth = null): array
    {
        if ($depth === null)
            $depth = 1;

        $max_depth = 0;
        $result = [];

        if ($is_property = $template instanceof Property)
            $template = new Template([ $template ]);

        foreach ($template->template as $key => $property) {

            if (is_array($property)) {
                $sub_template = new Template($property);
                $value = self::serialize($context, $sub_template, $depth);
                $result[$key] = $value;
                $depth ++;
                continue;
            }

            self::check_property($property);

            $name = $property->name;

            if (!property_exists($context, $name))
                throw new JsonSerializeException(
                    "property '$name' in JSON template of class " .
                    get_class($context) . " does not exist");

            $value = $context->$name;

            if ($property->has_template())
                $value = self::serialize((object)$value, $property, $depth);

            else if (is_subclass_of($value, self::class))
                $value = $value->json_serialize($depth);

            else {
                if (is_object($value))
                    $value = (array)$value;

                if (is_array($value))
                    $max_depth = max($max_depth, array_depth($value));
                else if (is_resource($value))
                    $value = strval($value);
                else if (!is_scalar($value))
                    throw new \Exception("cannot convert type " . gettype($value));
            }

            $result[$key] = $value;
        }

        $depth += $max_depth;

        if ($is_property) {
            $result = $result[0];
            $depth -= 1;
        }

        return $result;
    }

    private static function check_property(&$property): Property
    {
        if (is_string($property))
            return $property = new Property($property);

        if (! $property instanceof Property)
            throw new \Exception(
                "a template's value must be an instance of " .
                Property::class
            );

        return $property;
    }

    private static function cast(object $instance, string $class): object
    {
        return unserialize(sprintf(
            'O:%d:"%s"%s',
            strlen($class),
            $class,
            strstr(strstr(serialize($instance), '"'), ':')
        ));
    }
}


class JsonSerializeException extends Exception {}
class JsonDeserializeException extends Exception {}
