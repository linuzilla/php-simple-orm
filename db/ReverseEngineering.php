<?php


namespace Linuzilla\Database;


use JetBrains\PhpStorm\Pure;
use Linuzilla\Database\Attributes\Expose;
use Linuzilla\Database\Dialects\DatabaseDialect;
use Linuzilla\Database\Dialects\DbDialectColumn;
use Linuzilla\Database\Dialects\DbDialectTable;
use Linuzilla\Database\Dialects\MsSqlDialect;
use Linuzilla\Database\Dialects\MySqlDialect;
use Linuzilla\Database\Helpers\StringHelper;
use Linuzilla\Database\Repositories\BaseRepository;
use PDO;
use ReflectionClass;

/**
 * Class ReverseEngineering
 * @package Linuzilla\Database
 * @author Mac Liu <linuzilla@gmail.com>
 * @version 1.0.0
 * @date Wed Jun 23 01:03:00 UTC 2021
 */
class ReverseEngineering {
    const BASE_REPOSITORY_CLASS = BaseRepository::class;

    const ENTITY_BASE_NAME = 'BaseEntity';
    const DATASOURCE_BASE_NAME = '\Linuzilla\Database\Repositories\BaseRepository';

    private PDO $pdo;
    private DatabaseDialect $dialect;
    private DbInfo $dbInfo;
    private ReverseEngineeringParameters $param;
    private int $fileExistsCounter;
    private int $fileCreatedCounter;
    private int $fileErrorCounter;
    private string $now;
    private ReflectionClass $baseRepositoryReflection;


    /**
     * @param PDO $pdo
     * @param $databaseType
     * @param ReverseEngineeringParameters $parameters
     * @throws DbException
     */
    public static function generate(PDO $pdo, $databaseType, ReverseEngineeringParameters $parameters) {
        if ($databaseType == 'mysql') {
            $dialect = new MySqlDialect();
        } else if ($databaseType == 'mssql') {
            $dialect = new MsSqlDialect();
        } else {
            throw new DbException("database '$databaseType' not support");
        }

        (new ReverseEngineering($pdo, $dialect, $parameters))->reverseEngineering();
    }

    /**
     * ReverseEngineering constructor.
     * @param PDO $pdo
     * @param DatabaseDialect $dialect
     * @param ReverseEngineeringParameters $parameters
     */
    #[Pure]
    private function __construct(PDO $pdo, DatabaseDialect $dialect, ReverseEngineeringParameters $parameters) {
        $this->pdo = $pdo;
        $this->dialect = $dialect;
        $this->param = $parameters;
        $this->dbInfo = new DbInfo($this->pdo, $this->dialect);
        $this->now = date("D M j G:i:s T Y");
    }

    /**
     * @throws DbException
     */
    private function reverseEngineering() {
        $this->fileExistsCounter = 0;
        $this->fileCreatedCounter = 0;
        $this->fileErrorCounter = 0;

        $dataSourceClassName = $this->generateDataSource();

        $repositoryBaseClass = "\\" . $this->param->datasourceNameSpace . "\\" . $dataSourceClassName;

        foreach ($this->dbInfo->getTables($this->param->databaseName) as $tableEntry) {
            /**@var DbDialectTable $tableEntry */
            $tableName = $tableEntry->tableName;

            $camelCaseName = StringHelper::to_camel_case($tableName, true);
            $entityClass = $camelCaseName . "Entity";
            $repositoryClass = $camelCaseName . "Repository";
            $repositoryBase = $camelCaseName . "Base";

            $entityFullName = "\\" . $this->param->entitiesNameSpace . "\\" . $entityClass;
            $baseFullName = "\\" . $this->param->repositoriesBaseNameSpace . "\\" . $repositoryBase;

            $this->generateEntity($entityClass, $tableEntry);

            if ($this->param->enableRepositoryBase) {
                $this->generateRepositoryBase($repositoryBase, $repositoryBaseClass, $entityFullName);
                $this->generateRepositoryExtendBase($repositoryClass, $baseFullName);
            } else {
                $this->generateRepository($repositoryClass, $repositoryBaseClass, $entityFullName);
            }
        }

        printf("\nDatabase to Entity: %d created, %d exists, %d, failed\n",
            $this->fileCreatedCounter, $this->fileExistsCounter, $this->fileErrorCounter);
    }

    /**
     * @return string
     */
    private function generateDataSource(): string {
        $pickName = $this->param->databaseClassName ?? $this->param->databaseName;

        $clazzName = StringHelper::to_camel_case($pickName, true) . "DataSource";
        $fileName = $this->param->datasourceDestinationDirectory . '/' . $clazzName . '.php';

//        unlink($fileName);
        $this->createFile($fileName, $this->param->datasourceNameSpace, $clazzName,
            self::DATASOURCE_BASE_NAME, function ($fp) {
                fwrite($fp, "    private static \\PDO \$pdo;\n");
                fwrite($fp, "    private static \\Linuzilla\\Database\\Dialects\\DatabaseDialect \$dialect;\n");
                fwrite($fp, "    private static \\Linuzilla\\Database\\Interfaces\\QueryLogger \$queryLogger;\n\n");
                fwrite($fp, "    public static function initialize(\\PDO \$pdo) {\n");
                fwrite($fp, "        self::\$pdo = \$pdo;\n");
                fwrite($fp, "        self::\$dialect = new \\Linuzilla\\Database\\Dialects\\MySqlDialect();\n");
                fwrite($fp, "        self::\$queryLogger = new \\Linuzilla\\Database\\StderrQueryLoggerImpl();\n");
                fwrite($fp, "    }\n\n");
                fwrite($fp, "    public static function setLogger(\\Linuzilla\\Database\\Interfaces\\QueryLogger \$logger) {\n");
                fwrite($fp, "        self::\$queryLogger = \$logger;\n");
                fwrite($fp, "    }\n\n");
                fwrite($fp, "    public function pdo(): \\PDO {\n");
                fwrite($fp, "        return self::\$pdo;\n");
                fwrite($fp, "    }\n\n");
                fwrite($fp, "    public function dialect(): \\Linuzilla\\Database\\Dialects\\DatabaseDialect {\n");
                fwrite($fp, "        return self::\$dialect;\n");
                fwrite($fp, "    }\n\n");
                fwrite($fp, "    public function logger(): \\Linuzilla\\Database\\Interfaces\\QueryLogger {\n");
                fwrite($fp, "        return self::\$queryLogger;\n");
                fwrite($fp, "    }\n");
            });

        return $clazzName;
    }

    private function addPhpDoc($fp) {
        fprintf($fp, "/**\n");

        if (!empty($this->param->title)) {
            fprintf($fp, " * %s\n", $this->param->title);
        }
        if (!empty($this->param->author)) {
            fprintf($fp, " * @author: %s\n", $this->param->author);
        }
        if (!empty($this->param->version)) {
            fprintf($fp, " * @version: %s\n", $this->param->version);
        }
        fprintf($fp, " * @date: %s\n", $this->now);
        fprintf($fp, " */\n");
    }


    /**
     * @param string $fileName
     * @param string $nameSpace
     * @param string $clazzName
     * @param string $baseClazz
     * @param callable $function
     * @param callable|null $extraFunction
     * @param callable|null $classLevelAttribute
     */
    private function createFile(string $fileName, string $nameSpace, string $clazzName, string $baseClazz,
                                callable $function, ?callable $extraFunction = null, ?callable $classLevelAttribute = null) {
        if (!file_exists($fileName)) {
            printf("generate file: %s ... ", $fileName);
            if (($fp = fopen($fileName, "x")) !== false) {
                fprintf($fp, "<?php\n\n\nnamespace %s;\n\n", $nameSpace);
                if (isset($extraFunction) and is_callable($extraFunction)) {
                    $extraFunction($fp);
                }
                $this->addPhpDoc($fp);
                if (isset($classLevelAttribute) and is_callable($classLevelAttribute)) {
                    $classLevelAttribute($fp);
                }
                fprintf($fp, "class %s extends %s {\n", $clazzName, $baseClazz);
                $function($fp);
                fprintf($fp, "}\n");
                fclose($fp);
                printf("created.\n");
                $this->fileCreatedCounter++;
            } else {
                print_r(error_get_last());
                $this->fileErrorCounter++;
            }
        } else {
            $this->fileExistsCounter++;
        }

    }

    /**
     * @param string $clazzName
     * @param DbDialectTable $tableEntry
     * @throws DbException
     */
    private function generateEntity(string $clazzName, DbDialectTable $tableEntry) {
        $tableName = $tableEntry->tableName;

        $fileName = $this->param->entitiesDestinationDirectory . '/' . $clazzName . '.php';

        $this->createFile($fileName, $this->param->entitiesNameSpace, $clazzName,
            self::ENTITY_BASE_NAME, function ($file) use ($tableName, $tableEntry) {
                $columns = $this->dbInfo->getColumns($this->param->databaseName, $tableName);
                $pk = $this->dbInfo->getPrimaryKeys($this->param->databaseName, $tableName);
                $auto = $this->dbInfo->getAutoGenColumns($this->param->databaseName, $tableName);

                foreach ($columns as $column) {
                    /** @var DbDialectColumn $column */
                    fprintf($file, "    const %s = '%s';\n",
                        strtoupper(StringHelper::camel_to_snake($column->columnName)), $column->columnName);
                }
//                fprintf($file, "\n");

//                fprintf($file, "    public static \$_tableName = '%s';\n", $tableName);
//                fprintf($file, "    public static \$_tableType = '%s';\n", $tableEntry->tableType);
//                fprintf($file, "    public static \$_primaryKey = [ %s ];\n",
//                    implode(', ', array_map(function ($x) {
//                        return sprintf("'%s'", $x);
//                    }, $pk)));
//                fprintf($file, "    public static \$_autoIncrementKey = [ %s ];\n",
//                    implode(', ', array_map(function ($x) {
//                        return sprintf("'%s'", $x);
//                    }, $auto)));
//                fprintf($file, "    public static \$_columnNames = [ %s ];\n\n",
//                    implode(', ', array_map(function ($column) {
//                        /** @var DbDialectColumn $column */
//                        return sprintf("'%s'", $column->columnName);
//                    }, $columns)));

                foreach ($columns as $column) {
                    /** @var DbDialectColumn $column */
                    $columnName = $column->columnName;
                    fprintf($file, "\n");
                    if (in_array($columnName, $pk)) {
                        fprintf($file, "    #[PrimaryKey]\n");
                    }
                    if (in_array($columnName, $auto)) {
                        fprintf($file, "    #[AutoIncrement]\n");
                    }
                    if (isset($column->nullable)) {
                        if ($column->nullable) {
                            fprintf($file, "    #[Nullable]\n");
                        } else {
                            fprintf($file, "    #[NonNull]\n");
                        }
                    }
                    if (isset($column->updateTimeStamp)) {
                        if ($column->updateTimeStamp) {
                            fprintf($file, "    #[UpdateTimeStamp]\n");
                        }
                    }

                    fprintf($file, "    #[Properties(name:\"%s\", dataType: \"%s\", columnType: \"%s\")]\n",
                        $column->columnName,
                        $column->dataType ?? '',
                        $column->columnType ?? '');

                    fprintf($file, "    public $%s;\n", $columnName);
                }
            }, function ($fp) {
                fwrite($fp, "use Linuzilla\Database\Attributes\AutoIncrement;\n");
                fwrite($fp, "use Linuzilla\Database\Attributes\ColumnNames;\n");
                fwrite($fp, "use Linuzilla\Database\Attributes\Entity;\n");
                fwrite($fp, "use Linuzilla\Database\Attributes\NonNull;\n");
                fwrite($fp, "use Linuzilla\Database\Attributes\Nullable;\n");
                fwrite($fp, "use Linuzilla\Database\Attributes\PrimaryKey;\n");
                fwrite($fp, "use Linuzilla\Database\Attributes\Properties;\n");
                fwrite($fp, "use Linuzilla\Database\Attributes\UpdateTimeStamp;\n");
                fwrite($fp, "use Linuzilla\Database\Dialects\DbDialectTable;\n");
                fwrite($fp, "use Linuzilla\Database\Entities\BaseEntity;\n\n");
            }, function ($fp) use ($tableName, $tableEntry) {
                $columns = $this->dbInfo->getColumns($this->param->databaseName, $tableName);
                $pk = $this->dbInfo->getPrimaryKeys($this->param->databaseName, $tableName);
                $auto = $this->dbInfo->getAutoGenColumns($this->param->databaseName, $tableName);

                fprintf($fp, "#[Entity(name: '%s', type: DbDialectTable::%s)]\n", $tableName, strtoupper($tableEntry->tableType));
                fprintf($fp, "#[PrimaryKey([%s])]\n", implode(', ', array_map(function ($x) {
                    return sprintf("'%s'", $x);
                }, $pk)));
                fprintf($fp, "#[AutoIncrement([%s])]\n", implode(', ', array_map(function ($x) {
                    return sprintf("'%s'", $x);
                }, $auto)));
                fprintf($fp, "#[ColumnNames([%s])]\n", implode(', ', array_map(function ($column) {
                    /** @var DbDialectColumn $column */
                    return sprintf("'%s'", $column->columnName);
                }, $columns)));
            });
    }

    /**
     * @param string $clazzName
     * @param string $repositoryBase
     * @param string $entityClassName
     */
    private function generateRepository(string $clazzName, string $repositoryBase, string $entityClassName) {
        $fileName = $this->param->repositoriesDestinationDirectory . '/' . $clazzName . '.php';

        $this->createFile($fileName, $this->param->repositoriesNameSpace, $clazzName,
            $repositoryBase, function ($fp) use ($entityClassName) {
                fprintf($fp, "    public function __construct() {\n");
                fprintf($fp, "        parent::__construct(new %s());\n", $entityClassName);
                fprintf($fp, "    }\n");
            });
    }

    private function generateRepositoryExtendBase(string $clazzName, string $repositoryBase) {
        $fileName = $this->param->repositoriesDestinationDirectory . '/' . $clazzName . '.php';

        $this->createFile($fileName, $this->param->repositoriesNameSpace, $clazzName,
            $repositoryBase, function ($fp) {
//                fprintf($fp, "    public function __construct() {\n");
//                fprintf($fp, "        parent::__construct();\n");
//                fprintf($fp, "    }\n");
            });
    }

    private function generateRepositoryBase(string $clazzName, string $repositoryBase, string $entityClassName) {
        $fileName = $this->param->repositoriesBaseDestinationDirectory . '/' . $clazzName . '.php';

        $this->createFile($fileName, $this->param->repositoriesBaseNameSpace, $clazzName,
            $repositoryBase, function ($fp) use ($entityClassName) {
                fprintf($fp, "    public function __construct() {\n");
                fprintf($fp, "        parent::__construct(new %s());\n", $entityClassName);
                fprintf($fp, "    }\n");

                if ($this->param->enableGenerateDelegationFunction) {
                    $this->generateDelegationFunction($fp, $entityClassName);
                }
            }, function ($fp) {
                if ($this->param->enableGenerateDelegationFunction) {
                    fwrite($fp, "use Linuzilla\Database\DbException;\n\n");
                }
            });
    }

    /**
     * @return ReflectionClass
     */
    private function getReflection(): ReflectionClass {
        if (!isset($this->baseRepositoryReflection)) {
            $this->baseRepositoryReflection = new ReflectionClass(self::BASE_REPOSITORY_CLASS);
        }
        return $this->baseRepositoryReflection;
    }

    /**
     * @param $fp
     * @param string $myEntityName
     */
    private function generateDelegationFunction($fp, string $myEntityName) {
        $r = $this->getReflection();

        foreach ($r->getMethods() as $m) {
            if (!$m->isStatic() && $m->isPublic() && !$m->isConstructor() && !$m->isAbstract()) {
                $attrs = $m->getAttributes(Expose::class);

                if (count($attrs) > 0) {
                    $docComment = $m->getDocComment();

                    if (str_contains($docComment, self::ENTITY_BASE_NAME)) {
                        $doc = str_replace(self::ENTITY_BASE_NAME, $myEntityName, $docComment);
                        $args = implode(', ', array_map(function ($param) use ($m, $r) {
                            return '$' . $param->getName();
                        }, $m->getParameters()));

                        fprintf($fp, "\n");
                        fprintf($fp, "    %s\n", str_replace(self::ENTITY_BASE_NAME, $myEntityName, $doc));
                        fprintf($fp, "    public function _%s(%s) {\n", $m->getName(), $args);
                        fprintf($fp, "        return parent::%s(%s);\n", $m->getName(), $args);
                        fprintf($fp, "    }\n");
                    }
                }
            }
        }
    }
}