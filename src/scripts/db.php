<?php

const SEPARATOR = ',';
const PLACEHOLDER = '?';

function insert_chunked(\PDO $db, string $table,
                        array $columns, array $rows,
                        int $chunk_size = 16)
    : bool
{
    if (count($rows) === 0)
        return true;

    $chunks = array_chunk($rows, $chunk_size);
    $last_chunk = array_splice($chunks, count($chunks) - 1, 1);

    $column_names = implode(SEPARATOR, $columns);
    $row_placeholders = implode(SEPARATOR, array_fill(0, count($columns), PLACEHOLDER));
    $base_query = "INSERT INTO $table ($column_names) VALUES";

insert:
    $placeholders = implode(SEPARATOR, array_fill(0, $chunk_size, "($row_placeholders)"));
    $statement = $db->prepare("$base_query $placeholders;");
    if (!$statement)
        return false;

    foreach ($chunks as $chunk) {
        $values = array_merge(...$chunk);
        $statement->execute($values);
    }

    // last chunk
    if (count($last_chunk) > 0) {
        $chunk_size = count($last_chunk[0]);
        $chunks = array_splice($last_chunk, 0);
        $last_chunk = [];
        goto insert;
    }

    return true;
}

function sanitize_name(string $name): string
{
    $name = preg_replace("/\s+/", "_", $name);
    $name = strtolower($name);
    $name = preg_replace("/[^_a-z0-9]/", "-", $name);
    return $name;
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
        if ($this->file_handle === null)
            return false;

        $fp = fopen($this->filename, 'a+');
        flock($fp, LOCK_EX | LOCK_NB, $wouldblock);
        flock($fp, LOCK_UN);
        fclose($fp);

        return $wouldblock === 1;
    }
}
