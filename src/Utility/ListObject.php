<?php

namespace Utility;


class ListObject extends \ArrayObject
{
    public function array(): array
    {
        return (array)$this;
    }

    public function column(string $property): array
    {
        return array_column($this->array(), $property);
    }

    public function append_all($values): void
    {
        $this->exchangeArray(array_merge($this->array(), (array)$values));
    }

    public function merge($other): ListObject
    {
        $result = clone $this;
        $result->append_all($other);
        return $result;
    }
}
