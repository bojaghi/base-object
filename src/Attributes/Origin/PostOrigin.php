<?php

namespace Bojaghi\BaseObject\Attributes\Origin;

use Attribute;
use Bojaghi\BaseObject\Attributes\Field\Field;
use Bojaghi\BaseObject\Attributes\Field\Post;
use Bojaghi\BaseObject\Attributes\Field\PostMeta;
use Bojaghi\BaseObject\Attributes\Field\PostTerm;
use Bojaghi\BaseObject\QueryResult;
use Bojaghi\BaseObject\Util;
use WP_Query;

#[Attribute]
class PostOrigin implements Origin
{
    protected array $defaults = [];

    public function __construct(protected string $post_type = 'post', string|array $defaults = '')
    {
        $this->defaults = wp_parse_args($defaults, [
            'ID'             => 0,
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'comment_status' => 'closed',
            'ping_status'    => 'closed',
        ]);

        if ($this->post_type) {
            $this->defaults['post_type'] = $this->post_type;
        }
    }

    public function delete(mixed $id, bool $force = false): void
    {
        if ($this->post_type && $this->post_type !== get_post_type($id)) {
            return;
        }

        wp_delete_post((int)$id, $force);
    }

    /**
     * @param int                  $id
     * @param array<string, Field> $fieldAttributes
     *
     * @return array|null
     */
    public function get(mixed $id, array $fieldAttributes): ?array
    {
        if (!$id) {
            return null;
        }

        // Note: this makes the post cached.
        $post = get_post($id);
        if (!$post || ($this->post_type && $this->post_type !== $post->post_type)) {
            return null;
        }

        return array_map(function ($field) use ($id) {
            // Note: we should be using the cached value.
            return $field->fromOriginValue($id);
        }, $fieldAttributes);
    }

    /**
     * @param array                $args
     * @param array<string, Field> $fieldAttributes
     *
     * @return QueryResult
     */
    public function query(array $args, array $fieldAttributes): QueryResult
    {
        $result = new QueryResult();

        // Do not allow querying other post_types.
        $args['post_type'] = $this->post_type;

        $query = new WP_Query($args);

        $result->items    = array_map(fn($p) => $this->get($p->ID, $fieldAttributes), $query->posts);
        $result->total    = $query->found_posts;
        $result->page     = $query->query_vars['paged'];
        $result->perPage  = $query->query_vars['posts_per_page'];
        $result->lastPage = $query->max_num_pages;

        return $result;
    }

    /**
     * @param mixed                $id
     * @param array                $data
     * @param array<string, Field> $fieldAttributes
     *
     * @return int
     */
    public function set(mixed $id, array $data, array $fieldAttributes): int
    {
        $postArr = $this->defaults;
        $metaArr = [];
        $termArr = [];

        if ($this->post_type) {
            $postArr['post_type'] = $this->post_type;
        }

        foreach ($fieldAttributes as $key => $field) {
            if (is_a($field, Post::class)) {
                $postArr[$field->getOriginField()] = $field->toOriginValue($id, $data[$key]);
            } elseif (is_a($field, PostMeta::class)) {
                $metaArr[$field->getOriginField()] = $field->toOriginValue($id, $data[$key]);
            } elseif (is_a($field, PostTerm::class)) {
                $termArr[$field->getOriginField()] = $field->toOriginValue($id, $data[$key]);
            }
        }

        if ($metaArr) {
            $postArr['meta_input'] = $metaArr;
        }

        foreach ($termArr as $tax => $terms) {
            if (!empty($terms)) {
                $postArr['tax_input'][$tax] = $terms;
            } else {
                wp_delete_object_term_relationships($id, $tax);
            }
        }

        return $postArr['ID'] > 0 ? wp_update_post($postArr) : wp_insert_post($postArr);
    }
}
