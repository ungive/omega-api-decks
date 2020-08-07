<?php

namespace Image;


class ImageCacheEntry
{
    private ImageCache $cache;
    private ImageKey $key;

    private Image $original;
    private Image $image;

    public function __construct(ImageCache $cache, Image $image, ImageKey $key)
    {
        $this->cache = $cache;
        $this->key   = $key;

        $this->original = $image;
        $this->image = $image;
    }

    public function cache() { return $this->cache; }
    public function image() { return $this->image; }
    public function key()   { return $this->key; }

    public function filename(): string
    {
        return $this->key->filename();
    }

    public function subfolder(): ?string
    {
        $length = $this->cache->subfolder_length();
        if ($length <= 0)
            return null;

        $folder = substr($this->key->string(), 0, $length);
        $folder = str_pad($folder, $length, ImageCache::SUBFOLDER_PAD_CHAR);

        return $folder;
    }

    public function directory(): string
    {
        $directory = $this->cache->directory();
        $subfolder = $this->subfolder();

        if ($subfolder !== null)
            $directory .= "/$subfolder";

        return $directory;
    }

    public function flush(int $type = ImageType::AUTO, ...$args): bool
    {
        if ($type === ImageType::AUTO)
            $type = $this->image->type();

        $directory = $this->directory();
        $filename  = $this->key->filename();
        $extension = ImageType::extension($type, false);

        if (!file_exists($directory))
            if (!mkdir($directory, 0775, true))
                throw new ImageException("could not create directory/subfolder");

        $path = "$directory/$filename.$extension";
        return $this->image->write($type, $path, ...$args);
    }
}
