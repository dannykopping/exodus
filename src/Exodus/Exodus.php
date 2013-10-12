<?php
namespace Exodus;

use Doctrine\DBAL\Schema\Comparator;
use Doctrine\DBAL\Schema\SchemaDiff;
use Exodus\Exception\ExodusException;
use Exodus\Util\Ignore;

class Exodus
{
    const FORWARD_DIFF  = 'forward';
    const BACKWARD_DIFF = 'backward';

    const SCOPE_SOURCE      = 'source';
    const SCOPE_DESTINATION = 'destination';
    const SCOPE_BOTH        = 'both';

    /**
     * @var Server
     */
    private $source;

    /**
     * @var Server
     */
    private $destination;

    /**
     * @var array
     */
    private $sourceIgnores = array();

    /**
     * @var array
     */
    private $destinationIgnores = array();

    public function __construct(Server $source, Server $destination)
    {
        $this->setSource($source);
        $this->setDestination($destination);
    }

    /**
     * @param string $direction
     *
     * @throws ExodusException
     * @return SchemaDiff
     */
    public function getRawDiff($direction = self::FORWARD_DIFF)
    {
        $compare = new Comparator();
        $source  = $this->getSource();
        if (empty($source)) {
            throw new ExodusException(ExodusException::NO_SOURCE);
        }

        $destination = $this->getDestination();
        if (empty($destination)) {
            throw new ExodusException(ExodusException::NO_DESTINATION);
        }

        $sourceConnection = $source->getConnection();
        if (empty($sourceConnection)) {
            throw new ExodusException(sprintf(ExodusException::NO_CONNECTION, 'source'));
        }

        $destinationConnection = $destination->getConnection();
        if (empty($destinationConnection)) {
            throw new ExodusException(sprintf(ExodusException::NO_CONNECTION, 'destination'));
        }

        $a = $direction == self::FORWARD_DIFF ? $destinationConnection : $sourceConnection;
        $b = $direction == self::FORWARD_DIFF ? $sourceConnection : $destinationConnection;

        return $compare->compare(
            $a->getSchemaManager()->createSchema(),
            $b->getSchemaManager()->createSchema()
        );
    }

    public function ignoreTables($tables, $scope = self::SCOPE_BOTH)
    {
        if(empty($tables)) {
            return;
        }

        if(!is_array($tables)) {
            $tables = array($tables);
        }

        if($scope == self::SCOPE_SOURCE || $scope == self::SCOPE_BOTH) {
            $this->sourceIgnores = array_merge($this->sourceIgnores, $tables);

            if($this->getSource()) {
                $this->getSource()->setIgnore($this->sourceIgnores);
            }
        }

        if($scope == self::SCOPE_DESTINATION || $scope == self::SCOPE_BOTH) {
            $this->destinationIgnores = array_merge($this->destinationIgnores, $tables);

            if($this->getDestination()) {
                $this->getDestination()->setIgnore($this->destinationIgnores);
            }
        }
    }

    /**
     * @param string $direction
     * @param bool   $single
     *
     * @throws Exception\ExodusException
     * @return array
     */
    public function getSQLDiff($direction = self::FORWARD_DIFF, $single = false)
    {
        $source = $this->getSource();
        if (empty($source)) {
            throw new ExodusException(ExodusException::NO_SOURCE);
        }

        $connection = $source->getConnection();
        if (empty($connection)) {
            throw new ExodusException(sprintf(ExodusException::NO_CONNECTION, 'source'));
        }

        $queries = $this->getRawDiff($direction)->toSql($connection->getDatabasePlatform());
        if (count($queries) && $single) {
            $queries = implode(';' . PHP_EOL, $queries);
        }

        return $queries;
    }

    /**
     * @param Server $source
     */
    public function setSource(Server $source)
    {
        $this->source = $source;
    }

    /**
     * @return Server
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param Server $destination
     */
    public function setDestination(Server $destination)
    {
        $this->destination = $destination;
    }

    /**
     * @return Server
     */
    public function getDestination()
    {
        return $this->destination;
    }

}