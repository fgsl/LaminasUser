<?php
/**
 * LaminasUser
 * @author Flávio Gomes da Silva Lisboa <flavio.lisboa@fgsl.eti.br>
 * @license AGPL-3.0 <https://www.gnu.org/licenses/agpl-3.0.en.html>
 */

namespace LaminasUser\Factory\Mapper;

use Interop\Container\ContainerInterface;
use LaminasUser\Mapper;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use LaminasUser\Options\ModuleOptions;

class User implements FactoryInterface
{
    public function __invoke(ContainerInterface $serviceLocator, $requestedName, array $options = null)
    {
        /** @var ModuleOptions $options */
        $options = $serviceLocator->get('LaminasUser_module_options');
        $dbAdapter = $serviceLocator->get('LaminasUser_Laminas_db_adapter');

        $entityClass = $options->getUserEntityClass();
        $tableName = $options->getTableName();

        $mapper = new Mapper\User();
        $mapper->setDbAdapter($dbAdapter);
        $mapper->setTableName($tableName);
        $mapper->setEntityPrototype(new $entityClass);
        $mapper->setHydrator(new Mapper\UserHydrator());

        return $mapper;
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $this->__invoke($serviceLocator, null);
    }
}
