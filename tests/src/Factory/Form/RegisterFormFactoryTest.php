<?php
/**
 * LaminasUser
 * @author Flávio Gomes da Silva Lisboa <flavio.lisboa@fgsl.eti.br>
 * @license AGPL-3.0 <https://www.gnu.org/licenses/agpl-3.0.en.html>
 */

namespace LaminasUserTest\Factory\Form;

use LaminasUser\Factory\Form\Register as RegisterFactory;
use LaminasUser\Mapper\User as UserMapper;
use LaminasUser\Options\ModuleOptions;
use Laminas\Hydrator\ClassMethodsHydrator;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase;
use Laminas\Form\FormElementManagerFactory;

class RegisterFormFactoryTest extends TestCase
{
    public function testFactory()
    {
        $serviceManager = new ServiceManager;
        $serviceManager->setService('LaminasUser_module_options', new ModuleOptions);
        $serviceManager->setService('LaminasUser_user_mapper', new UserMapper);
        $serviceManager->setService('LaminasUser_register_form_hydrator', new ClassMethodsHydrator());

        $serviceManager->setFactory('FormElementManager', FormElementManagerFactory::class);

        $factory = new RegisterFactory();

        $this->assertInstanceOf('LaminasUser\Form\Register', $factory->__invoke($serviceManager, 'LaminasUser\Form\Register'));
    }
}
