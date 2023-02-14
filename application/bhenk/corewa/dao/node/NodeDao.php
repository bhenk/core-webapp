<?php

namespace bhenk\corewa\dao\node;

use bhenk\corewa\dao\abc\AbstractDao;
use bhenk\corewa\data\sql\MysqlConnector;
use bhenk\corewa\logging\Log;
use Exception;
use Throwable;
use function file_get_contents;
use function intval;
use function mysqli_report;

class NodeDao extends AbstractDao {

    const TABLE_NAME = "tbl_node";

    public function getDataObjectName(): string {
        return NodeDo::class;
    }

    public function getTableName(): string {
        return self::TABLE_NAME;
    }

    public function selectChildren(int $ID): array {
        $where_clause = "parent_id=" . $ID;
        return $this->selectWhere($where_clause);
    }

    public function selectGenerationNumbers(): array {
        $sql = file_get_contents(__DIR__ . "/sql/select_generation.sql");
        Log::debug("SELECT generation numbers", [$sql]);
        $selected = [];
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        try {
            $row_count = 0;
            $conn = MysqlConnector::get()->getConnector();
            $result = $conn->query($sql);
            while ($row = $result->fetch_assoc()) {
                $selected[$row["ID"]] = intval($row["generation_number"]);
                $row_count++;
            }
            Log::debug("SELECT generation numbers row count: " . $row_count);
            return $selected;
        } catch (Throwable $e) {
            throw new Exception("Could not select generation numbers", 205, $e);
        }
    }
}