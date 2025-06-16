<?php

namespace Bojaghi\BaseObject\Attributes\Field;

use Attribute;
use InvalidArgumentException;

#[Attribute]
class Post implements Field
{
    private static array $knownFields = [
        'ID',
        'post_author',
        'post_date',
        'post_date_gmt',
        'post_content',
        'post_title',
        'post_excerpt',
        'post_status',
        'comment_status',
        'ping_status',
        'post_password',
        'post_name',
        'to_ping',
        'pinged',
        'post_modified',
        'post_modified_gmt',
        'post_content_filtered',
        'post_parent',
        'guid',
        'menu_order',
        'post_type',
        'post_mime_type',
        'comment_count',
    ];
    public string $field;

    public function __construct(string $field)
    {
        if (!in_array($field, self::$knownFields)) {
            throw new InvalidArgumentException("Unknown post field: $field");
        }

        $this->field = $field;
    }

    public function fromOriginValue($id): mixed
    {
        return match ($this->field) {
            /** This filter is documented in wp-includes/post-template.php */
            'post_content' => apply_filters('the_content', get_post_field('post_content', $id)),
            'post_title'   => apply_filters('the_title', get_post_field('post_title', $id), $id),
            default        => get_post_field($this->field, $id),
        };
    }

    public function getOriginField(): string
    {
        return $this->field;
    }

    public function toOriginValue($id, $value): mixed
    {
        return $value;
    }
}
