<?php

namespace bhenk\corewa\dao\abc;

interface EntityInterface {

    public static function fromArray(array $arr): Entity;

    public function getID(): ?int;

    public function toArray(): array;

    public function clone(?int $ID): Entity;

}