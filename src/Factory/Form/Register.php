<?php
/**
 * LaminasUser
 * @author Flávio Gomes da Silva Lisboa <flavio.lisboa@fgsl.eti.br>
 * @license AGPL-3.0 <https://www.gnu.org/licenses/agpl-3.0.en.html>
 */

namespace LaminasUser\Factory\Form;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use LaminasUser\Form;
use LaminasUser\Validator;

class Register implements FactoryInterface
{
    public function __invoke(ContainerInterface $serviceManager, $requestedName, array $options = null)
    {
        $options = $serviceManager->get('LaminasUser_module_options');
        $form = new Form\Register(null, $options);

        //$form->setCaptchaElement($sm->get('LaminasUser_captcha_element'));
        $form->setHydrator($serviceManager->get('LaminasUser_register_form_hydrator'));
        $form->setInputFilter(new Form\RegisterFilter(
            new Validator\NoRecordExists(array(
                'mapper' => $serviceManager->get('LaminasUser_user_mapper'),
                'key'    => 'email'
            )),
            new Validator\NoRecordExists(array(
                'mapper' => $serviceManager->get('LaminasUser_user_mapper'),
                'key'    => 'username'
            )),
            $options
        ));

        return $form;
    }
}
