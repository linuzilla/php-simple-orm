<?php


namespace Linuzilla\Database;


/**
 * Class ReverseEngineeringParameters
 * @package Linuzilla\Database
 * @author Mac Liu <linuzilla@gmail.com>
 * @version 1.0.0
 * @date Tue Jun 22 18:16:06 CST 2021
 */
class ReverseEngineeringParameters {
    public string $title;
    public string $author;
    public string $version;
    public bool $enableGenerateDelegationFunction;

    public string $databaseName;
    public string $databaseClassName;

    public string $datasourceNameSpace;
    public string $datasourceDestinationDirectory;

    public string $entitiesNameSpace;
    public string $entitiesDestinationDirectory;

    public bool $enableRepositoryBase;
    public string $repositoriesBaseNameSpace;
    public string $repositoriesBaseDestinationDirectory;

    public string $repositoriesNameSpace;
    public string $repositoriesDestinationDirectory;
}
