<?php
/**
 * LaminasUser
 * @author Flávio Gomes da Silva Lisboa <flavio.lisboa@fgsl.eti.br>
 * @license AGPL-3.0 <https://www.gnu.org/licenses/agpl-3.0.en.html>
 */
namespace LaminasUser;

use Laminas\ModuleManager\Feature\ConfigProviderInterface;
use Laminas\ModuleManager\Feature\ControllerPluginProviderInterface;
use Laminas\ModuleManager\Feature\ControllerProviderInterface;
use Laminas\ModuleManager\Feature\ServiceProviderInterface;

class Module implements
    ControllerProviderInterface,
    ControllerPluginProviderInterface,
    ConfigProviderInterface,
    ServiceProviderInterface
{
    public function getConfig($env = null)
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getControllerPluginConfig()
    {
        return array(
            'factories' => array(
                'LaminasUserAuthentication' => \LaminasUser\Factory\Controller\Plugin\LaminasUserAuthentication::class,
            ),
        );
    }

    public function getControllerConfig()
    {
        return array(
            'factories' => array(
                'LaminasUser' => \LaminasUser\Factory\Controller\UserControllerFactory::class,
            ),
        );
    }

    public function getViewHelperConfig()
    {
        return array(
            'factories' => array(
                'LaminasUserDisplayName' => \LaminasUser\Factory\View\Helper\LaminasUserDisplayName::class,
                'LaminasUserIdentity' => \LaminasUser\Factory\View\Helper\LaminasUserIdentity::class,
                'LaminasUserLoginWidget' => \LaminasUser\Factory\View\Helper\LaminasUserLoginWidget::class,
            ),
        );

    }

    public function getServiceConfig()
    {
        return array(
            'aliases' => array(
                'LaminasUser_Laminas_db_adapter' => \Laminas\Db\Adapter\Adapter::class,
            ),
            'invokables' => array(
                'LaminasUser_register_form_hydrator' => \Laminas\Hydrator\ClassMethods::class,
            ),
            'factories' => array(
                'LaminasUser_redirect_callback' => \LaminasUser\Factory\Controller\RedirectCallbackFactory::class,
                'LaminasUser_module_options' => \LaminasUser\Factory\Options\ModuleOptions::class,
                'LaminasUser\Authentication\Adapter\AdapterChain' => \LaminasUser\Authentication\Adapter\AdapterChainServiceFactory::class,

                // We alias this one because it's LaminasUser's instance of
                // Laminas\Authentication\AuthenticationService. We don't want to
                // hog the FQCN service alias for a Laminas\* class.
                'LaminasUser_auth_service' => \LaminasUser\Factory\AuthenticationService::class,

                'LaminasUser_user_hydrator' => \LaminasUser\Factory\UserHydrator::class,
                'LaminasUser_user_mapper' => \LaminasUser\Factory\Mapper\User::class,

                'LaminasUser_login_form' => \LaminasUser\Factory\Form\Login::class,
                'LaminasUser_register_form' => \LaminasUser\Factory\Form\Register::class,
                'LaminasUser_change_password_form' => \LaminasUser\Factory\Form\ChangePassword::class,
                'LaminasUser_change_email_form' => \LaminasUser\Factory\Form\ChangeEmail::class,

                'LaminasUser\Authentication\Adapter\Db' => \LaminasUser\Factory\Authentication\Adapter\DbFactory::class,
                'LaminasUser\Authentication\Storage\Db' => \LaminasUser\Factory\Authentication\Storage\DbFactory::class,

                'LaminasUser_user_service' => \LaminasUser\Factory\Service\UserFactory::class,
            ),
        );
    }
}
