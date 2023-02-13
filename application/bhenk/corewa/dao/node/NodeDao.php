<?php

namespace bhenk\corewa\dao\node;

use bhenk\corewa\dao\abc\AbstractDao;
use function file_get_contents;

class NodeDao extends AbstractDao {

    const TABLE_NAME = "tbl_node";

    public function getDataObjectName(): string {
        return NodeDo::class;
    }

    public function getTableName(): string {
        return self::TABLE_NAME;
    }

    public function getCreateTableStatement(): string {
        return file_get_contents(__DIR__ . "/sql/create_table.sql");
    }


}