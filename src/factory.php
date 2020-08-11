<?php

namespace Config;

use Config;
use Format\FormatDecoder;
use Format\FormatDecodeTester;
use Format\NeedsRepository;
use Game\Deck;
use Game\Repository\Repository;
use Game\Repository\SqliteRepository;
use Image\Image;
use Image\ImageCache;
use Image\ImageKey;
use Image\MemoryImage;
use Render\CellFactory;
use Render\Table;


abstract class CardRepository extends Repository
{
    private static ?Repository $instance = null;

    public static function get(): Repository
    {
        if (self::$instance === null) {
            $R = Config::get('repository');
            self::$instance = new SqliteRepository($R['path'], $R['options']);
        }

        return self::$instance;
    }
}


function get_repository(): Repository
{
    return CardRepository::get();
}

function create_repository_pdo(): \PDO
{
    $path = Config::get('repository')['path'];
    return new \PDO("sqlite:$path");
}


function create_decoder_from_class(string $class): FormatDecoder
{
    if (!is_subclass_of($class, FormatDecoder::class))
        throw new \Exception("$class is not a subclass of " . FormatDecoder::class);

    $args = [];
    if (is_subclass_of($class, NeedsRepository::class))
        $args[] = get_repository();

    return new $class(...$args);
}

function create_decoder(string $format_name): FormatDecoder
{
    $D = Config::get('formats')['decoders'];
    if (!isset($D[$format_name]))
        throw new \Exception("no decoder exists for format '$format_name'");

    $class = $D[$format_name];
    return create_decoder_from_class($class);
}

function create_decoders(string ...$format_names): \Generator
{
    foreach ($format_names as $format_name)
        yield $format_name => create_decoder($format_name);
}

function create_all_decoders(): \Generator
{
    foreach (Config::get('formats')['decoders'] as $format_name => $class)
        yield $format_name => create_decoder_from_class($class);
}

function create_decode_tester(string ...$format_names): FormatDecodeTester
{
    $tester = new FormatDecodeTester();

    $decoders = count($format_names) > 0
        ? create_decoders(...$format_names)
        : create_all_decoders();

    foreach ($decoders as $format_name => $decoder)
        $tester->register($format_name, $decoder);

    return $tester;
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
