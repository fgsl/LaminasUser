<?php
/**
 * LaminasUser
 * @author Flávio Gomes da Silva Lisboa <flavio.lisboa@fgsl.eti.br>
 * @license AGPL-3.0 <https://www.gnu.org/licenses/agpl-3.0.en.html>
 */

namespace LaminasUserTest\View\Helper;

use PHPUnit\Framework\TestCase;
use LaminasUser\View\Helper\LaminasUserDisplayName as ViewHelper;
use LaminasUser;

class LaminasUserDisplayNameTest extends TestCase
{
    protected $helper;

    protected $authService;

    protected $user;

    public function setUp():void
    {
        $helper = new ViewHelper;
        $this->helper = $helper;

        $authService = $this->createMock('Laminas\Authentication\AuthenticationService');
        $this->authService = $authService;

        $user = $this->createMock('LaminasUser\Entity\User');
        $this->user = $user;

        $helper->setAuthService($authService);
    }

    /**
     * @covers LaminasUser\View\Helper\LaminasUserDisplayName::__invoke
     */
    public function testInvokeWithoutUserAndNotLoggedIn()
    {
        $this->authService->expects($this->once())
                          ->method('hasIdentity')
                          ->will($this->returnValue(false));

        $result = $this->helper->__invoke(null);

        $this->assertFalse($result);
    }

    /**
     * @covers LaminasUser\View\Helper\LaminasUserDisplayName::__invoke 
     */
    public function testInvokeWithoutUserButLoggedInWithWrongUserObject()
    {
        $this->expectException(LaminasUser\Exception\DomainException::class);
        $this->authService->expects($this->once())
                          ->method('hasIdentity')
                          ->will($this->returnValue(true));
        $this->authService->expects($this->once())
                          ->method('getIdentity')
                          ->will($this->returnValue(new \StdClass));

        $this->helper->__invoke(null);
    }

    /**
     * @covers LaminasUser\View\Helper\LaminasUserDisplayName::__invoke
     */
    public function testInvokeWithoutUserButLoggedInWithDisplayName()
    {
        $this->user->expects($this->once())
                   ->method('getDisplayName')
                   ->will($this->returnValue('LaminasUser'));

        $this->authService->expects($this->once())
                          ->method('hasIdentity')
                          ->will($this->returnValue(true));
        $this->authService->expects($this->once())
                          ->method('getIdentity')
                          ->will($this->returnValue($this->user));

        $result = $this->helper->__invoke(null);

        $this->assertEquals('LaminasUser', $result);
    }

    /**
     * @covers LaminasUser\View\Helper\LaminasUserDisplayName::__invoke
     */
    public function testInvokeWithoutUserButLoggedInWithoutDisplayNameButWithUsername()
    {
        $this->user->expects($this->once())
                   ->method('getDisplayName')
                   ->will($this->returnValue(null));
        $this->user->expects($this->once())
                   ->method('getUsername')
                   ->will($this->returnValue('LaminasUser'));

        $this->authService->expects($this->once())
                          ->method('hasIdentity')
                          ->will($this->returnValue(true));
        $this->authService->expects($this->once())
                          ->method('getIdentity')
                          ->will($this->returnValue($this->user));

        $result = $this->helper->__invoke(null);

        $this->assertEquals('LaminasUser', $result);
    }

    /**
     * @covers LaminasUser\View\Helper\LaminasUserDisplayName::__invoke
     */
    public function testInvokeWithoutUserButLoggedInWithoutDisplayNameAndWithOutUsernameButWithEmail()
    {
        $this->user->expects($this->once())
                   ->method('getDisplayName')
                   ->will($this->returnValue(null));
        $this->user->expects($this->once())
                   ->method('getUsername')
                   ->will($this->returnValue(null));
        $this->user->expects($this->once())
                   ->method('getEmail')
                   ->will($this->returnValue('LaminasUser@LaminasUser.com'));

        $this->authService->expects($this->once())
                          ->method('hasIdentity')
                          ->will($this->returnValue(true));
        $this->authService->expects($this->once())
                          ->method('getIdentity')
                          ->will($this->returnValue($this->user));

        $result = $this->helper->__invoke(null);

        $this->assertEquals('LaminasUser', $result);
    }

    /**
     * @covers LaminasUser\View\Helper\LaminasUserDisplayName::setAuthService
     * @covers LaminasUser\View\Helper\LaminasUserDisplayName::getAuthService
     */
    public function testSetGetAuthService()
    {
        // We set the authservice in setUp, so we dont have to set it again
        $this->assertSame($this->authService, $this->helper->getAuthService());
    }
}
