<?php

namespace Bojaghi\BaseObject\Attributes\Field;

use Attribute;

#[Attribute]
abstract class Meta implements Field
{
    public string $objectType = '';
}
