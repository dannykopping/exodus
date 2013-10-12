<?php
namespace Exodus;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

class Server
{
    private $schema;
    private $host;
    private $port;
    private $username;
    private $password;
    private $driver;

    private $ignore = array();
    const IGNORE_TABLE_REGEX = '/^((?!%s).)*$/im';

    const PDO_MYSQL         = 'pdo_mysql';
    const PDO_SQLITE        = 'pdo_sqlite';
    const PDO_PGSQL         = 'pdo_pgsql';
    const PDO_ORACLE        = 'pdo_oci';
    const OCI8              = 'oci8';
    const IBMDB2            = 'ibm_db2';
    const PDO_IBM           = 'pdo_ibm';
    const PDO_SQLSRV        = 'pdo_sqlsrv';
    const MYSQLI            = 'mysqli';
    const DRIZZLE_PDO_MYSQL = 'drizzle_pdo_mysql';
    const SQLSRV            = 'sqlsrv';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var Configuration
     */
    private $config;

    function __construct($schema, $host, $username, $password, $port = 3306, $driver = self::PDO_MYSQL)
    {
        $this->setSchema($schema);
        $this->setHost($host);
        $this->setUsername($username);
        $this->setPassword($password);
        $this->setPort($port);
        $this->setDriver($driver);

        $this->connect();
    }

    /**
     * @return Connection
     */
    public function connect()
    {
        $this->config     = new Configuration();
        $connectionParams = array(
            'dbname'   => $this->getSchema(),
            'user'     => $this->getUsername(),
            'password' => $this->getPassword(),
            'host'     => $this->getHost(),
            'port'     => $this->getPort(),
            'driver'   => $this->getDriver(),
        );

        $this->connection = DriverManager::getConnection($connectionParams, $this->config);
        return $this->connection;
    }

    /**
     * @param array $ignore
     */
    public function setIgnore($ignore)
    {
        $this->ignore = $ignore;

        if(empty($this->config)) {
            return;
        }

        // apply ignores
        $ignore = implode('|', $ignore);
        if (!empty($ignore)) {
            $this->config->setFilterSchemaAssetsExpression(sprintf(self::IGNORE_TABLE_REGEX, $ignore));
        }
    }

    /**
     * @return array
     */
    public function getIgnore()
    {
        return $this->ignore;
    }

    /**
     * @return Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    public function closeConnection()
    {
        if ($this->connection) {
            $this->connection = null;
        }
    }

    /**
     * @param mixed $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * @return mixed
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param mixed $schema
     */
    public function setSchema($schema)
    {
        $this->schema = $schema;
    }

    /**
     * @return mixed
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $port
     */
    public function setPort($port)
    {
        $this->port = $port;
    }

    /**
     * @return mixed
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param mixed $driver
     */
    public function setDriver($driver)
    {
        $this->driver = $driver;
    }

    /**
     * @return mixed
     */
    public function getDriver()
    {
        return $this->driver;
    }
}