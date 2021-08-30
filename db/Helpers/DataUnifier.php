<?php


namespace Linuzilla\Database\Helpers;


use Linuzilla\Database\Criterion\Op;
use Linuzilla\Database\Criterion\QueryWithArgs;
use Linuzilla\Database\Criterion\Qx;
use Linuzilla\Database\Criterion\SpecialCondition;
use Linuzilla\Database\DbException;
use Linuzilla\Database\Interfaces\ColumnsAndDialectProvider;
use Linuzilla\Database\Models\ExtendedColumn;

/**
 * to unify object and array property access
 *
 * Class DataUnifier
 * @package Linuzilla\Database\Helpers
 * @author Mac Liu <linuzilla@gmail.com>
 * @version 1.0.0
 * @date Tue Jun 22 23:29:33 UTC 2021
 */
class DataUnifier {
    private object|array $data;
    private bool $isArray;
    private bool $isOperationOnField;

    /**
     * DataHolder constructor.
     * @param array|object $data
     */
    public function __construct(object|array $data) {
        $this->data = $data;
        $this->isArray = is_array($this->data);
        $this->isOperationOnField = ($this->data instanceof SpecialCondition);
    }

    /**
     * @param $fieldName
     * @return bool
     */
    public function isset($fieldName): bool {
        if ($this->isArray) {
//            echo __FILE__ . ':' . __LINE__ . ' ';
//            print_r($fieldName);
//            echo $fieldName . PHP_EOL;
            return isset($this->data[$fieldName]);
        } else if ($this->isOperationOnField) {
            return false;
        } else {
            return isset($this->data->$fieldName);
        }
    }

    /**
     * @param $fieldName
     * @return mixed
     */
    public function get($fieldName): mixed {
        if ($this->isArray) {
            return $this->data[$fieldName] ?? null;
        } else if ($this->isOperationOnField) {
            return null;
        } else {
            return $this->data->$fieldName ?? null;
        }
    }

    public function count(): int {
        if ($this->isArray) {
            return count($this->data);
        } else if ($this->isOperationOnField) {
            return 0;
        } else {
            $vars = get_object_vars($this->data);
            $counter = 0;

            foreach ($vars as $k => $v) {
                if (isset($this->data->$k)) {
                    $counter++;
                }
            }
            return $counter;
        }
    }

    /**
     * @param ColumnsAndDialectProvider $repos
     * @param ExtendedColumn $extendedColumn
     * @param int $operation
     * @param callable $biConsumer
     * @throws DbException
     */
    public function richColumnValue(ColumnsAndDialectProvider $repos, ExtendedColumn $extendedColumn, int $operation, callable $biConsumer) {
        if ($this->isset($extendedColumn->fullColumnName)) {
            $value = $this->get($extendedColumn->fullColumnName);

            switch ($operation) {
                case Op::INSERT_OPERATION:
                    if ($value instanceof Op) {
                        $value->apply($repos, $operation, $extendedColumn, $biConsumer);
                    } else {
                        $biConsumer(null, $value);
                    }
                    break;
                case Op::UPDATE_OPERATION:
                    if ($value instanceof Op) {
                        $value->apply($repos, $operation, $extendedColumn, $biConsumer);
                    } else {
                        $biConsumer($extendedColumn->q($repos) . '=?', $value);
                    }
                    break;
                default:
                    throw new DbException("operation not support");
            }
        }
    }

    public function buildQuery(ColumnsAndDialectProvider $repos): QueryWithArgs {
        if ($this->isOperationOnField) {
            /**@var SpecialCondition $op */
            $op = $this->data;
            return $op->retrieve();
        } else {
            $where = false;
            $args = [];

            foreach ($repos->getExtendedColumns() as $extendedColumn) {
                if ($this->isset($extendedColumn->fullColumnName)) {
                    if ($where !== false) {
                        $where .= ' AND ';
                    } else {
                        $where = '';
                    }

                    $fieldValue = $this->get($extendedColumn->fullColumnName);

                    if ($fieldValue instanceof Qx) {
                        list($where, $args) = self::criterionHandling($repos, $fieldValue, $extendedColumn, $where, $args);
                    } else {
                        $where .= $extendedColumn->q($repos) . "=?";
                        $args[] = $fieldValue;
                    }
//                } else {
//                    echo $extendedColumn->columnName . " not found!" . PHP_EOL;
                }
            }

            if ($where !== false) {
                return new QueryWithArgs($where, $args);
            } else {
                return new QueryWithArgs('', []);
            }
        }
    }

    public static function criterionHandling(ColumnsAndDialectProvider $repos, Qx $qx, ExtendedColumn $extendedColumn, string $where, mixed $args): array {
        $data = $qx->get();
        $where .= $extendedColumn->q($repos) . ' ' . $data[0];
        if (!is_null($data[1])) {
            if (is_array($data[1])) {
                $args = array_merge($args, $data[1]);
            } else {
                $args[] = $data[1];
            }
        }
        return [$where, $args];
    }
}