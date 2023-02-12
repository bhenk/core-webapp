<?php

namespace bhenk\corewa\dao\node;

use bhenk\corewa\dao\abc\AbstractDao;
use bhenk\corewa\dao\abc\Entity;
use bhenk\corewa\data\sql\MysqlConnector;
use bhenk\corewa\logging\Log;
use Exception;
use function array_values;
use function file_get_contents;
use function str_replace;
use function var_dump;

class NodeDao extends AbstractDao {

    const TABLE_NAME = "tbl_node";

    function __construct() {}

    public function getTableName(): string {
        return self::TABLE_NAME;
    }

    public function getCreateTableStatement(): string {
        return file_get_contents(__DIR__ . "/sql/create_table.sql");
    }
}