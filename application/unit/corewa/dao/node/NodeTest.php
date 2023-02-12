<?php

namespace unit\corewa\dao\node;

use bhenk\corewa\dao\abc\Entity;
use bhenk\corewa\dao\node\Node;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertInstanceOf;
use function var_dump;

class NodeTest extends TestCase {

    public function testConstructor() {
        $node1 = new Node(1, 3, "my name", "my alias", "my nature");
        $node2 = Node::fromArray($node1->toArray());
        self::assertNotSame($node1, $node2);
        self::assertEquals("my name", $node2->getName());

        $arr = $node1->toArray();
        $arr[0] = 42;
        $node3 = Node::fromArray($arr);
        assertEquals(42, $node3->getId());

        $node4 = $node3->clone(88);
        assertEquals(88, $node4->getId());
        self::assertInstanceOf(Node::class, $node4);
    }

    public function testToArray() {
        $node = new Node(1, 3, "my name", "my alias", "my nature");
        $arr = $node->toArray();
        self::assertEquals([1, 3, "my name", "my alias", "my nature"], $arr);

        $entity = new Entity(5);
        assertEquals([5], $entity->toArray());
    }

    public function testFromArray() {
        $arr = [1, 3, "my name", "my alias", "my nature"];
        $node = Node::fromArray($arr);

        self::assertInstanceOf(Node::class, $node);
        assertEquals(1, $node->getId());
        assertEquals("my name", $node->getName());

        $entity = Entity::fromArray($arr);
        assertInstanceOf(Entity::class, $entity);
        assertEquals(1, $entity->getId());
    }

    public function testClone() {
        $node1 = new Node(1, 3, "my name", "my alias", "my nature");
        $node2 = $node1->clone(null);
        assertEquals(null, $node2->getId());
    }

}
