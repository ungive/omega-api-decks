<?php

const ROOT_DIR = __DIR__ . '/../..';

const DB_FILE = ROOT_DIR . '/data/data.db';
const UPDATE_LOCK_FILE = ROOT_DIR . '/data/update.lock~';
const LOG_DIR = ROOT_DIR . '/logs';


# putenv("DATABASE_URL=http://chocolatecannoli.com/database/OmegaDB.cdb");
putenv("DATABASE_URL=http://127.0.0.1:8080/static/database.db");
putenv("WEBHOOK_UPDATE_TOKEN=7925587626b24ee84bf5a86c2634cfa982297e21f9ebfed8efa0f11b621794d3");
putenv("IMAGE_URL=https://storage.googleapis.com/ygoprodeck.com/pics_small");
putenv("IMAGE_EXT=jpg");
