<?php

require('../vendor/autoload.php');

use Image\Image;
use Image\ImageKey;


const PROGRESS_STEP = 1.0;
const FLUSH_FREQUENCY = 128;


$log = get_logger('cache');


$cache = Config\create_image_cache();
$cache->loader(function (ImageKey $key, int $type): ?Image {

    try {
        $url = Config\get_image_url($key);
        if ($url === null) {
            return null;
        }
        return Image::from_url($url, $type);
    }
    catch (\Exception $e) {
        return null;
    }
});


// $db = new \PDO('sqlite:' . Config::get('repository')['path']);
$db = Config\create_repository_pdo();

$cards = $db->query(" SELECT id FROM card ORDER BY CAST(id AS TEXT) ");
$count = intval($db->query(" SELECT COUNT(id) FROM card ")->fetchColumn());

$failed = [];

$total = 0;
$loaded = 0;
$last_percent = 0.0;

$log->info("loading cards...");

$cell_dimensions  = Config::get('cell');
$cache_original   = Config::get('cache')['cache_original'];
$resample_resized = Config::get('images')['resample_resized'];

foreach ($cards as $row) {
    $code = $row['id'];
    $total ++;

    $entry = $cache->load_with_loader($code);

    if ($entry === null) {
        $failed[] = $code;
        continue;
    }

    $loaded ++;

    $image = $entry->image();

    // resize if we're not caching originals
    if (!$cache_original)
        $image->resize(
            $cell_dimensions->width(),
            $cell_dimensions->height(),
            $resample_resized
        );

    // flush and clear the buffer frequently so that
    // we don't fill up our memory unnecessarily.
    if (count($cache) >= FLUSH_FREQUENCY) {
        $amount = $cache->flush();
        $cache->clear_buffer();
    }

    $percent = 100.0 * $total / $count;
    if ($percent > $last_percent + PROGRESS_STEP) {
        $percent = intval($percent / PROGRESS_STEP) * PROGRESS_STEP;
        $last_percent = $percent;

        $log->info("progress: $percent% ($total of $count)");
    }
}

$cache->flush(); // don't forget to flush the remaining cards!

$log->info("$loaded of $count cards loaded");

if ($loaded < $total) {
    $missing = $total - $loaded;
    $failed = implode(", ", $failed);
    $log->info("$missing cards failed to load: $failed");
}

$log->info("done.");
