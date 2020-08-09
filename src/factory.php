<?php

namespace Config;

use Config;
use Format\FormatDecoder;
use Format\FormatDecodeTester;
use Format\NameFormatDecoder;
use Format\OmegaFormatConverter;
use Format\YdkeFormatConverter;
use Format\YdkFormatConverter;
use Game\Deck;
use Game\Repository\Repository;
use Game\Repository\SqliteRepository;
use Image\Image;
use Image\ImageCache;
use Image\ImageKey;
use Image\MemoryImage;
use Render\CellFactory;
use Render\Table;


function create_repository(): Repository
{
    $R = \Config::get('repository');
    return new SqliteRepository($R['path'], $R['options']);
}

function create_repository_pdo(): \PDO
{
    $path = Config::get('repository')['path'];
    return new \PDO("sqlite:$path");
}

function get_decode_strategies(): array
{
    $repository = create_repository();

    // the ordering here matters. most restrictive format should
    // come first, such that we get any error as early as possible.

    return [
        new YdkFormatConverter(),
        new YdkeFormatConverter(),
        new OmegaFormatConverter($repository),
        new NameFormatDecoder($repository)
    ];
}

function create_decoder(): FormatDecoder
{
    $decoder = new FormatDecodeTester();

    foreach (get_decode_strategies() as $strategy)
        $decoder->register($strategy);

    return $decoder;
}

function get_image_url(ImageKey $key): string
{
    $url = Config::get_env('CARD_IMAGE_URL');
    $extension = Config::get_env('CARD_IMAGE_URL_EXT');

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

    if ($image === null) {
        $placeholder = Config::get('images')['placeholder'];
        $image = MemoryImage::from_file($placeholder);
    }

    if ($image === null)
        throw new \Exception("failed to read image placeholder");

    return $image;
}

function create_image_cache(): ImageCache
{
    $type = Config::get('output')['image_type'];
    $extension = Config::get_env('CARD_IMAGE_URL_EXT');

    $cache = new ImageCache(
        Config::get('cache')['directory'],
        Config::get('cache')['subfolder_length']
    );

    $loader = \Closure::fromCallable(__NAMESPACE__ . '\\image_loader');

    $cache->loader($loader);
    $cache->type($type, $extension);

    return $cache;
}

function create_table(string $name): Table
{
    $tables = Config::get('tables');

    if (!isset($tables[$name]))
        throw new \Exception("table $name is not defined in configuration");

    $cell_dimensions = Config::get('cell');

    $cell_factory = new CellFactory(
        $cell_dimensions->width(),
        $cell_dimensions->height()
    );

    $T = $tables[$name];

    $table = new Table($T['width'], $T['height'], $cell_factory);

    $table->layout($T['layout']['primary'], $T['layout']['secondary']);
    $table->overlap($T['overlap']);

    $table->root($T['root']->x(), $T['root']->y());
    $table->spacing($T['spacing']->horizontal(), $T['spacing']->vertical());

    return $table;
}

function create_deck_table(string $name, Deck $deck): Table
{
    $table = create_table($name);

    foreach ($deck->cards() as $card)
        $table->push($card);

    return $table;
}
