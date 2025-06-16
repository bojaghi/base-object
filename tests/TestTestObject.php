<?php

namespace Bojaghi\BaseObject\Tests;

use Bojaghi\BaseObject\Tests\ObjectClasses\TestObject;
use WP_UnitTestCase;

class TestTestObject extends WP_UnitTestCase
{
    public function test_get_update()
    {
        // During the unit test, allow this user to have 'edit_posts' cap to update tags.
        wp_get_current_user()?->add_cap('edit_posts'); // Exactly this is what we need.

        $id = $this->factory()->post->create(
            [
                'post_title'   => 'Test',
                'post_content' => '',
                'meta_input'   => [
                    'my_field' => 'field test',
                ],
            ],
        );

        $tags = [
            $this->factory()->tag->create([
                'name' => 'Test Tag #1',
                'slug' => 'test-tag-1',
            ]),
            $this->factory()->tag->create([
                'name' => 'Test Tag #2',
                'slug' => 'test-tag-2',
            ]),
        ];

        $cat = $this->factory()->category->create([
            'name' => 'Test Cat 1',
            'slug' => 'test-cat-1',
        ]);

        wp_set_object_terms($id, $tags, 'post_tag');
        wp_set_object_terms($id, $cat, 'category');

        $dbCat  = get_the_terms($id, 'category');
        $dbTags = get_the_terms($id, 'post_tag');

        // Check the record is alright.
        $this->assertEquals('Test', get_post_field('post_title', $id));
        $this->assertEquals('', get_post_field('post_content', $id));
        $this->assertEquals('field test', get_post_meta($id, 'my_field', true));
        $this->assertEquals($tags[0], $dbTags[0]->term_id);
        $this->assertEquals($tags[1], $dbTags[1]->term_id);
        $this->assertEquals($cat, $dbCat[0]->term_id);

        $obj = TestObject::get($id);

        // Assertion
        $this->assertEquals($id, $obj->id);
        $this->assertEquals('Test', $obj->title);
        $this->assertEquals('', $obj->content);
        $this->assertEquals(get_post_meta($id, 'my_field', true), $obj->myField);
        $this->assertEquals($dbCat[0]->term_id, $obj->cat);
        $this->assertEquals([$dbTags[0]->slug, $dbTags[1]->slug], $obj->tags);

        // Try to update
        $obj->title   = 'Test 2';
        $obj->content = 'Content 2';
        $obj->myField = 'Field 2';
        $obj->cat     = 0; // Remove
        $obj->tags    = [$dbTags[1]->slug];
        $obj->save();

        $this->assertEquals($id, $obj->id);
        $this->assertEquals('Test 2', get_post_field('post_title', $id));
        $this->assertEquals('Content 2', get_post_field('post_content', $id));
        $this->assertEquals('Field 2', get_post_meta($id, 'my_field', true));

        // $dbCat is 'uncategorized'.
        $dbCat  = get_the_terms($id, 'category');
        $dbTags = get_the_terms($id, 'post_tag');

        $this->assertCount(1, $dbCat);
        $this->assertCount(1, $dbTags);;
        $this->assertEquals('uncategorized', $dbCat[0]->slug);
        $this->assertEquals($obj->tags[0], $dbTags[0]->slug);
    }
}
