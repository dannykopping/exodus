<?php
namespace Exodus;

use Doctrine\DBAL\Schema\Comparator;
use Doctrine\DBAL\Schema\SchemaDiff;
use Exodus\Exception\ExodusException;

class Exodus
{
    /**
     * @var Server
     */
    private $source;

    /**
     * @var Server
     */
    private $destination;

    const FORWARD_DIFF  = 'forward';
    const BACKWARD_DIFF = 'backward';

    public function __construct(Server $source, Server $destination)
    {
        $this->setSource($source);
        $this->setDestination($destination);
    }

    /**
     * @param string $direction
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

    /**
     * @param string $direction
     * @param bool $single
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
        if(count($queries) && $single) {
            $queries = implode(';'.PHP_EOL, $queries);
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