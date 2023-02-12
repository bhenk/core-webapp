<?php

namespace bhenk\corewa\dao\abc;

use ReflectionClass;
use function array_values;
use function var_dump;

class Entity implements EntityInterface {

    public static function fromArray(array $arr) : static {
        $rc = new ReflectionClass(static::class);
        return $rc->newInstanceArgs(array_values($arr));
    }

    function __construct(private readonly ?int $id) {}

    public function getId(): ?int {
        return $this->id;
    }

    public function toArray(): array {
        $arr = [];
        $rc = new ReflectionClass($this);
        foreach ($rc->getProperties() as $prop) {
            $arr[$prop->getName()] = $prop->getValue($this);
        }
        return $arr;
    }

    public function clone(?int $id) : Entity {
        $arr = $this->toArray();
        $arr[0] = $id;
        return static::fromArray($arr);
    }

}