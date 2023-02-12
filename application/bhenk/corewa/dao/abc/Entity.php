<?php

namespace bhenk\corewa\dao\abc;

use ReflectionClass;
use function var_dump;

readonly class Entity implements EntityInterface {

    public static function fromArray(array $arr) : static {
        $rc = new ReflectionClass(static::class);
        return $rc->newInstanceArgs($arr);
    }

    function __construct(private ?int $id) {}

    public function getId(): ?int {
        return $this->id;
    }

    public function toArray(): array {
        $arr = [];
        $rc = new ReflectionClass($this);
        foreach ($rc->getProperties() as $prop) {
            $arr[] = $prop->getValue($this);
        }
        return $arr;
    }

    public function clone(?int $id) : Entity {
        $arr = $this->toArray();
        $arr[0] = $id;
        return static::fromArray($arr);
    }
}