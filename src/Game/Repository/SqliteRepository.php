<?php

namespace Game\Repository;

use Game\Card;
use Game\DeckType;


class SqliteRepository extends Repository
{
    private \PDO $db;

    public function __construct(NameMatchOptions $options)
    {
        parent::__construct($options);

        if (!file_exists(DB_FILE))
            throw new \Exception("database does not exist");

        $this->db = new \PDO('sqlite:' . DB_FILE);
        $this->db->sqliteCreateFunction('levenshtein', 'levenshtein', 2);
    }

    public function get_card_by_code(int $code): Card
    {
        $stmt = $this->db->prepare(<<<SQL

            SELECT
                id,
                type
            FROM card
            WHERE id = :code

        SQL);

        $stmt->bindValue(':code', $code);

        $stmt->execute();
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (count($rows) === 0)
            throw new \Exception("card not found: $code");

        return $this->row_to_card($rows[0]);
    }

    public function get_card_by_name(string $name): Card
    {
        $stmt = $this->db->prepare(<<<SQL

            SELECT
                id,
                type,
                LEVENSHTEIN(:query, name) AS dist,
                ABS(LENGTH(name) - LENGTH(:query)) AS diff,
                LENGTH(name) AS len
            FROM card
            WHERE match_name AND cluster = :cluster
                AND 1.0 * diff / len < 1.0 * :max_length_diff_per_letter
                AND 1.0 * dist / len < 1.0 * :max_errors_per_letter
            ORDER BY dist, diff, len
            LIMIT 1;

        SQL);

        $sanitized_name = \Db\sanitize_name($name);
        $name_cluster = \Db\name_cluster($sanitized_name);

        $stmt->bindValue(':query', $sanitized_name);
        $stmt->bindValue(':cluster', $name_cluster);
        $stmt->bindValue(':max_length_diff_per_letter', $this->options->max_length_diff_per_letter());
        $stmt->bindValue(':max_errors_per_letter', $this->options->max_errors_per_letter());

        $stmt->execute();
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (count($rows) === 0)
            throw new \Exception("card not found: $name");

        return $this->row_to_card($rows[0]);
    }

    private function row_to_card(array $row): Card
    {
        $id = intval($row['id']);

        switch ($row['type']) {
        case 'MAIN':  $type = DeckType::MAIN;  break;
        case 'EXTRA': $type = DeckType::EXTRA; break;
        default: assert(false, "unknown card type: " . $row['type']);
        }

        return new Card($id, $type);
    }

    // public function get_cards_by_code(int ...$codes): CardList
    // {
    //     $db = new \PDO('sqlite:' . DB_FILE);

    //     $code_values = implode(',', $codes);
    //     $stmt = $db->prepare(<<<SQL

    //         SELECT
    //             id,
    //             type
    //         FROM card
    //         WHERE id IN ($code_values)

    //     SQL);
    //     $stmt->execute();
    //     $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    //     $cards = new CardList();
    //     $codes = array_flip($codes);

    //     foreach ($rows as $row) {
    //         $card = $this->row_to_card($row);
    //         $offset = $codes[$card->code];
    //         unset($codes[$card->code]);
    //         $cards[$offset] = $card;
    //     }

    //     if (count($codes) > 0)
    //         throw new \Exception("card not found: " . array_key_first($codes));

    //     return $cards;
    // }

    // public function get_cards_by_name(string ...$names): CardList
    // {
    //     $cards = new CardList();

    //     if (count($names) !== 0)
    //         foreach ($names as $name)
    //             $cards[] = $this->get_card_by_name($name);

    //     return $cards;
    // }
}
