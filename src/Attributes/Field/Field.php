<?php

namespace Bojaghi\BaseObject\Attributes\Field;

interface Field
{
    /**
     * 데이터 소스로부터 값을 가져올 때 호출
     *
     * @param $id
     *
     * @return mixed
     */
    public function fromOriginValue($id): mixed;

    /**
     * Origin 어트리뷰트와 값을 주고 받을 때 사용되는 필드 이름을 리턴
     *
     * @return string
     */
    public function getOriginField(): string;

    /**
     * 오브젝트로부터 데이터소스로 값을 넘길 때 호출
     *
     * @param $id
     * @param $value
     *
     * @return mixed
     */
    public function toOriginValue($id, $value): mixed;
}
