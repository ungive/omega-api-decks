<?php

namespace Image;

use Render\Rectangle;
use Render\Vector;

class Image
{
    private $handle;
    private int $type;

    public function __construct($handle = null, int $type = ImageType::NONE)
    {
        assert($type !== ImageType::AUTO, "cannot detect type from handle");

        $this->handle = $handle;
        $this->type = $type;
    }

    public function __destruct()
    {
        if ($this->handle !== null)
            imagedestroy($this->handle);
    }

    public function handle($handle = false)
    {
        if ($handle !== false)
            $this->handle = $handle;

        return $this->handle;
    }

    public function type(int $type = ImageType::AUTO): int
    {
        if ($type !== ImageType::AUTO)
            $this->type = $type;

        return $this->type;
    }

    public function move_from(Image $image): void
    {
        $this->handle = $image->handle;
        $this->type = $image->type;

        $image->handle = null;
        $image->type = ImageType::NONE;
    }

    public function width(): int { return imagesx($this->handle); }
    public function height(): int { return imagesy($this->handle); }

    public function resize(int $width, int $height, bool $resample = false): bool
    {
        assert($width > 0 || $height > 0, "one side must be larger than 0");

        if ($this->width() === $width && $this->height() < $height) return true;
        if ($this->height() === $height && $this->width() < $width) return true;

        $w = $this->width();
        $h = $this->height();
        $r = $w / $h;

        if ($width === 0) {
            $factor = $height / $h;
            $width = intval($w * $factor);
        }
        else if ($height === 0) {
            $factor = $width / $w;
            $height = intval($h * $factor);
        }
        else if ($width / $height > $r)
            $width = intval($height * $r);
        else
            $height = intval($width / $r);

        $source = $this->handle;
        $destination = imagecreatetruecolor($width, $height);

        $resize = $resample ? 'imagecopyresampled' : 'imagecopyresized';
        $result = $resize($destination, $source, 0, 0, 0, 0, $width, $height, $w, $h);

        if (!$result)
            return false;

        $this->handle = $destination;
        return true;
    }

    public function insert(
        Image $other,
        Vector $to_position, Vector $from_position,
        Rectangle $to_dimensions, Rectangle $from_dimensions,
        bool $resample = false
    ): bool
    {
        $resize = $resample ? 'imagecopyresampled' : 'imagecopyresized';
        return $resize(
            $this->handle(), $other->handle(),
            $to_position->x(), $to_position->y(),
            $from_position->x(), $from_position->y(),
            $to_dimensions->width(), $to_dimensions->height(),
            $from_dimensions->width(), $to_dimensions->height()
        );
    }

    public function write(int $type = ImageType::AUTO, $to = null, ...$args): bool
    {
        if ($type === ImageType::AUTO)
            $type = $this->type;

        switch ($type) {
        case ImageType::GIF:  $result = imagegif ($this->handle, $to, ...$args); break;
        case ImageType::JPEG: $result = imagejpeg($this->handle, $to, ...$args); break;
        case ImageType::PNG:  $result = imagepng ($this->handle, $to, ...$args); break;
        case ImageType::BMP:  $result = imagebmp ($this->handle, $to, ...$args); break;
        case ImageType::WBMP: $result = imagewbmp($this->handle, $to, ...$args); break;
        case ImageType::XBM:  $result = imagexbm ($this->handle, $to, ...$args); break;
        case ImageType::WEBP: $result = imagewebp($this->handle, $to, ...$args); break;
        default: throw new ImageException("unsupported image type");
        }

        return $result;
    }

    public function echo(int $type = ImageType::AUTO,
                         bool $send_headers = true, ...$args): bool
    {
        if ($type === ImageType::AUTO)
            $type = $this->type;

        if ($send_headers) {
            $mime_type = ImageType::mime_type($type);
            header("Content-Type: $mime_type");
        }

        return $this->write($type, null, ...$args);
    }

    public static function from_image(self $image): self
    {
        return new self($image->handle(), $image->type());
    }

    public static function from_file(string $name,
                                     int $type = ImageType::AUTO): ?Image
    {
        if ($type === ImageType::AUTO)
            $type = ImageType::from_filename($name);

        if (!is_readable($name))
            throw new ImageException("cannot read file");

        switch ($type) {
        case ImageType::GIF:  $handle = imagecreatefromgif($name);  break;
        case ImageType::JPEG: $handle = imagecreatefromjpeg($name); break;
        case ImageType::PNG:  $handle = imagecreatefrompng($name);  break;
        case ImageType::BMP:  $handle = imagecreatefrombmp($name);  break;
        case ImageType::WBMP: $handle = imagecreatefromwbmp($name); break;
        case ImageType::XBM:  $handle = imagecreatefromxbm($name);  break;
        case ImageType::WEBP: $handle = imagecreatefromwebp($name); break;
        default: throw new ImageException("unsupported image type");
        }

        return $handle !== false ? new Image($handle, $type) : null;
    }

    public static function from_url(string $url, int $type = ImageType::AUTO,
                                    int $timeout_ms = 0): ?Image
    {
        if ($type === ImageType::AUTO)
            $type = ImageType::from_filename($url);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, $timeout_ms);
        $data = curl_exec($ch);

        $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $is_valid_type = $content_type === false;

        if (!$is_valid_type)
            foreach (explode(';', $content_type) as $part)
                if (trim($part) === ImageType::mime_type($type)) {
                    $is_valid_type = true;
                    break;
                }

        if (!$is_valid_type)
            throw new ImageException(
                "url returned unexpected content: $content_type");

        $errno = curl_errno($ch);
        $error = curl_error($ch);

        curl_close($ch);

        if ($errno !== 0)
            throw new ImageException("could not open image: $error");

        return Image::from_data($data, $type);
    }

    public static function from_data(string $data,
                                     int $type = ImageType::NONE): ?Image
    {
        $handle = imagecreatefromstring($data);
        return $handle !== false ? new Image($handle, $type) : null;
    }
}
