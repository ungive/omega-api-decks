<?php

namespace Game\Repository;


class DataCard
{
    public int $code;
    public int $deck_type;

    public function __construct(int $code, int $deck_type)
    {
        $this->code = $code;
        $this->deck_type = $deck_type;
    }
}


use \Utility\TypedListObject;

class DataCardList extends TypedListObject
{
    protected function allowed($value): bool
    {
        return $value instanceof DataCard;
    }
}
