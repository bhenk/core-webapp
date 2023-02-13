<?php

namespace unit\corewa\dao\node;

use bhenk\corewa\dao\abc\Entity;
use bhenk\corewa\dao\node\NodeDao;
use bhenk\corewa\dao\node\NodeDo;
use bhenk\corewa\logging\ConsoleLoggerTrait;
use bhenk\corewa\logging\Log;
use bhenk\corewa\logging\LogAttribute;
use Exception;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertInstanceOf;
use function PHPUnit\Framework\assertTrue;

#[LogAttribute(true)]
class NodeDaoTest extends TestCase {
    use ConsoleLoggerTrait;

    #[LogAttribute(false)]
    public function testCreateTableDrop(): void {
        $dao = new NodeDao();
        $result = $dao->createTable(true);
        assertEquals(2, $result);
    }

    #[LogAttribute(false)]
    public function testCreateTable(): void {
        $dao = new NodeDao();
        $result = $dao->createTable(false);
        assertEquals(1, $result);
    }

    #[LogAttribute(false)]
    public function testInsert(): void {
        $node = new NodeDo(null, null, "what sunday", "by alias", "zondag", false);
        $dao = new NodeDao();
        /** @var NodeDo $newNode */
        $newNode = $dao->insert($node);
        assertInstanceOf(NodeDo::class, $newNode);
        assertTrue($node->equals($newNode));
        assertFalse($node->isSame($newNode));
    }

    #[LogAttribute(false)]
    public function testInsertBatch() {
        $batch = [
            new NodeDo(null, null, "name 1", "alias 1", "nature 1", false),
            new NodeDo(null, 1, "name 2", "alias 2", "nature 2", true),
            new NodeDo(null, 2, "name 3", "alias 3", "nature 3", false),
        ];
        $dao = new NodeDao();
        $batch2 = $dao->insertBatch($batch);
        assertEquals(3, count($batch2));
        $t = 0;
        /** @var NodeDo $node */
        foreach ($batch2 as $node) {
            assertFalse($node->isSame($batch[$t]));
            assertTrue($node->equals($batch[$t++]));
        }
    }

    #[LogAttribute(false)]
    public function testInsertBatchWithError() {
        $batch = [
            new NodeDo(null, null, "name 1", "alias 1", "nature 1", false),
            new NodeDao(),
            new NodeDo(null, 2, "name 3", "alias 3", "nature 3", false),
        ];
        $dao = new NodeDao();
        $thrown = false;
        try {
            $dao->insertBatch($batch);
        } catch (Exception $e) {
            Log::debug("test exception: ", [$e]);
            $thrown = true;
        }
        assertTrue($thrown);
    }

    #[LogAttribute(false)]
    public function testInsertBatchWithError2() {
        $batch = [
            new NodeDo(null, null, "name 1", "alias 1", "nature 1", false),
            new Entity(12),
            new NodeDo(null, 2, "name 3", "alias 3", "nature 3", false),
        ];
        $dao = new NodeDao();
        $thrown = false;
        try {
            $dao->insertBatch($batch);
        } catch (Exception $e) {
            Log::debug("test exception: ", [$e]);
            $thrown = true;
        }
        assertTrue($thrown);
    }

    public function testUpdate(): void {
        $node = new NodeDo(null, 12, "node name", "node alias", "node nature");
        $dao = new NodeDao();
        /** @var NodeDo $node2 */
        $node2 = $dao->insert($node);

        $node2->setParentId(42);
        $node2->setNature(null);
        $result = $dao->update($node2);

        assertTrue($result);
    }


}
