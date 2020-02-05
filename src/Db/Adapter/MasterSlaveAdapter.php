<?php
/**
 * LaminasUser
 * @author Flávio Gomes da Silva Lisboa <flavio.lisboa@fgsl.eti.br>
 * @license AGPL-3.0 <https://www.gnu.org/licenses/agpl-3.0.en.html>
 */
namespace LaminasBase\Db\Adapter;

use LaminasUser\Db\Adapter\MasterSlaveAdapterInterface;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Adapter\Platform;
use Laminas\Db\ResultSet;
use Laminas\Db\Adapter\Driver\DriverInterface;

class MasterSlaveAdapter extends Adapter implements MasterSlaveAdapterInterface
{
    /**
     * slave adapter
     *
     * @var Adapter
     */
    protected $slaveAdapter;
    /**
     * @param Adapter $slaveAdapter
     * @param DriverInterface|array $driver
     * @param Platform\PlatformInterface $platform
     * @param ResultSet\ResultSet $queryResultPrototype
     */
    public function __construct(
        Adapter $slaveAdapter,
        $driver,
        Platform\PlatformInterface $platform = null,
        ResultSet\ResultSetInterface $queryResultPrototype = null
    ) {
        $this->slaveAdapter = $slaveAdapter;
        parent::__construct($driver, $platform, $queryResultPrototype);
    }
    /**
     * get slave adapter
     *
     * @return Adapter
     */
    public function getSlaveAdapter()
    {
        return $this->slaveAdapter;
    }
}
