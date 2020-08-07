<?php

if (getenv('DATA_DIR') === false)
    putenv('DATA_DIR=/Users/jonas/Apache/data');

if (getenv('DATABASE_URL') === false)
    putenv("DATABASE_URL=file:///Users/jonas/Apache/deck-list/database.db");

if (getenv('CARD_IMAGE_URL') === false)
    putenv('CARD_IMAGE_URL=file:///Volumes/Extension/Omega/pics_small');

if (getenv('CARD_IMAGE_URL_EXT') === false)
    putenv('CARD_IMAGE_URL_EXT=jpg');


define('DATA_DIR', getenv('DATA_DIR'));


const ROOT_DIR = __DIR__  . '/../..';
const PUBLIC_DIR = ROOT_DIR . '/public';
const STATIC_DIR = PUBLIC_DIR . '/static';


#  --- put this in the config

# TODO: using constants isn't very elegant.

const CACHE_DIRECTORY = DATA_DIR . '/cache';
const CACHE_SUBFOLDER_LENGTH = 2;

const REPOSITORY_TYPE = 'SqliteRepository';
const MAX_LENGTH_DIFF_PER_LETTER = 2 / 5;
const MAX_ERRORS_PER_LETTER = 1 / 5;

const RESAMPLE_CARDS = true;

// caches original card image sizes.
// doing this results in resizing each card image on _every_ request, which
// can add up to a couple hundred milliseconds of additional execution time.
const CACHE_ORIGINAL = false;

const CARD_WIDTH  = 81;
const CARD_HEIGHT = 118;

const BACKGROUND_IMAGE = STATIC_DIR . '/background.jpg';
const IMAGE_PLACEHOLDER_PATH = STATIC_DIR . '/unknown.jpg';

const DECK_IMAGE_FORMAT = 'jpg';

# ---


const DB_FILE = DATA_DIR . '/card.db';
const UPDATE_LOCK_FILE = DATA_DIR . '/update.lock~';
