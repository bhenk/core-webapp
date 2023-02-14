<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace bhenk\corewa\dao\abc;

use Stringable;

interface EntityInterface extends Stringable {

    public static function fromArray(array $arr): Entity;

    public function getID(): ?int;

    public function toArray(): array;

    public function clone(?int $ID): Entity;

    public function equals(Entity $other): bool;

    public function isSame(Entity $other): bool;

}