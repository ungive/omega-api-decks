<?php

// disable warnings when errors are displayed because the omega deck code
// can emit a gzip warning that messes up any image that we try to transmit.
if (ini_get("display_errors") === '1')
    error_reporting(E_ALL & ~E_WARNING);
