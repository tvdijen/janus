<?php

namespace Janus\ServiceRegistry\Connection;

use Janus\ServiceRegistry\Connection\ConnectionDto;
use Janus\ServiceRegistry\Entity\Connection;

class ConnectionDtoCollection
{
    /**
     * Collection of Connection Dto's
     *
     * @var ConnectionDescriptorDto[]
     */
    public $connections = array();

    /**
     * @var integer
     */
    public $offset;

    /**
     * @var integer
     */
    public $limit;

    /**
     * @param ConnectionDescriptorDto[] $connections
     * @param integer $offset
     * @param integer $limit
     */
    public function __construct(array $connections = array(), $offset = null, $limit = null)
    {
        foreach ($connections as $connection) {
            $this->addConnection($connection);
        }

        $this->offset = $offset;
        $this->limit = $limit;
    }

    /**
     * @param ConnectionDescriptorDto $connectionDto
     * @return $this
     */
    public function addConnection(ConnectionDescriptorDto $connectionDto)
    {
        $this->connections[$connectionDto->id] = $connectionDto;
        return $this;
    }
}
