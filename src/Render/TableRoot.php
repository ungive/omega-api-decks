<?php

namespace Render;


# TODO: implement this.
# currently a table is always rooted at the top left,
# i.e. the top-left most cell will always touch point (0, 0).
class TableRoot
{
    const TOP_LEFT = 1;
    const TOP_RIGHT = 2;
    const BOTTOM_LEFT = 3;
    const BOTTOM_RIGHT = 4;
}
