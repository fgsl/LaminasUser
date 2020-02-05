<?php
/**
 * LaminasUser
 * @author Flávio Gomes da Silva Lisboa <flavio.lisboa@fgsl.eti.br>
 * @license AGPL-3.0 <https://www.gnu.org/licenses/agpl-3.0.en.html>
 */

namespace LaminasUserTest\Validator;

use PHPUnit\Framework\TestCase;
use LaminasUser\Validator\RecordExists as Validator;

class RecordExistsTest extends TestCase
{
    protected $validator;

    protected $mapper;

    public function setUp():void
    {
        $options = array('key' => 'username');
        $validator = new Validator($options);
        $this->validator = $validator;

        $mapper = $this->createMock('LaminasUser\Mapper\UserInterface');
        $this->mapper = $mapper;

        $validator->setMapper($mapper);
    }

    /**
     * @covers LaminasUser\Validator\RecordExists::isValid
     */
    public function testIsValid()
    {
        $this->mapper->expects($this->once())
                     ->method('findByUsername')
                     ->with('LaminasUser')
                     ->will($this->returnValue('LaminasUser'));

        $result = $this->validator->isValid('LaminasUser');
        $this->assertTrue($result);
    }

    /**
     * @covers LaminasUser\Validator\RecordExists::isValid
     */
    public function testIsInvalid()
    {
        $this->mapper->expects($this->once())
                     ->method('findByUsername')
                     ->with('LaminasUser')
                     ->will($this->returnValue(false));

        $result = $this->validator->isValid('LaminasUser');
        $this->assertFalse($result);

        $options = $this->validator->getOptions();
        $this->assertArrayHasKey(\LaminasUser\Validator\AbstractRecord::ERROR_NO_RECORD_FOUND, $options['messages']);
        $this->assertEquals($options['messageTemplates'][\LaminasUser\Validator\AbstractRecord::ERROR_NO_RECORD_FOUND], $options['messages'][\LaminasUser\Validator\AbstractRecord::ERROR_NO_RECORD_FOUND]);
    }
}
