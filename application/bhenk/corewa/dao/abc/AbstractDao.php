<?php

namespace bhenk\corewa\dao\abc;

use bhenk\corewa\data\sql\MysqlConnector;
use bhenk\corewa\logging\Log;
use Exception;
use ReflectionClass;
use ReflectionException;
use Throwable;
use function array_slice;
use function array_values;
use function is_null;
use function mysqli_report;
use function rtrim;
use function str_repeat;

abstract class AbstractDao {

    public abstract function getDataObjectName(): string;

    public abstract function getTableName(): string;

    /**
     * Produces a minimal _CreateTableStatement_.
     *
     * ```
     * CREATE TABLE IF NOT EXISTS `%table_name%`
     * (
     *      `ID`                INT NOT NULL AUTO_INCREMENT,
     *      `%int_prop%`        INT,
     *      `%string_prop%`     VARCHAR(255),
     *      `%bool_prop%`       BOOLEAN,
     *      `%float_prop%`      FLOAT,
     *      PRIMARY KEY (`ID`)
     * );
     * ```
     * In the above _%xyz%_ is placeholder for table name or property name.
     *
     * Subclasses may override.
     *
     * @return string
     * @throws ReflectionException
     */
    public function getCreateTableStatement(): string {
        $sql = /** @lang text */
            "CREATE TABLE IF NOT EXISTS `" . $this->getTableName() . "`\n(\n"
            . "\t`ID` \tINT NOT NULL AUTO_INCREMENT";
        foreach ((new ReflectionClass($this->getDataObjectName()))->getProperties() as $prop) {
            $name = $prop->getName();
            if ($name != "ID") {
                $datatype = DataTypes::fromName($prop->getType()->getName());
                $sql .= ",\n\t`" . $prop->getName() . "`\t" . $datatype;
            }
        }
        $sql .= ",\n\tPRIMARY KEY (`ID`)\n);";
        return $sql;
    }

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

    public function delete(int $ID): int {
        return $this->deleteBatch([$ID]);
    }

    public function select(int $ID): ?Entity {
        $selected = $this->selectBatch([$ID]);
        return (count($selected) == 1) ? $selected[0] : null;
    }

    public function insertBatch(array $entity_array): array {
        $sql = $this->getPrepareInsertStatement();
        Log::debug($sql);
        $new_entities = [];
        $stmt = null;
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        try {
            $row_count = 0;
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
                    $row_count += $stmt->affected_rows;
                } else {
                    $msg = "Could not insert " . $this->getDataObjectName();
                    Log::error($msg, [$entity]);
                    throw new Exception($msg);
                }
            }
            Log::debug("INSERT row count: " . $row_count);
            return $new_entities;
        } catch (Throwable $e) {
            throw new Exception("Could not insert Entity", 201, $e);
        } finally {
            if (!is_null($stmt)) $stmt->close();
        }
    }

    public function updateBatch(array $entity_array): bool {
        $sql = $this->getPrepareUpdateStatement();
        Log::debug($sql);
        $stmt = null;
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        try {
            $row_count = 0;
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
                $row_count += $stmt->affected_rows;
            }
            Log::debug("UPDATE row count: " . $row_count);
            return true;
        } catch (Throwable $e) {
            throw new Exception("Could not update Entity", 202, $e);
        } finally {
            if (!is_null($stmt)) $stmt->close();
        }
    }

    public function deleteBatch(array $ids): int {
        $sql = $this->getPrepareDeleteStatement(count($ids));
        Log::debug($sql);
        $stmt = null;
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        try {
            $conn = MysqlConnector::get()->getConnector();
            $stmt = $conn->prepare($sql);
            $stmt->execute($ids);
            $row_count = $stmt->affected_rows;
            Log::debug("DELETE row count: " . $row_count);
            return $row_count;
        } catch (Throwable $e) {
            throw new Exception("Could not delete Entity", 203, $e);
        } finally {
            if (!is_null($stmt)) $stmt->close();
        }
    }

    public function deleteWhere(string $where_clause): int {
        $sql = /** @lang text */
            "DELETE FROM `" . $this->getTableName() . "` WHERE " . $where_clause;
        Log::debug($sql);
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        try {
            $conn = MysqlConnector::get()->getConnector();
            $conn->query($sql);
            $row_count = $conn->affected_rows;
            Log::debug("DELETE row count: " . $row_count);
            return $row_count;
        } catch (Throwable $e) {
            throw new Exception("Could not delete Entity", 203, $e);
        }
    }

    public function selectBatch(array $ids): array {
        $sql = $this->getPrepareSelectStatement(count($ids));
        Log::debug($sql);
        $stmt = null;
        /** @var $do Entity */
        $do = $this->getDataObjectName();
        $selected = [];
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        try {
            $row_count = 0;
            $conn = MysqlConnector::get()->getConnector();
            $stmt = $conn->prepare($sql);
            $stmt->execute($ids);
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $selected[] = $do::fromArray($row);
                $row_count++;
            }
            Log::debug("SELECT row count: " . $row_count);
            return $selected;
        } catch (Throwable $e) {
            throw new Exception("Could not select Entity", 204, $e);
        } finally {
            if (!is_null($stmt)) $stmt->close();
        }
    }

    public function selectWhere(string $where_clause): array {
        $sql = /** @lang text */
            "SELECT * FROM `" . $this->getTableName() . "` WHERE " . $where_clause;
        Log::debug($sql);
        /** @var $do Entity */
        $do = $this->getDataObjectName();
        $selected = [];
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        try {
            $row_count = 0;
            $conn = MysqlConnector::get()->getConnector();
            $result = $conn->query($sql);
            while ($row = $result->fetch_assoc()) {
                $selected[] = $do::fromArray($row);
                $row_count++;
            }
            Log::debug("SELECT row count: " . $row_count);
            return $selected;
        } catch (Throwable $e) {
            throw new Exception("Could not select Entity", 204, $e);
        }
    }

    private function getPrepareInsertStatement(): string {
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

    private function getPrepareUpdateStatement(): string {
        // UPDATE tbl_name SET parent_id=?, name=?, alias=?, nature=?, public=? WHERE ID=?
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

    private function getPrepareDeleteStatement(int $count): string {
        // DELETE FROM `tbl_name` WHERE `ID`=?[ OR `ID`=?]...
        $sql = /** @lang text */
            "DELETE FROM `" . $this->getTableName() . "` WHERE `ID`=?";
        $sql .= str_repeat(" OR `ID`=?", $count - 1);
        return $sql;
    }

    private function getPrepareSelectStatement(int $count): string {
        // SELECT * FROM `table_name` WHERE `ID`=?[ OR `ID`=?]...
        $sql = /** @lang text */
            "SELECT * FROM `" . $this->getTableName() . "` WHERE `ID`=?";
        $sql .= str_repeat(" OR `ID`=?", $count - 1);
        return $sql;
    }

}