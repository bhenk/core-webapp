<?php

namespace unit\corewa\dao\node;

use bhenk\corewa\dao\node\Node;
use bhenk\corewa\dao\node\NodeDao;
use bhenk\corewa\logging\ConsoleLoggerTrait;
use bhenk\corewa\logging\LogAttribute;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertInstanceOf;
use function PHPUnit\Framework\assertNotNull;
use function PHPUnit\Framework\assertTrue;

#[LogAttribute(true)]
class NodeDaoTest extends TestCase {
    use ConsoleLoggerTrait;

    #[LogAttribute(true)]
    public function testCreateTableDrop(): void {
        $dao = new NodeDao();
        $result = $dao->createTable(true);
        assertEquals(2, $result);
    }

    #[LogAttribute(true)]
    public function testCreateTable(): void {
        $dao = new NodeDao();
        $result = $dao->createTable(false);
        assertEquals(1, $result);
    }

    public function testInsert(): void {
        $node = new Node(null, null, "what sunday", "by alias", "zondag");
        $dao = new NodeDao();
        /** @var Node $newNode */
        $newNode = $dao->insert($node);
        assertInstanceOf(Node::class, $newNode);
        assertEquals("what sunday", $newNode->getName());
        assertNotNull($newNode->getId());
    }

    public function testUpdate() : void {
        $node = new Node(null, 12, "node name", "node alias", "node nature");
        $dao = new NodeDao();
        /** @var Node $node2 */
        $node2 = $dao->insert($node);

        $node2->setParentId(42);
        $node2->setNature(null);
        $result = $dao->update($node2);

        assertTrue($result);
    }


}
