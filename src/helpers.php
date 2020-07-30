<?php

namespace Utility;


function starts_with($haystack, $needle)
{
    return substr($haystack, 0, strlen($needle)) === $needle;
}

function ends_with($haystack, $needle)
{
    return substr($haystack, strlen($haystack) - strlen($needle)) === $needle;
}
