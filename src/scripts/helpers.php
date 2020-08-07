<?php

namespace Utility;

use Http\Http;

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
    else {
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $is_error = $code != 0 && $code !== Http::OK;
        if ($code !== 0) $status_code = $code;
    }

    curl_close($ch);
    fclose($fp);

    if ($is_error) {
        unlink($dest_file);
        return false;
    }

    return true;
}

class FileLock
{
    private string $filename;
    private bool $is_locked = false;

    private $file_handle = null;

    public function __construct(string $filename)
    {
        $this->filename = $filename;
    }

    public function lock(): bool
    {
        $this->file_handle = fopen($this->filename, 'a+');
        chmod($this->filename, 0660);

        if ($success = $this->file_handle !== false) {
            flock($this->file_handle, LOCK_EX);
            $this->is_locked = true;
        }

        return $success;
    }

    public function unlock()
    {
        if (!$this->is_locked)
            return;

        flock($this->file_handle, LOCK_UN);
        $this->is_locked = false;

        fclose($this->file_handle);
        $this->file_handle = null;

        if (file_exists($this->filename))
            unlink($this->filename);
    }

    public function is_locked()
    {
        if ($this->is_locked) return true;
        // if ($this->file_handle === null)
        //     return false;

        $fp = fopen($this->filename, 'a+');
        flock($fp, LOCK_EX | LOCK_NB, $wouldblock);
        flock($fp, LOCK_UN);
        fclose($fp);

        return $wouldblock === 1;
    }
}

function continue_in_background(bool $ignore_user_abort = true,
                                int $time_limit = 0): void
{
    ignore_user_abort($ignore_user_abort);
    set_time_limit($time_limit);

    header("Connection: close");
    ob_end_flush();
    flush();
}
