<?php

namespace bhenk\corewa\dao\abc;

use bhenk\corewa\data\sql\MysqlConnector;
use bhenk\corewa\logging\Log;
use Exception;
use function array_slice;
use function array_values;
use function count;
use function mysqli_report;
use function rtrim;
use function str_repeat;

abstract class AbstractDao {

    public abstract function getTableName(): string;

    public abstract function getCreateTableStatement(): string;

    public function createTable(bool $drop = false): int {
        $query = $drop ?
            /** @lang text */
            "DROP TABLE IF EXISTS `"
            . $this->getTableName()
            . "`;" . PHP_EOL
            : "";
        $query .= $this->getCreateTableStatement();
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        try {
            $conn = MysqlConnector::get()->getConnector();
            $result = $conn->multi_query($query);
            $result += $conn->next_result();
            Log::info("Executed statements: " . $result, [$query]);
            return $result;
        } catch (Exception $e) {
            throw new Exception("Could not create table " . $this->getTableName(), 200, $e);
        }
    }

    public function insert(Entity $entity): Entity {
        // INSERT INTO tbl_node (parent_id, name, alias, nature) VALUES (? ,? ,? ,?)
        $arr = $entity->toArray();
        $sql = /** @lang text */
            "INSERT INTO "
            . $this->getTableName()
            . " ("
            . implode(", ", array_slice(array_keys($arr), 1))      //"parent_id, name, alias, nature"
            . ") VALUES ("
            . rtrim(str_repeat("? ,", count($arr) - 1), ", ")  //"?, ?, ?, ?"
            . ")";
        Log::debug($sql);
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        try {
            $conn = MysqlConnector::get()->getConnector();
            $stmt = $conn->prepare($sql);
            $result = $stmt->execute(array_slice(array_values($arr), 1));
            $arr["ID"] = $stmt->insert_id;
            $stmt->close();
            $new_entity = $entity::fromArray($arr);
            if ($result) {
                Log::debug("Inserted " . $entity::class . ", ID = " . $new_entity->getId());
            } else {
                Log::error("Could not insert " . $entity::class . ", ID = " . $arr["ID"]);
            }
            return $new_entity;
        } catch (Exception $e) {
            throw new Exception("Could not insert Entity", 201, $e);
        }
    }

    public function update(Entity $entity): bool {
        // "UPDATE Table_name SET username=?, password=? WHERE email=?"
        $arr = $entity->toArray();
        $set = implode("=?, ", array_slice(array_keys($arr), 1)) . "=? WHERE ID=?";
        $sql = /** @lang text */
            "UPDATE "
            . $this->getTableName()
            . " SET "
            . $set;
        Log::debug($sql);
        $update = array_slice(array_values($arr), 1);
        $update[] = $entity->getId();
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        try {
            $conn = MysqlConnector::get()->getConnector();
            $stmt = $conn->prepare($sql);
            $result = $stmt->execute($update);
            $stmt->close();
            if ($result) {
                Log::debug("Updated " . $entity::class . ", ID = " . $entity->getId());
            } else {
                Log::error("Could not update " . $entity::class . ", ID = " . $entity->getId());
            }
            return $result;
        } catch (Exception $e) {
            throw new Exception("Could not update Entity", 202, $e);
        }
    }

}