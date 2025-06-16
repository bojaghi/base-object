<?php

namespace Bojaghi\BaseObject;

class QueryResult
{
    public array $items = [];

    public int $total = -1;

    public int $page = -1;

    public int $perPage = -1;

    public int $lastPage = -1;
}
