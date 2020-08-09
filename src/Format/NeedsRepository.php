<?php

namespace Format;

use Game\Repository\Repository;


abstract class NeedsRepository
{
    protected Repository $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }
}
