<?php

namespace Bojaghi\BaseObject\Tests\ObjectClasses;

use Bojaghi\BaseObject\Attributes\Field\Post;
use Bojaghi\BaseObject\Attributes\Field\PostMeta;
use Bojaghi\BaseObject\Attributes\Field\PostTerm;
use Bojaghi\BaseObject\Attributes\Origin\PostOrigin;
use Bojaghi\BaseObject\Attributes\Primary;
use Bojaghi\BaseObject\BaseObject;

#[PostOrigin]
class TestObject extends BaseObject
{
    #[Primary]
    #[Post('ID')]
    public int $id;

    #[Post('post_title')]
    public string $title;

    #[Post('post_content')]
    public string $content;

    #[PostMeta('my_field', single: true)]
    public string $myField;

    #[PostTerm('category', single: true)]
    public int $cat;

    #[PostTerm('post_tag', single: false)]
    public array $tags;
}
