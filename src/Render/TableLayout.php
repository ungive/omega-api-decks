<?php

namespace Render;


class TableLayout
{
    const LEFT_TO_RIGHT = 0x2;
    const RIGHT_TO_LEFT = 0x4;

    const TOP_TO_BOTTOM = 0x8;
    const BOTTOM_TO_TOP = 0x10;

    public static function opposite(int $layout): int
    {
        switch ($layout) {
        case self::LEFT_TO_RIGHT: return self::RIGHT_TO_LEFT;
        case self::RIGHT_TO_LEFT: return self::LEFT_TO_RIGHT;
        case self::TOP_TO_BOTTOM: return self::BOTTOM_TO_TOP;
        case self::BOTTOM_TO_TOP: return self::TOP_TO_BOTTOM;
        default: throw new Exception("unknown table layout");
        }
    }

    public static function is_horizontal(int $layout): bool
    {
        $horizontal = self::LEFT_TO_RIGHT | self::RIGHT_TO_LEFT;
        return ($layout & $horizontal) === $layout;
    }

    public static function is_vertical(int $layout): bool
    {
        $vertical = self::TOP_TO_BOTTOM | self::BOTTOM_TO_TOP;
        return ($layout & $vertical) === $layout;
    }
}
