<?php
/**
 * LaminasUser
 * @author Flávio Gomes da Silva Lisboa <flavio.lisboa@fgsl.eti.br>
 * @license AGPL-3.0 <https://www.gnu.org/licenses/agpl-3.0.en.html>
 */

namespace LaminasUserTest\Form;

use PHPUnit\Framework\TestCase;
use LaminasUser\Form\ChangeEmail as Form;

class ChangeEmailTest extends TestCase
{
    /**
     * @covers LaminasUser\Form\ChangeEmail::__construct
     */
    public function testConstruct()
    {
        $options = $this->createMock('LaminasUser\Options\AuthenticationOptionsInterface');

        $form = new Form(null, $options);

        $elements = $form->getElements();

        $this->assertArrayHasKey('identity', $elements);
        $this->assertArrayHasKey('newIdentity', $elements);
        $this->assertArrayHasKey('newIdentityVerify', $elements);
        $this->assertArrayHasKey('credential', $elements);
    }

    /**
     * @covers LaminasUser\Form\ChangeEmail::getAuthenticationOptions
     * @covers LaminasUser\Form\ChangeEmail::setAuthenticationOptions
     */
    public function testSetGetAuthenticationOptions()
    {
        $options = $this->createMock('LaminasUser\Options\AuthenticationOptionsInterface');
        $form = new Form(null, $options);

        $this->assertSame($options, $form->getAuthenticationOptions());
    }
}
