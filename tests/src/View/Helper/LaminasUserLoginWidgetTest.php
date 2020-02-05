<?php
/**
 * LaminasUser
 * @author Flávio Gomes da Silva Lisboa <flavio.lisboa@fgsl.eti.br>
 * @license AGPL-3.0 <https://www.gnu.org/licenses/agpl-3.0.en.html>
 */

namespace LaminasUserTest\View\Helper;

use LaminasUser\View\Helper\LaminasUserLoginWidget as ViewHelper;
use PHPUnit\Framework\TestCase;

class LaminasUserLoginWidgetTest extends TestCase
{
    protected $helper;

    protected $view;

    public function setUp():void
    {
        $this->helper = new ViewHelper;

        $view = $this->createMock('Laminas\View\Renderer\RendererInterface');
        $this->view = $view;

        $this->helper->setView($view);
    }

    public function providerTestInvokeWithRender()
    {
        $attr = array();
        $attr[] = array(
            array(
                'render' => true,
                'redirect' => 'LaminasUser'
            ),
            array(
                'loginForm' => null,
                'redirect' => 'LaminasUser'
            ),
        );
        $attr[] = array(
            array(
                'redirect' => 'LaminasUser'
            ),
            array(
                'loginForm' => null,
                'redirect' => 'LaminasUser'
            ),
        );
        $attr[] = array(
            array(
                'render' => true,
            ),
            array(
                'loginForm' => null,
                'redirect' => false
            ),
        );

        return $attr;
    }

    /**
     * @covers LaminasUser\View\Helper\LaminasUserLoginWidget::__invoke
     * @dataProvider providerTestInvokeWithRender
     */
    public function testInvokeWithRender($option, $expect)
    {
        /**
         * @var $viewModel \Laminas\View\Model\ViewModels
         */
        $viewModel = null;

        $this->view->expects($this->at(0))
             ->method('render')
             ->will($this->returnCallback(function ($vm) use (&$viewModel) {
                 $viewModel = $vm;
                 return "test";
             }));

        $result = $this->helper->__invoke($option);

        $this->assertNotInstanceOf('Laminas\View\Model\ViewModel', $result);
        $this->assertIsString($result);


        $this->assertInstanceOf('Laminas\View\Model\ViewModel', $viewModel);
        foreach ($expect as $name => $value) {
            $this->assertEquals($value, $viewModel->getVariable($name, "testDefault"));
        }
    }

    /**
     * @covers LaminasUser\View\Helper\LaminasUserLoginWidget::__invoke
     */
    public function testInvokeWithoutRender()
    {
        $result = $this->helper->__invoke(array(
            'render' => false,
            'redirect' => 'LaminasUser'
        ));

        $this->assertInstanceOf('Laminas\View\Model\ViewModel', $result);
        $this->assertEquals('LaminasUser', $result->redirect);
    }

    /**
     * @covers LaminasUser\View\Helper\LaminasUserLoginWidget::setLoginForm
     * @covers LaminasUser\View\Helper\LaminasUserLoginWidget::getLoginForm
     */
    public function testSetGetLoginForm()
    {
        $loginForm = $this->getMockBuilder('LaminasUser\Form\Login')->disableOriginalConstructor()->getMock();

        $this->helper->setLoginForm($loginForm);
        $this->assertInstanceOf('LaminasUser\Form\Login', $this->helper->getLoginForm());
    }

    /**
     * @covers LaminasUser\View\Helper\LaminasUserLoginWidget::setViewTemplate
     */
    public function testSetViewTemplate()
    {
        $this->helper->setViewTemplate('LaminasUser');

        $reflectionClass = new \ReflectionClass('LaminasUser\View\Helper\LaminasUserLoginWidget');
        $reflectionProperty = $reflectionClass->getProperty('viewTemplate');
        $reflectionProperty->setAccessible(true);

        $this->assertEquals('LaminasUser', $reflectionProperty->getValue($this->helper));
    }
}
