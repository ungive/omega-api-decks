<?php

namespace Image;


class ImageType
{
    const NONE = -1;
    const AUTO = 0;

    const GIF  = IMAGETYPE_GIF;
    const JPEG = IMAGETYPE_JPEG;
    const PNG  = IMAGETYPE_PNG;
    const BMP  = IMAGETYPE_BMP;
    const WBMP = IMAGETYPE_WBMP;
    const XBM  = IMAGETYPE_XBM;
    const WEBP = IMAGETYPE_WEBP;

    public static function is_valid(int $type): bool
    {
        return $type > 0;
    }

    public static function from_format(string $format): int
    {
        switch (strtoupper($format)) {
        case 'GIF': return self::GIF;
        case 'JPG':
        case 'JPEG': return self::JPEG;
        case 'PNG':  return self::PNG;
        case 'BMP':  return self::BMP;
        case 'WBMP': return self::WBMP;
        case 'XBM':  return self::XBM;
        case 'WEBP': return self::WEBP;
        }

        throw new ImageException("unsupported image format");
    }

    public static function is_format(string $format): bool
    {
        try {
            self::from_format($format);
        }
        catch (ImageException $e) {
            return false;
        }

        return true;
    }

    public static function from_filename(string $filename): int
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        return ImageType::from_format($extension);
    }

    public static function extension(int $type, bool $include_dot = true): ?string
    {
        return image_type_to_extension($type, $include_dot) ?: null;
    }

    public static function mime_type(int $type): ?string
    {
        return image_type_to_mime_type($type) ?: null;
    }

    public static function types(): \Generator
    {
        $reflection = new \ReflectionClass(__CLASS__);
        foreach($reflection->getConstants() as $format => $type) {
            if ($type <= 0)
                continue;

            yield $format => $type;
        }
    }

    public static function extensions(bool $include_dot = true): \Generator
    {
        foreach(self::types() as $type)
            yield $type => self::extension($type, $include_dot);
    }

    public static function mime_types(): \Generator
    {
        foreach(self::types() as $type)
            yield $type => self::mime_type($type);
    }
}
