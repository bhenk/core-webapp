<?php

namespace bhenk\corewa\dao\abc;

use bhenk\corewa\data\sql\MysqlConnector;
use bhenk\corewa\logging\Log;
use Exception;
use ReflectionClass;
use Throwable;
use function array_slice;
use function array_values;
use function mysqli_report;
use function rtrim;

abstract class AbstractDao {

    public abstract function getDataObjectName(): string;

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
        } catch (Throwable $e) {
            throw new Exception("Could not create table " . $this->getTableName(), 200, $e);
        }
    }

    public function insert(Entity $entity): Entity {
        return $this->insertBatch([$entity])[0];
    }

    public function update(Entity $entity): bool {
        return $this->updateBatch([$entity]);
    }

    public function insertBatch(array $entity_array): array {
        $sql = $this->getInsertStatement();
        Log::debug($sql);
        $new_entities = [];
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        try {
            $conn = MysqlConnector::get()->getConnector();
            $stmt = $conn->prepare($sql);
            /** @var Entity $entity */
            foreach ($entity_array as $entity) {
                $arr = $entity->toArray();
                $result = $stmt->execute(array_slice(array_values($arr), 1));
                if ($result) {
                    $ID = $stmt->insert_id;
                    $new_entities[] = $entity->clone($ID);
                    Log::debug("Inserted " . $entity::class . ", ID = " . $ID);
                } else {
                    $msg = "Could not insert " . $this->getDataObjectName();
                    Log::error($msg, [$entity]);
                    throw new Exception($msg);
                }
            }
            return $new_entities;
        } catch (Throwable $e) {
            throw new Exception("Could not insert Entity", 201, $e);
        }
    }

    public function updateBatch(array $entity_array): bool {
        $sql = $this->getUpdateStatement();
        Log::debug($sql);
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        try {
            $conn = MysqlConnector::get()->getConnector();
            $stmt = $conn->prepare($sql);
            /** @var Entity $entity */
            foreach ($entity_array as $entity) {
                $arr = $entity->toArray();
                $update = array_slice(array_values($arr), 1);
                $update[] = $entity->getID();
                $result = $stmt->execute($update);
                if (!$result) {
                    $msg = "Could not update " . $this->getDataObjectName();
                    Log::error($msg, [$entity]);
                    throw new Exception($msg);
                }
            }
            $stmt->close();
            return true;
        } catch (Throwable $e) {
            throw new Exception("Could not update Entity", 202, $e);
        }
    }

    private function getInsertStatement(): string {
        // INSERT INTO tbl_node (parent_id, name, alias, nature) VALUES (?, ?, ?, ?)
        $s1 = /** @lang text */
            "INSERT INTO " . $this->getTableName() . " (";
        $s2 = ") VALUES (";
        foreach ((new ReflectionClass($this->getDataObjectName()))->getProperties() as $prop) {
            $name = $prop->getName();
            if ($name != "ID") {
                $s1 .= $name . ", ";
                $s2 .= "?, ";
            }
        }
        return rtrim($s1, ", ") . rtrim($s2, ", ") . ")";
    }

    private function getUpdateStatement(): string {
        // UPDATE tbl_node SET parent_id=?, name=?, alias=?, nature=?, public=? WHERE ID=?
        $s1 = /** @lang text */
            "UPDATE "
            . $this->getTableName()
            . " SET ";
        foreach ((new ReflectionClass($this->getDataObjectName()))->getProperties() as $prop) {
            $name = $prop->getName();
            if ($name != "ID") {
                $s1 .= $prop->getName() . "=?, ";
            }
        }
        return rtrim($s1, ", ") . " WHERE ID=?";
    }

}