<?php

namespace Game\Data;


class SqliteRepository extends Repository
{
    public function get_cards_by_code(int ...$codes): DataCardList
    {
        throw new \Exception("not implemented");
    }

    public function get_cards_by_name(string ...$codes): DataCardList
    {
        $db = new \PDO('sqlite:' . DB_FILE);
        $db->sqliteCreateFunction('levenshtein', 'levenshtein', 2);

        $select_stmt = $db->prepare(<<<SQL
            WITH
            query AS (
                SELECT "kuriboh" AS name UNION
                SELECT "dark_magician" AS name
            )
            SELECT id, name, type
            FROM card
            INNER JOIN query USING(name)
            WHERE match_name;
        SQL);
        $select_stmt->execute();
        $rows = $select_stmt->fetchAll(\PDO::FETCH_ASSOC);

        throw new \Exception("not implemented");
    }
}
