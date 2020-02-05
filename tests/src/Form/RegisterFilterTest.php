<?php
/**
 * LaminasUser
 * @author Flávio Gomes da Silva Lisboa <flavio.lisboa@fgsl.eti.br>
 * @license AGPL-3.0 <https://www.gnu.org/licenses/agpl-3.0.en.html>
 */

namespace LaminasUserTest\Form;

use PHPUnit\Framework\TestCase;
use LaminasUser\Form\RegisterFilter as Filter;

class RegisterFilterTest extends TestCase
{
    /**
     * @covers LaminasUser\Form\RegisterFilter::__construct
     */
    public function testConstruct()
    {
        $options = $this->createMock('LaminasUser\Options\ModuleOptions');
        $options->expects($this->once())
                ->method('getEnableUsername')
                ->will($this->returnValue(true));
        $options->expects($this->once())
                ->method('getEnableDisplayName')
                ->will($this->returnValue(true));

        $emailValidator = $this->getMockBuilder('LaminasUser\Validator\NoRecordExists')->disableOriginalConstructor()->getMock();
        $usernameValidator = $this->getMockBuilder('LaminasUser\Validator\NoRecordExists')->disableOriginalConstructor()->getMock();

        $filter = new Filter($emailValidator, $usernameValidator, $options);

        $inputs = $filter->getInputs();
        $this->assertArrayHasKey('username', $inputs);
        $this->assertArrayHasKey('email', $inputs);
        $this->assertArrayHasKey('display_name', $inputs);
        $this->assertArrayHasKey('password', $inputs);
        $this->assertArrayHasKey('passwordVerify', $inputs);
    }

    public function testSetGetEmailValidator()
    {
        $options = $this->createMock('LaminasUser\Options\ModuleOptions');
        $validatorInit = $this->getMockBuilder('LaminasUser\Validator\NoRecordExists')->disableOriginalConstructor()->getMock();
        $validatorNew = $this->getMockBuilder('LaminasUser\Validator\NoRecordExists')->disableOriginalConstructor()->getMock();

        $filter = new Filter($validatorInit, $validatorInit, $options);

        $this->assertSame($validatorInit, $filter->getEmailValidator());
        $filter->setEmailValidator($validatorNew);
        $this->assertSame($validatorNew, $filter->getEmailValidator());
    }

    public function testSetGetUsernameValidator()
    {
        $options = $this->createMock('LaminasUser\Options\ModuleOptions');
        $validatorInit = $this->getMockBuilder('LaminasUser\Validator\NoRecordExists')->disableOriginalConstructor()->getMock();
        $validatorNew = $this->getMockBuilder('LaminasUser\Validator\NoRecordExists')->disableOriginalConstructor()->getMock();

        $filter = new Filter($validatorInit, $validatorInit, $options);

        $this->assertSame($validatorInit, $filter->getUsernameValidator());
        $filter->setUsernameValidator($validatorNew);
        $this->assertSame($validatorNew, $filter->getUsernameValidator());
    }

    public function testSetGetOptions()
    {
        $options = $this->createMock('LaminasUser\Options\ModuleOptions');
        $optionsNew = $this->createMock('LaminasUser\Options\ModuleOptions');
        $validatorInit = $this->getMockBuilder('LaminasUser\Validator\NoRecordExists')->disableOriginalConstructor()->getMock();
        $filter = new Filter($validatorInit, $validatorInit, $options);

        $this->assertSame($options, $filter->getOptions());
        $filter->setOptions($optionsNew);
        $this->assertSame($optionsNew, $filter->getOptions());
    }
}
