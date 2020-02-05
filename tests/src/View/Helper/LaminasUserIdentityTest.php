<?php
/**
 * LaminasUser
 * @author Flávio Gomes da Silva Lisboa <flavio.lisboa@fgsl.eti.br>
 * @license AGPL-3.0 <https://www.gnu.org/licenses/agpl-3.0.en.html>
 */

namespace LaminasUserTest\View\Helper;

use PHPUnit\Framework\TestCase;
use LaminasUser\View\Helper\LaminasUserIdentity as ViewHelper;

class LaminasUserIdentityTest extends TestCase
{
    protected $helper;

    protected $authService;

    public function setUp():void
    {
        $helper = new ViewHelper;
        $this->helper = $helper;

        $authService = $this->createMock('Laminas\Authentication\AuthenticationService');
        $this->authService = $authService;

        $helper->setAuthService($authService);
    }

    /**
     * @covers LaminasUser\View\Helper\LaminasUserIdentity::__invoke
     */
    public function testInvokeWithIdentity()
    {
        $this->authService->expects($this->once())
                          ->method('hasIdentity')
                          ->will($this->returnValue(true));
        $this->authService->expects($this->once())
                          ->method('getIdentity')
                          ->will($this->returnValue('LaminasUser'));

        $result = $this->helper->__invoke();

        $this->assertEquals('LaminasUser', $result);
    }

    /**
     * @covers LaminasUser\View\Helper\LaminasUserIdentity::__invoke
     */
    public function testInvokeWithoutIdentity()
    {
        $this->authService->expects($this->once())
                          ->method('hasIdentity')
                          ->will($this->returnValue(false));

        $result = $this->helper->__invoke();

        $this->assertFalse($result);
    }

    /**
     * @covers LaminasUser\View\Helper\LaminasUserIdentity::setAuthService
     * @covers LaminasUser\View\Helper\LaminasUserIdentity::getAuthService
     */
    public function testSetGetAuthService()
    {
        //We set the authservice in setUp, so we dont have to set it again
        $this->assertSame($this->authService, $this->helper->getAuthService());
    }
}
