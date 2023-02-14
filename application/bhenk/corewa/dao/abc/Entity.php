<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace bhenk\corewa\dao\abc;

use ReflectionClass;
use Stringable;
use function array_slice;
use function array_values;
use function get_class;

class Entity implements Stringable, EntityInterface {

    function __construct(private readonly ?int $ID) {}

    public static function fromArray(array $arr): static {
        $rc = new ReflectionClass(static::class);
        return $rc->newInstanceArgs(array_values($arr));
    }

    public function getID(): ?int {
        return $this->ID;
    }

    public function toArray(): array {
        $arr = [];
        $rc = new ReflectionClass($this);
        foreach ($rc->getProperties() as $prop) {
            $val = $prop->getValue($this);
            if ($prop->getType()->getName() == "bool") {
                $val = $val ? 1 : 0;
            }
            $arr[$prop->getName()] = $val;
        }
        return $arr;
    }

    public function clone(?int $ID): Entity {
        $arr = $this->toArray();
        $arr["ID"] = $ID;
        return static::fromArray($arr);
    }

    public function equals(Entity $other): bool {
        return get_class($this) === get_class($other) and
            array_slice($this->toArray(), 1) === array_slice($other->toArray(), 1);
    }

    public function isSame(Entity $other): bool {
        return $this->equals($other) and
            $this->getID() === $other->getID();
    }

    public function __toString(): string {
        $s = get_class($this);
        $rc = new ReflectionClass($this);
        $s .= PHP_EOL;
        foreach ($rc->getProperties() as $prop) {
            $val = $prop->getValue($this);
            if ($prop->getType()->getName() == "string")
                $val = "'" . $val . "'";
            $s .= "\t" . $prop->getName() . " ("
                . $prop->getType()->getName() . ") -> " . $val . PHP_EOL;
        }
        return $s;
    }

}