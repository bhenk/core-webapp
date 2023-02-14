<?php

namespace unit\corewa\dao\node;

use bhenk\corewa\dao\abc\Entity;
use bhenk\corewa\dao\node\NodeDo;
use bhenk\corewa\logging\ConsoleLoggerTrait;
use bhenk\corewa\logging\Log;
use bhenk\corewa\logging\LogAttribute;
use PHPUnit\Framework\TestCase;
use function get_class;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertInstanceOf;
use function PHPUnit\Framework\assertNotSame;
use function PHPUnit\Framework\assertTrue;

#[LogAttribute(false)]
class NodeDoTest extends TestCase {
    use ConsoleLoggerTrait;

    public function testConstructor() {
        $node1 = new NodeDo(1, 3, "my name", "my alias", "my nature");
        $node2 = NodeDo::fromArray($node1->toArray());
        assertNotSame($node1, $node2);
        assertEquals("my name", $node2->getName());

        $arr = $node1->toArray();
        $arr["ID"] = 42;
        $node3 = NodeDo::fromArray($arr);
        assertEquals(42, $node3->getID());

        $node4 = $node3->clone(88);
        assertEquals(88, $node4->getID());
        assertInstanceOf(NodeDo::class, $node4);
    }

    public function testToArray() {
        $node = new NodeDo(1, 3, "my name", "my alias", "my nature");
        $arr = $node->toArray();
        assertEquals(["ID" => 1, "parent_id" => 3, "name" => "my name", "alias" => "my alias",
            "nature" => "my nature", "public" => true, "estimate" => 0.0], $arr);

        $entity = new Entity(5);
        assertEquals(["ID" => 5], $entity->toArray());
    }

    public function testFromArray() {
        $arr = [1, 3, "my name", "my alias", "my nature", false];
        $node = NodeDo::fromArray($arr);

        assertInstanceOf(NodeDo::class, $node);
        assertEquals(1, $node->getID());
        assertEquals("my name", $node->getName());

        $entity = Entity::fromArray($arr);
        assertInstanceOf(Entity::class, $entity);
        assertEquals(1, $entity->getID());
    }

    public function testClone() {
        $node1 = new NodeDo(1, 3, "my name", "my alias", "my nature");
        $node2 = $node1->clone(null);
        assertEquals(null, $node2->getID());
        assertTrue($node1->equals($node2));
    }

    public function test__toString() {
        $node1 = new NodeDo(1, 3, "my name", "my alias", "my nature");
        Log::debug("A NodeDo __toString():", [$node1]);
        self::assertStringStartsWith(get_class($node1), $node1->__toString());
    }

}
