<?php

namespace Image;


class ImageCache implements \Countable
{
    const SUBFOLDER_PAD_CHAR = '_';

    private string $directory;
    private int $subfolder_length;

    private int $type = ImageType::NONE;
    private ?string $custom_extension = null;

    private ?\Closure $loader_ = null;

    private array $cached = [];
    private array $loaded = [];

    public function __construct(string $directory, int $subfolder_length = 0)
    {
        $this->directory = $directory;
        $this->subfolder_length = $subfolder_length;
    }

    public function directory(): string { return $this->directory; }
    public function subfolder_length(): int { return $this->subfolder_length; }

    public function loader(\Closure $loader): void { $this->loader_ = $loader; }

    public function type(int $type = ImageType::NONE, ?string $custom_extension = null): int
    {
        if ($type !== ImageType::NONE) {

            if ($type !== $this->type) {
                assert($custom_extension === null || strlen($custom_extension) > 0,
                    "custom extension is empty");
                $this->custom_extension = $custom_extension;
            }

            $this->type = $type;
        }

        return $this->type;
    }

    public function extension(): string
    {
        if ($this->type === ImageType::NONE)
            throw new ImageException("cannot determine extension without a type");

        if ($this->type === ImageType::AUTO)
            throw new ImageException(
                "cannot determine extension with potentially multiple types");

        if ($this->custom_extension !== null)
            return $this->custom_extension;

        return ImageType::extension($this->type);
    }

    public function load_with_loader($key, int $type = ImageType::AUTO): ?ImageCacheEntry
    {
        if ($this->loader_ === null)
            throw new ImageException("cannot load without a loader");

        $image_key = new ImageKey($key);

        if ($type === ImageType::AUTO)
            $type = $this->type;

        $image = ($this->loader_)($image_key, $type);
        if ($image === null)
            return null;

        if (! $image instanceof Image)
            throw new ImageException("the loader is expected to return an Image");

        // make sure the image has the required type.
        if ($type !== ImageType::AUTO)
            $image->type($type);

        $entry = new ImageCacheEntry($this, $image, $image_key);
        $this->loaded[$image_key->value()] = $entry;

        return $entry;
    }

    public function read_from_disk($key, int $type = ImageType::AUTO): ?ImageCacheEntry
    {
        $image = new Image();
        $image_key = new ImageKey($key);
        $entry = new ImageCacheEntry($this, $image, $image_key);

        $directory = $entry->directory();
        $filename  = $entry->filename();
        $extension = null;

        if ($type === ImageType::AUTO) {

            $files = glob("$directory/$filename.?*", GLOB_NOSORT);
            if (count($files) === 0)
                return null;

            $is_valid = false;
            foreach ($files as $file) {
                $extension = pathinfo($file, PATHINFO_EXTENSION);
                if (ImageType::is_format($extension)) {
                    $is_valid = true;
                    break;
                }
            }

            if (!$is_valid)
                return null;
        }
        else
            $extension = ImageType::extension($type);

        $cache_image = Image::from_file("$directory/$filename.$extension");
        if ($cache_image === null)
            return null;

        $image->move_from($cache_image);
        $this->cached[$image_key->value()] = $entry;

        return $entry;
    }

    /**
    * looks for the specified key in this object's internal memory,
    * in the cache directory specified during construction time or
    * it attempts to load it with this object's loader function.
    */
    public function load($key): ?ImageCacheEntry
    {
        $entry = $this->read($key);
        if ($entry !== null)
            return $entry;

        return $this->load_with_loader($key);
    }

    /**
    * looks for the specified key in this object's internal memory
    * or in the cache directory specified during construction time.
    */
    public function read($key): ?ImageCacheEntry
    {
        $entry = $this->get($key);
        if ($entry !== null)
            return $entry;

        return $this->read_from_disk($key);
    }

    /**
    * looks for the specified key in this object's internal memory.
    */
    public function get($key): ?ImageCacheEntry
    {
        $cached = $this->get_from_cached($key);
        return $cached ?: $this->get_from_loaded($key);
    }

    public function get_from_cached($key): ?ImageCacheEntry
    {
        return isset($this->cached[$key]) ? $this->cached[$key] : null;
    }

    public function get_from_loaded($key): ?ImageCacheEntry
    {
        return isset($this->loaded[$key]) ? $this->loaded[$key] : null;
    }

    public function loaded(): \Generator
    {
        foreach ($this->loaded as $entry)
            yield $entry->key()->value() => $entry;
    }

    public function cached(): \Generator
    {
        foreach ($this->cached as $entry)
            yield $entry->key()->value() => $entry;
    }

    public function flush(int $type = ImageType::AUTO, ...$args): int
    {
        $count = 0;

        foreach ($this->loaded() as $key => $entry) {
            $entry->flush($type, ...$args);

            unset($this->loaded[$key]);
            $this->cached[$key] = $entry;

            $count ++;
        }

        return $count;
    }

    public function clear_buffer()
    {
        $this->cached = [];
        $this->loaded = [];
    }

    public function count_cached(): int { return count($this->cached); }
    public function count_loaded(): int { return count($this->loaded); }

    public function count(): int
    {
        return $this->count_cached() + $this->count_loaded();
    }

    // public function load_or_null($key, int $type = ImageType::AUTO): ?ImageCacheEntry
    // {
    //     try {
    //         $entry = $this->read_from_disk($key, $type);
    //     }
    //     catch (ImageException $e) {
    //         $entry = null;
    //     }

    //     if ($entry === null)
    //         $entry = $this->load_with_loader($key, $type);

    //     return $entry;
    // }

    // public function load($key, int $type = ImageType::AUTO): ImageCacheEntry
    // {
    //     $entry = $this->load_or_null($key, $type);
    //     if ($entry === null)
    //         throw new ImageException("failed to load image");

    //     return $entry;
    // }

    // public function get_or_null($key): ?ImageCacheEntry
    // {
    //     $cached = $this->get_from_cached($key);
    //     return $cached ?: $this->get_from_loaded($key);
    // }

    // public function get($key): ImageCacheEntry
    // {
    //     $entry = $this->get_or_null($key);
    //     if ($entry === null)
    //         throw new ImageException("this key does not exist");

    //     return $entry;
    // }

    // public function get_or_load($key, int $type = ImageType::AUTO): ImageCacheEntry
    // {
    //     $entry = $this->get_or_null($key);
    //     if ($entry !== null)
    //         return $entry;

    //     return $this->load($key, $type);
    // }
}
