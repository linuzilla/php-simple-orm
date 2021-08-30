<?php


namespace Linuzilla\Database\Criterion;


use Closure;
use JetBrains\PhpStorm\Pure;
use Linuzilla\Database\DbException;
use Linuzilla\Database\Interfaces\ColumnsAndDialectProvider;
use Linuzilla\Database\Models\ExtendedColumn;

/**
 * Class Op
 * @package Linuzilla\Database\Criterion
 * @author Mac Liu <linuzilla@gmail.com>
 * @version 1.0.0
 * @date Thu Jun 24 11:33:47 UTC 2021
 */
class Op {
    const INSERT_OPERATION = 1;
    const UPDATE_OPERATION = 2;
    const ORDER_OPERATION = 3;

    private Closure $function;
    private int $operation;


    /**
     * Op constructor.
     * @param int $operation
     * @param Closure $function
     */
    private function __construct(int $operation, Closure $function) {
        $this->operation = $operation;
        $this->function = $function;
    }

    /**
     * @param ColumnsAndDialectProvider $repos
     * @param int $operation
     * @param ExtendedColumn $columnName
     * @param callable $biConsumer
     * @throws DbException
     */
    public function apply(ColumnsAndDialectProvider $repos, int $operation, ExtendedColumn $columnName, callable $biConsumer) {
        if (($this->operation & $operation) == $operation) {
            /** @var callable $f */
            $f = $this->function;
            $f($repos, $operation, $columnName, $biConsumer);
        } else {
            throw new DbException("operation not support");
        }
    }

    /**
     * @return Op
     */
    #[Pure] public static function inc(): Op {
        return new Op(self::UPDATE_OPERATION,
            function (ColumnsAndDialectProvider $repos, int $operation, ExtendedColumn $columnName, callable $biConsumer) {
                $biConsumer(sprintf("%s=%s+1",
                    $columnName->q($repos), $columnName->q($repos)), null);
            });
    }

    /**
     * @return Op
     */
    #[Pure] public static function dec(): Op {
        return new Op(self::UPDATE_OPERATION,
            function (ColumnsAndDialectProvider $repos, int $operation, ExtendedColumn $columnName, callable $biConsumer) {
                $biConsumer(sprintf("%s=%s-1",
                    $columnName->q($repos), $columnName->q($repos)), null);
            });
    }

    /**
     * @param int $value
     * @return Op
     */
    #[Pure] public static function add(int $value): Op {
        return new Op(self::UPDATE_OPERATION,
            function (ColumnsAndDialectProvider $repos, int $operation, ExtendedColumn $columnName, callable $biConsumer) use ($value) {
                $biConsumer(sprintf("%s=%s+?",
                    $columnName->q($repos), $columnName->q($repos)), $value);
            });
    }

    /**
     * @param int $value
     * @return Op
     */
    #[Pure] public static function sub(int $value): Op {
        return new Op(self::UPDATE_OPERATION,
            function (ColumnsAndDialectProvider $repos, int $operation, ExtendedColumn $columnName, callable $biConsumer) use ($value) {
                $biConsumer(sprintf("%s=%s-?", $columnName->q($repos), $columnName->q($repos)), $value);
            });
    }

    /**
     * @param int $value
     * @return Op
     */
    #[Pure] public static function bitwiseAnd(int $value): Op {
        return new Op(self::UPDATE_OPERATION,
            function (ColumnsAndDialectProvider $repos, int $operation, ExtendedColumn $columnName, callable $biConsumer) use ($value) {
                $biConsumer(sprintf("%s=%s & ?", $columnName->q($repos), $columnName->q($repos)), $value);
            });
    }

    /**
     * @param int $value
     * @return Op
     */
    #[Pure] public static function bitwiseOr(int $value): Op {
        return new Op(self::UPDATE_OPERATION,
            function (ColumnsAndDialectProvider $repos, int $operation, ExtendedColumn $columnName, callable $biConsumer) use ($value) {
                $biConsumer(sprintf("%s=%s | ?", $columnName->q($repos), $columnName->q($repos)), $value);
            });
    }

    /**
     * @return Op
     */
    #[Pure] public static function currentDate(): Op {
        return new Op(self::UPDATE_OPERATION | self::INSERT_OPERATION,
            function (ColumnsAndDialectProvider $repos, int $operation, ExtendedColumn $columnName, callable $biConsumer) {
                switch ($operation) {
                    case self::INSERT_OPERATION:
                        $biConsumer("CURDATE()", null);
                        break;
                    case self::UPDATE_OPERATION:
                        $biConsumer(sprintf("%s=CURDATE()", $columnName->q($repos)), null);
                        break;
                }
            });
    }

    /**
     * @return Op
     */
    #[Pure] public static function currentTime(): Op {
        return new Op(self::UPDATE_OPERATION | self::INSERT_OPERATION,
            function (ColumnsAndDialectProvider $repos, int $operation, ExtendedColumn $columnName, callable $biConsumer) {
                switch ($operation) {
                    case self::INSERT_OPERATION:
                        $biConsumer("CURTIME()", null);
                        break;
                    case self::UPDATE_OPERATION:
                        $biConsumer(sprintf("%s=CURDATE()", $columnName->q($repos)), null);
                        break;
                }
            });
    }

    /**
     * @return Op
     */
    #[Pure] public static function now(): Op {
        return new Op(self::UPDATE_OPERATION | self::INSERT_OPERATION,
            function (ColumnsAndDialectProvider $repos, int $operation, ExtendedColumn $columnName, callable $biConsumer) {
                switch ($operation) {
                    case self::INSERT_OPERATION:
                        $biConsumer("NOW()", null);
                        break;
                    case self::UPDATE_OPERATION:
                        $biConsumer(sprintf("%s=NOW()", $columnName->q($repos)), null);
                        break;
                }
            });
    }

    /**
     * @return Op
     */
    #[Pure] public static function null(): Op {
        return new Op(self::UPDATE_OPERATION | self::INSERT_OPERATION,
            function (ColumnsAndDialectProvider $repos, int $operation, ExtendedColumn $columnName, callable $biConsumer) {
                switch ($operation) {
                    case self::INSERT_OPERATION:
                        $biConsumer("NULL", null);
                        break;
                    case self::UPDATE_OPERATION:
                        $biConsumer(sprintf("%s=NULL", $columnName->q($repos)), null);
                        break;
                }
            });
    }

    /**
     * @param int $n
     * @return Op
     */
    #[Pure] public static function nDaysFromNow(int $n): Op {
        return new Op(self::UPDATE_OPERATION | self::INSERT_OPERATION,
            function (ColumnsAndDialectProvider $repos, int $operation, ExtendedColumn $columnName, callable $biConsumer) use ($n) {
                $nday = sprintf("DATE_ADD(NOW(), INTERVAL %s DAY)", $n);

                switch ($operation) {
                    case self::INSERT_OPERATION:
                        $biConsumer($nday, null);
                        break;
                    case self::UPDATE_OPERATION:
                        $biConsumer(sprintf("%s=%s", $columnName->q($repos), $nday), null);
                        break;
                }
            });
    }

    /**
     * @param int $n
     * @return Op
     */
    #[Pure] public static function nHoursFromNow(int $n): Op {
        return new Op(self::UPDATE_OPERATION | self::INSERT_OPERATION,
            function (ColumnsAndDialectProvider $repos, int $operation, ExtendedColumn $columnName, callable $biConsumer) use ($n) {
                $nday = sprintf("DATE_ADD(NOW(), INTERVAL %s HOUR)", $n);

                switch ($operation) {
                    case self::INSERT_OPERATION:
                        $biConsumer($nday, null);
                        break;
                    case self::UPDATE_OPERATION:
                        $biConsumer(sprintf("%s=%s", $columnName->q($repos), $nday), null);
                        break;
                }
            });
    }

    /**
     * @param $ipString
     * @return Op
     */
    #[Pure] public static function inet_aton(string $ipString): Op {
        return new Op(self::UPDATE_OPERATION | self::INSERT_OPERATION,
            function (ColumnsAndDialectProvider $repos, int $operation, ExtendedColumn $columnName, callable $biConsumer) use ($ipString) {
                switch ($operation) {
                    case self::INSERT_OPERATION:
                        $biConsumer("INET_ATON(?)", $ipString);
                        break;
                    case self::UPDATE_OPERATION:
                        $biConsumer(sprintf("%s=INET_ATON(?)", $columnName->q($repos)), $ipString);
                        break;
                    case self::ORDER_OPERATION:
                        $biConsumer(sprintf("INET_ATON(%s)", $repos->dialect()->quote($ipString)), $ipString);
                }
            });
    }

    /**
     * @param int $ipValue
     * @return Op
     */
    #[Pure] public static function inet_ntoa(int $ipValue): Op {
        return new Op(self::UPDATE_OPERATION | self::INSERT_OPERATION,
            function (ColumnsAndDialectProvider $repos, int $operation, ExtendedColumn $columnName, callable $biConsumer) use ($ipValue) {
                switch ($operation) {
                    case self::INSERT_OPERATION:
                        $biConsumer("INET_NTOA(?)", $ipValue);
                        break;
                    case self::UPDATE_OPERATION:
                        $biConsumer(sprintf("%s=INET_NTOA(?)", $columnName->q($repos)), $ipValue);
                        break;
                }
            });
    }
}