<?php

namespace Bojaghi\BaseObject\Attributes\Field;

use Attribute;
use InvalidArgumentException;
use WP_Term;

/**
 * 포스트 오브젝트와 관련되어 텀이 사용될 때 사용할 필드 어트리뷰트
 *
 * 이 어트리뷰트는 포스트 테이블의 레코드가 메인으로 오브젝트를 구성할 때,
 * 해당 레코드와 연관된 텀 정보를 연결할 때 사용합니다.
 *
 * 주지해야 할 사항입니다.
 * 이 어트리뷰트는 포스트가 어떤 텀을 선택했는지를 읽고 쓰기 위한 용도로 제작되었습니다.
 * 텀 자체를 편집하기 위한 용도로 만들어지지 않았습니다.
 *
 * 중요한 노트:
 * - 위계적인 택소노미를 사용할 때 term_id 필드를 사용하세요.
 * - 평행한 택소노미를 사용할 때 slug 필드를 사용하세요.
 * 이는 wp_insert_post() 의 'tax_input' 필드와 관련이 있습니다.
 */
#[Attribute]
class PostTerm implements Field
{
    public string $taxonomy;
    public bool   $single;
    public string $field;

    private static array $knownFields = [
        'all',
        'term_id',
        'name',
        'slug',
    ];

    public function __construct(string $taxonomy, bool $single = false, string $field = '')
    {
        if (!taxonomy_exists($taxonomy)) {
            throw new InvalidArgumentException("Unknown taxonomy: $taxonomy");;
        }

        if (!$field) {
            $field = is_taxonomy_hierarchical($taxonomy) ? 'term_id' : 'slug';
        }

        if (!in_array($field, self::$knownFields)) {
            throw new InvalidArgumentException("Unknown term field: $field");
        }

        $this->taxonomy = $taxonomy;
        $this->field    = $field;
        $this->single   = $single;
    }

    public function getOriginField(): string
    {
        return $this->taxonomy;
    }

    public function fromOriginValue($id): mixed
    {
        $rawValue = wp_get_object_terms($id, $this->taxonomy);

        if (is_array($rawValue) && count($rawValue)) {
            if ($this->single) {
                if ('all' === $this->field) {
                    return $rawValue[0];
                } else {
                    return $rawValue[0]->{$this->field};
                }
            } else {
                if ('all' === $this->field) {
                    return (array)$rawValue;
                } else {
                    return array_map(fn($term) => $term->{$this->field}, (array)$rawValue);
                }
            }
        } else {
            if ($this->single) {
                return '';
            } else {
                return [];
            }
        }
    }

    public function toOriginValue($id, $value): mixed
    {
        if ($value instanceof WP_Term) {
            /** @see wp_insert_post() */
            $value = is_taxonomy_hierarchical($value->taxonomy) ? $value->term_id : $value->slug;
        }

        return $value;
    }
}