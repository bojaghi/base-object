<?php

namespace Bojaghi\BaseObject\Attributes\Field;

use Attribute;

#[Attribute]
class PostMeta extends Meta
{
    public function __construct(public string $key, public bool $single = false)
    {
        $this->objectType = 'post';
    }

    public function fromOriginValue($id): mixed
    {
        return get_post_meta($id, $this->key, $this->single);
    }

    public function getOriginField(): string
    {
        return $this->key;
    }

    public function toOriginValue($id, $value): mixed
    {
        return $value;
    }
}
