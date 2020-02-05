<?php
/**
 * LaminasUser
 * @author Flávio Gomes da Silva Lisboa <flavio.lisboa@fgsl.eti.br>
 * @license AGPL-3.0 <https://www.gnu.org/licenses/agpl-3.0.en.html>
 */

namespace LaminasUserTest\Factory\Form;

use Laminas\Form\FormElementManager;
use Laminas\ServiceManager\ServiceManager;
use LaminasUser\Factory\Form\ChangePassword as ChangePasswordFactory;
use LaminasUser\Options\ModuleOptions;
use PHPUnit\Framework\TestCase;
use LaminasUser\Mapper\User as UserMapper;

class ChangePasswordFormFactoryTest extends TestCase
{
    public function testFactory()
    {
        $serviceManager = new ServiceManager;
        $serviceManager->setService('LaminasUser_module_options', new ModuleOptions);
        $serviceManager->setService('LaminasUser_user_mapper', new UserMapper);

        $formElementManager = new FormElementManager($serviceManager);
        $serviceManager->setService('FormElementManager', $formElementManager);

        $factory = new ChangePasswordFactory();

        $this->assertInstanceOf('LaminasUser\Form\ChangePassword', $factory->__invoke($serviceManager, 'LaminasUser\Form\ChangePassword'));
    }
}
