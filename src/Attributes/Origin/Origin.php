<?php

namespace Bojaghi\BaseObject\Attributes\Origin;

use Bojaghi\BaseObject\QueryResult;

interface Origin
{
    public function delete(mixed $id, bool $force = false);

    public function get(mixed $id, array $fieldAttributes);

    public function query(array $args, array $fieldAttributes): QueryResult;

    public function set(mixed $id, array $data, array $fieldAttributes);
}
