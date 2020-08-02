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

/**
* unpacks according to format an increments data appropriately.
*/
function unpack_inc(string $format, string &$data)
{
    assert(false, "TODO: bugged. increment based on format.");

    $unpacked = unpack($format, $data);
    $data = substr($data, count($unpacked));
    return $unpacked;
}

// function set_response_code(int $code)
// {
//     http_response_code($code);
//     die;
// }

function temp_filename(): string
{
    return tempnam(sys_get_temp_dir(), '');
}

function download_file(string $dest_file, string $url, ?int &$status_code): bool
{
    if (file_exists($dest_file))
        unlink($dest_file);

    $fp = fopen($dest_file, 'w+');
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);

    curl_exec($ch);

    if ($is_error = curl_errno($ch) !== 0)
        get_logger()->warning('cURL: ' . curl_error($ch));
    else
        $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);
    fclose($fp);

    if ($is_error || $status_code !== 200) {
        unlink($dest_file);
        return false;
    }

    return true;
}
