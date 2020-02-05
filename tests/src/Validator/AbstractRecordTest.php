<?php
/**
 * LaminasUser
 * @author Flávio Gomes da Silva Lisboa <flavio.lisboa@fgsl.eti.br>
 * @license AGPL-3.0 <https://www.gnu.org/licenses/agpl-3.0.en.html>
 */

namespace LaminasUserTest\Validator;

use LaminasUserTest\Validator\TestAsset\AbstractRecordExtension;
use PHPUnit\Framework\TestCase;

class AbstractRecordTest extends TestCase
{
    /**
     * @covers LaminasUser\Validator\AbstractRecord::__construct
     */
    public function testConstruct()
    {
        $options = array('key'=>'value');
        $instance = new AbstractRecordExtension($options);
        $this->assertTrue($instance instanceof AbstractRecordExtension);
    }

    /**
     * @covers LaminasUser\Validator\AbstractRecord::__construct
     */
    public function testConstructEmptyArray()
    {
        $this->expectException(\LaminasUser\Validator\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('No key provided');
        $options = array();
        new AbstractRecordExtension($options);
    }

    /**
     * @covers LaminasUser\Validator\AbstractRecord::getMapper
     * @covers LaminasUser\Validator\AbstractRecord::setMapper
     */
    public function testGetSetMapper()
    {
        $options = array('key' => '');
        $validator = new AbstractRecordExtension($options);

        $this->assertNull($validator->getMapper());

        $mapper = $this->createMock('LaminasUser\Mapper\UserInterface');
        $validator->setMapper($mapper);
        $this->assertSame($mapper, $validator->getMapper());
    }

    /**
     * @covers LaminasUser\Validator\AbstractRecord::getKey
     * @covers LaminasUser\Validator\AbstractRecord::setKey
     */
    public function testGetSetKey()
    {
        $options = array('key' => 'username');
        $validator = new AbstractRecordExtension($options);

        $this->assertEquals('username', $validator->getKey());

        $validator->setKey('email');
        $this->assertEquals('email', $validator->getKey());
    }

    /**
     * @covers LaminasUser\Validator\AbstractRecord::query
     */
    public function testQueryWithInvalidKey()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Invalid key used in LaminasUser validator');
        $options = array('key' => 'LaminasUser');
        $validator = new AbstractRecordExtension($options);

        $method = new \ReflectionMethod('LaminasUserTest\Validator\TestAsset\AbstractRecordExtension', 'query');
        $method->setAccessible(true);

        $method->invoke($validator, array('test'));
    }

    /**
     * @covers LaminasUser\Validator\AbstractRecord::query
     */
    public function testQueryWithKeyUsername()
    {
        $options = array('key' => 'username');
        $validator = new AbstractRecordExtension($options);

        $mapper = $this->createMock('LaminasUser\Mapper\UserInterface');
        $mapper->expects($this->once())
               ->method('findByUsername')
               ->with('test')
               ->will($this->returnValue('LaminasUser'));

        $validator->setMapper($mapper);

        $method = new \ReflectionMethod('LaminasUserTest\Validator\TestAsset\AbstractRecordExtension', 'query');
        $method->setAccessible(true);

        $result = $method->invoke($validator, 'test');

        $this->assertEquals('LaminasUser', $result);
    }

    /**
     * @covers LaminasUser\Validator\AbstractRecord::query
     */
    public function testQueryWithKeyEmail()
    {
        $options = array('key' => 'email');
        $validator = new AbstractRecordExtension($options);

        $mapper = $this->createMock('LaminasUser\Mapper\UserInterface');
        $mapper->expects($this->once())
            ->method('findByEmail')
            ->with('test@test.com')
            ->will($this->returnValue('LaminasUser'));

        $validator->setMapper($mapper);

        $method = new \ReflectionMethod('LaminasUserTest\Validator\TestAsset\AbstractRecordExtension', 'query');
        $method->setAccessible(true);

        $result = $method->invoke($validator, 'test@test.com');

        $this->assertEquals('LaminasUser', $result);
    }
}
