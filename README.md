# Base Object

포스트와 포스트 메타, 또는 워드프레스 오브젝트/메타 구조등 여러 데이터 소스를 단일 오브젝트로 추상화아여
보다 편리하게 읽고 쓰기를 구현할 수 있게 해주는 패키지 입니다.

## 설치

`compser require bojaghi/base-object`

## 기본개념

- 오브젝트를 만들 때는 `BaseObject`를 상속받아야 합니다.
- 클래스, 그리고 클래스 프로퍼티는 반드시 어트리뷰트를 사용해야 합니다.
- 속성값으로 사용할 프로퍼티는 반드시 public 이어야 합니다.

### 데이터 소스

원래 데이터를 가져오려는 근원지를 말합니다. 
워드프레스의 포스트-메타-텀 테이블이 될 수 있고, 커스텀 테이블이 될 수도 있습니다.

### 어트리뷰트

`BaseObject`를 위한 어트리뷰트는 3종류가 있습니다.

1. Primary
2. Origin
3. Field

#### Primary 어트리뷰트

'Primary' (프라이머리) 어트리뷰트는 단순합니다. 해당 프로퍼티가 오브젝트의 식별자임을 선언합니다.
식별자가 없으면 읽고 쓰기 동작을 정상적으로 할 수 없기 때문에 반드시 필요합니다.
단, 편의를 위해 만약 이 어트리뷰트를 사용하지 않아도 'id' 프로퍼티를 인식할 수 있으면 'id'가 프라이머라라고 간주합니다. 

#### Origin 어트리뷰트

'Origin' (오리진) 어트리뷰트는 클래스 선언부 위에서 사용합니다.
사실 `BaseObject` 자체는 추상화되어 어떻게 읽고 쓰기를 해야 할지 알지 못합니다.
실제로 역할을 하는 것은 이 오리진입니다. 오리진에 명시된 데이터 소스 정보를 이용해 실제 읽고 쓰기는 여기에 구현되어 있습니다.

예를 들어, 커스텀 포스트의 일부 필드와 메타 필드중 일부를 엮어 하나의 오브젝트로 간주하려고 합니다.
그러면 이 오브텍트는 포스트로부터 유래한 것입니다.
그러므로 읽고 쓰기는 사실 포스트의 ID를 식별자로 하며, 워드프레스에서 읽고 쓰기를 위해 만든 API를 사용하는 것이 적절합니다.
실제로 `PostOrigin` 어트리뷰트는 읽고 쓰기를 위해 `wp_insert_post`, `wp_update_post` 등의
워드프레스의 함수를 사용하여 포스트/메타 테이블을 읽고 씁니다.

오리진 어트리뷰트는 읽고 쓰기를 위해 `BaseObject`와 값을 교환합니다.
이 때 내부적으로 키-값 연관배열을 사용하도록 약속되었습니다.
키는 오브젝트에서 정의한 프로퍼티 변수 이름을 사용합니다.

#### Field 어트리뷰트

읽고 쓰기가 일어날 때 각 프로퍼티가 데이터 소스의 어떤 필드와 대응되는지를 지정합니다.
또한 필드의 이름, 값을 추상화하는 역할도 합니다.

오리진 어트리뷰트가 객체와 값을 주고 받을 때, 데이터 소스에서 읽어온 원래의 키나 값이
그대로 매핑되라는 보장이 없습니다. 키-값을 주고 받을 때 값을 적절히 통제하는 역할을 합니다.

## 예시

```php

use Bojaghi\BaseObjects\Attributes\Origin\PostOrigin;
use Bojaghi\BaseObjects\Attributes\Field\Post;
use Bojaghi\BaseObjects\Attributes\Field\PostMeta;
use Bojaghi\BaseObjects\BaseObject;

#[PostOrigin(post_type: 'post')]
class MyObject extends BaseObject 
{
    // 포스트 ID 와 $id 필드를 연결하고, 이 필드가 PK임을 선언합니다.
    #[Primary]
    #[Post('ID')]
    public int $id;
    
    // $title 프로퍼티는 post.post_title과 연결됩니다.
    #[Post('post_title')]
    public string $title;
    
    // 포스트의 메타값을 씁니다. single=true 이어야 스칼라 값을 이용할 수 있습니다.
    #[PostMeta(key: 'my_field', single: true)]
    public string $myField;
    
    // 위계적인 택소노미는, 예를 들어 카테고리, 텀 ID를 쓰는 것이 좋습니다. 여러 텀을 쓰지 않고 딱 1개만 사용합니다. (single = true)
    #[PostTerm(taxonomy: 'my_cat', single: true, field: 'term_id')]
    public int $myCat;
     
    // 평평한 택소노미는, 예를 들어 태그, 슬러그를 써도 괜찮습니다.
    // 여러 태그 슬러그를 배열로 받습니다.
    #[PostTerm(taxonomy: 'my_tag', single: false, field: 'slug')]
    public array $tags;
}
```

이렇게 하면 `BaseObject` 상속을 통해 `get()`, `delete()` 메소드를 사용할 수 있습니다.
