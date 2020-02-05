<?php
/**
 * LaminasUser
 * @author Flávio Gomes da Silva Lisboa <flavio.lisboa@fgsl.eti.br>
 * @license AGPL-3.0 <https://www.gnu.org/licenses/agpl-3.0.en.html>
 */

namespace LaminasUserTest\Factory\Form;

use Laminas\Form\FormElementManager;
use Laminas\ServiceManager\ServiceManager;
use LaminasUser\Factory\Form\ChangeEmail as ChangeEmailFactory;
use LaminasUser\Options\ModuleOptions;
use PHPUnit\Framework\TestCase;
use LaminasUser\Mapper\User as UserMapper;

class ChangeEmailFormFactoryTest extends TestCase
{
    public function testFactory()
    {
        $serviceManager = new ServiceManager([
            'services' => [
                'LaminasUser_module_options' => new ModuleOptions,
                'LaminasUser_user_mapper' => new UserMapper
            ]
        ]);

        $formElementManager = new FormElementManager($serviceManager);
        $serviceManager->setService('FormElementManager', $formElementManager);

        $factory = new ChangeEmailFactory();

        $this->assertInstanceOf('LaminasUser\Form\ChangeEmail', $factory->__invoke($serviceManager, 'LaminasUser\Form\ChangeEmail'));
    }
}
