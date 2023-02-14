<?php

namespace unit\corewa\dao\user;

use bhenk\corewa\dao\user\UserDao;
use bhenk\corewa\dao\user\UserDo;
use bhenk\corewa\logging\ConsoleLoggerTrait;
use bhenk\corewa\logging\LogAttribute;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertGreaterThanOrEqual;
use function PHPUnit\Framework\assertStringStartsWith;

#[LogAttribute(false)]
class UserDaoTest extends TestCase {
    use ConsoleLoggerTrait;

    #[LogAttribute(false)]
    public function testCreateTableStatement() {
        $dao = new UserDao();
        $sql = $dao->getCreateTableStatement();
        assertStringStartsWith("CREATE", $sql);
    }

    public function testDropCreateTable() {
        $dao = new UserDao();
        $r = $dao->createTable(true);
        assertGreaterThanOrEqual(1, $r);
    }

    public function testInsertBatch() {
        $batch = [
            new UserDo(null, "Bob", null, "Dylan", "bob@email.com"),
            new UserDo(null, "Elvis", null, "Presley", "elvis@email.com"),
        ];
        $dao = new UserDao();
        $batch2 = $dao->insertBatch($batch);
        assertEquals("Bob", $batch2[0]->getFirstName());
        assertEquals("Presley", $batch2[1]->getLastName());
    }

}
