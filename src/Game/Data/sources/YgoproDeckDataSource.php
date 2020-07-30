<?php

namespace Game\Data;


class YgoproDeckDataSource extends DataSource
{
    function fetch_cards_by_ids(array $codes): CardList
    {
        $query = http_build_query([ 'id' => implode(',', $codes) ]);
        var_dump($query);

        return null;
    }

    function fetch_cards_by_names(array $names): CardList
    {
        throw new Exception("not implemented");
    }
}
