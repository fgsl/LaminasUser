<?php
/**
 * LaminasUser
 * @author Flávio Gomes da Silva Lisboa <flavio.lisboa@fgsl.eti.br>
 * @license AGPL-3.0 <https://www.gnu.org/licenses/agpl-3.0.en.html>
 */
namespace LaminasUserTest\Authentication\Adapter;

use LaminasUser\Authentication\Adapter\AdapterChainServiceFactory;
use PHPUnit\Framework\TestCase;

class AdapterChainServiceFactoryTest extends TestCase
{
    /**
     * The object to be tested.
     *
     * @var AdapterChainServiceFactory
     */
    protected $factory;

    /**
     * @var \Laminas\ServiceManager\ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * @var \LaminasUser\Options\ModuleOptions
     */
    protected $options;

    /**
     * @var \Laminas\EventManager\EventManagerInterface
     */
    protected $eventManager;


    protected $serviceLocatorArray;

    public function helperServiceLocator($index)
    {
        return $this->serviceLocatorArray[$index];
    }

    /**
     * Prepare the object to be tested.
     */
    protected function setUp():void
    {
        $this->serviceLocator = $this->createMock('Laminas\ServiceManager\ServiceLocatorInterface');

        $this->options = $this->getMockBuilder('LaminasUser\Options\ModuleOptions')
            ->disableOriginalConstructor()
            ->getMock();

        $this->serviceLocator->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(array($this,'helperServiceLocator')));

        $this->eventManager = $this->createMock('Laminas\EventManager\EventManager');

        $this->serviceLocatorArray = array (
            'LaminasUser_module_options'=>$this->options,
            'EventManager' => $this->eventManager
        );        

        $this->factory = new AdapterChainServiceFactory();
    }

    /**
     * @covers \LaminasUser\Authentication\Adapter\AdapterChainServiceFactory::createService
     */
    public function testCreateService()
    {
        $adapter = array(
            'adapter1'=> $this->createMock(
                'LaminasUser\Authentication\Adapter\AbstractAdapter',
                array('authenticate', 'logout')
            ),
            'adapter2'=> $this->createMock(
                'LaminasUser\Authentication\Adapter\AbstractAdapter',
                array('authenticate', 'logout')
            )
        );
        $adapterNames = array(100=>'adapter1', 200=>'adapter2');

        $this->serviceLocatorArray = array_merge($this->serviceLocatorArray, $adapter);

        $this->options->expects($this->once())
                      ->method('getAuthAdapters')
                      ->will($this->returnValue($adapterNames));

        $adapterChain = $this->factory->__invoke($this->serviceLocator, 'LaminasUser\Authentication\Adapter\AdapterChain');

        $this->assertInstanceOf('LaminasUser\Authentication\Adapter\AdapterChain', $adapterChain);
    }

    /**
     * @covers \LaminasUser\Authentication\Adapter\AdapterChainServiceFactory::setOptions
     * @covers \LaminasUser\Authentication\Adapter\AdapterChainServiceFactory::getOptions
     */
    public function testGetOptionWithSetter()
    {
        $this->factory->setOptions($this->options);

        $options = $this->factory->getOptions();

        $this->assertInstanceOf('LaminasUser\Options\ModuleOptions', $options);
        $this->assertSame($this->options, $options);


        $options2 = clone $this->options;
        $this->factory->setOptions($options2);
        $options = $this->factory->getOptions();

        $this->assertInstanceOf('LaminasUser\Options\ModuleOptions', $options);
        $this->assertNotSame($this->options, $options);
        $this->assertSame($options2, $options);
    }

    /**
     * @covers \LaminasUser\Authentication\Adapter\AdapterChainServiceFactory::getOptions
     */
    public function testGetOptionWithLocator()
    {
        $options = $this->factory->getOptions($this->serviceLocator);

        $this->assertInstanceOf('LaminasUser\Options\ModuleOptions', $options);
        $this->assertSame($this->options, $options);
    }

    /**
     * @covers \LaminasUser\Authentication\Adapter\AdapterChainServiceFactory::getOptions
     */
    public function testGetOptionFailing()
    {
        $this->expectException(\LaminasUser\Authentication\Adapter\Exception\OptionsNotFoundException::class);
        $this->factory->getOptions();
    }
}
