<?php

namespace Config;

use Format\DecodeStrategyTester;
use Format\FormatDecodeStrategy;
use Format\NameFormatDecodeStrategy;
use Format\OmegaFormatStrategy;
use Format\YdkeFormatStrategy;
use Format\YdkFormatStrategy;
use Game\Repository\NameMatchOptions;
use Game\Repository\Repository;
use Image\Image;
use Image\ImageCache;
use Image\ImageKey;
use Image\ImageType;
use Image\MemoryImage;


# TODO: rename all of these to create_* instead of get_*
# TODO: or make these instances singletons. might make sense

function get_repository(): Repository
{
    $options = new NameMatchOptions(
        MAX_LENGTH_DIFF_PER_LETTER,
        MAX_ERRORS_PER_LETTER
    );

    $type = REPOSITORY_TYPE;
    $type = "Game\\Repository\\$type";
    $repository = new $type($options);

    return $repository;
}

function get_decode_strategies(): array # of FormatDecodeStrategy
{
    $repository = get_repository();

    // the ordering here matters. most restrictive format should
    // come first, such that we get any error as early as possible.

    return [
        new YdkeFormatStrategy(),
        new YdkFormatStrategy(),
        new OmegaFormatStrategy($repository),
        new NameFormatDecodeStrategy($repository)
    ];
}

function get_decoder(): FormatDecodeStrategy
{
    $decoder = new DecodeStrategyTester();

    foreach (get_decode_strategies() as $strategy)
        $decoder->register($strategy);

    return $decoder;
}

function get_image_url(ImageKey $key): string
{
    $url = getenv('CARD_IMAGE_URL');
    if ($url === false)
        throw new \Exception("image url is not defined");

    $extension = getenv('CARD_IMAGE_URL_EXT');
    if ($extension === false)
        throw new \Exception("image url extension is not defined");

    $name = $key->value();

    return "$url/$name.$extension";
}

function image_loader(ImageKey $key, int $type): Image
{
    try {
        $url = get_image_url($key);
        $image = Image::from_url($url, $type);
    }
    catch (\Exception $e) {
        $image = null;
    }

    if ($image === null)
        $image = MemoryImage::from_file(IMAGE_PLACEHOLDER_PATH);

    if ($image === null)
        throw new \Exception("failed to read image placeholder");

    return $image;
}

function get_image_cache(): ImageCache
{
    $type = ImageType::from_format(DECK_IMAGE_FORMAT);

    $extension = getenv('CARD_IMAGE_URL_EXT');
    if ($extension === false)
        throw new \Exception("image url extension is not defined");

    $cache = new ImageCache(
        CACHE_DIRECTORY,
        CACHE_SUBFOLDER_LENGTH
    );

    $cache->loader(\Closure::fromCallable(__NAMESPACE__ . '\\image_loader'));
    $cache->type($type, $extension);

    return $cache;
}
