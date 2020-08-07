<?php

namespace Image;


/**
* an Image that only lives in memory and thus cannot be written anywhere.
*/
class MemoryImage extends Image
{
    public function write(int $type = ImageType::AUTO, $to = null, ...$args): bool
    {
        if ($to === null)
            return parent::write($type, $to, ...$args);
        return false;
    }

    public static function from_file(string $name,
                                     int $type = ImageType::AUTO): ?MemoryImage
    {
        $image = parent::from_file($name, $type);
        return self::move_from_image_or_null($image);
    }

    public static function from_url(string $url, int $timeout_ms = 0,
                                    int $type = ImageType::AUTO): ?MemoryImage
    {
        $image = parent::from_url($url, $timeout_ms, $type);
        return self::move_from_image_or_null($image);
    }

    public static function from_data(string $data,
                                     int $type = ImageType::NONE): ?MemoryImage
    {
        $image = parent::from_data($data, $type);
        return self::move_from_image_or_null($image);
    }

    private static function move_from_image(Image $image): MemoryImage
    {
        $result = new MemoryImage();
        $result->move_from($image);
        return $result;
    }

    public static function move_from_image_or_null(?Image $image): ?MemoryImage
    {
        return $image !== null ? self::move_from_image($image) : null;
    }
}
