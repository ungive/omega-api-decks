<?php

namespace Utility;


abstract class TypedListObject extends ListObject
{
    public function __construct($v = [])
    {
        parent::__construct();
        $this->append_all($v);
    }

    protected abstract function allowed($value): bool;

    public function offsetSet($offset, $value): void
    {
        if (!$this->allowed($value))
            throw new \TypeError("array element has the wrong type");

        parent::offsetSet($offset, $value);
    }
}
