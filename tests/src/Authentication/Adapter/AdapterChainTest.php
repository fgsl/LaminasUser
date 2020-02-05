<?php
/**
 * LaminasUser
 * @author Flávio Gomes da Silva Lisboa <flavio.lisboa@fgsl.eti.br>
 * @license AGPL-3.0 <https://www.gnu.org/licenses/agpl-3.0.en.html>
 */
namespace LaminasUserTest\Authentication\Adapter;

use Laminas\EventManager\EventInterface;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\SharedEventManagerInterface;
use LaminasUser\Authentication\Adapter\AdapterChain;
use LaminasUser\Authentication\Adapter\AdapterChainEvent;
use Laminas\Stdlib\RequestInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Laminas\EventManager\ResponseCollection;

class AdapterChainTest extends TestCase
{
    /**
     * The object to be tested.
     *
     * @var AdapterChain
     */
    protected $adapterChain;

    /**
     * Mock event manager.
     *
     * @var MockObject|EventManagerInterface
     */
    protected $eventManager;

    /**
     * Mock event manager.
     *
     * @var MockObject|SharedEventManagerInterface
     */
    protected $sharedEventManager;

    /**
     * For tests where an event is required.
     *
     * @var MockObject|EventInterface
     */
    protected $event;

    /**
     * Used when testing prepareForAuthentication.
     *
     * @var MockObject|RequestInterface
     */
    protected $request;

    /**
     * Prepare the objects to be tested.
     */
    protected function setUp():void
    {
        $this->event = null;
        $this->request = null;

        $this->adapterChain = new AdapterChain();

        $this->sharedEventManager = $this->createMock('Laminas\EventManager\SharedEventManagerInterface');
        //$this->sharedEventManager->expects($this->any())->method('getListeners')->will($this->returnValue([]));

        $this->eventManager = $this->createMock('Laminas\EventManager\EventManagerInterface');
        $this->eventManager->expects($this->any())->method('getSharedManager')->will($this->returnValue($this->sharedEventManager));
        $this->eventManager->expects($this->any())->method('setIdentifiers');
        $this->eventManager->expects($this->any())->method('triggerEventUntil')->will($this->returnValue($this->createMock(ResponseCollection::class)));

        $this->adapterChain->setEventManager($this->eventManager);
    }

    /**
     * @covers \LaminasUser\Authentication\Adapter\AdapterChain::authenticate
     */
    public function testAuthenticate()
    {
        $event = $this->createMock('LaminasUser\Authentication\Adapter\AdapterChainEvent');
        $event->expects($this->once())
              ->method('getCode')
              ->will($this->returnValue(123));
        $event->expects($this->once())
              ->method('getIdentity')
              ->will($this->returnValue('identity'));
        $event->expects($this->once())
              ->method('getMessages')
              ->will($this->returnValue(array()));

        $this->sharedEventManager->expects($this->once())
             ->method('getListeners')
             ->with($this->equalTo(['authenticate']), $this->equalTo('authenticate'))
             ->will($this->returnValue(array()));

        $this->adapterChain->setEvent($event);
        $result = $this->adapterChain->authenticate();

        $this->assertInstanceOf('Laminas\Authentication\Result', $result);
        $this->assertEquals($result->getIdentity(), 'identity');
        $this->assertEquals($result->getMessages(), array());
    }

    /**
     * @covers \LaminasUser\Authentication\Adapter\AdapterChain::resetAdapters
     */
    public function testResetAdapters()
    {
        $listeners = array();

        for ($i=1; $i<=3; $i++) {
            $storage = $this->createMock('LaminasUser\Authentication\Storage\Db');
            $storage->expects($this->once())
                    ->method('clear');

            $adapter = $this->createMock('LaminasUser\Authentication\Adapter\ChainableAdapter');
            $adapter->expects($this->once())
                    ->method('getStorage')
                    ->will($this->returnValue($storage));

            $callback = [$adapter, 'authenticate'];
            $listeners[] = $callback;
        }

        $this->sharedEventManager->expects($this->once())
             ->method('getListeners')
             ->with($this->equalTo(['authenticate']), $this->equalTo('authenticate'))
             ->will($this->returnValue($listeners));

        $result = $this->adapterChain->resetAdapters();

        $this->assertInstanceOf('LaminasUser\Authentication\Adapter\AdapterChain', $result);
    }

    /**
     * Get through the first part of SetUpPrepareForAuthentication
     */
    protected function setUpPrepareForAuthentication()
    {
        $this->request = $this->createMock('Laminas\Stdlib\RequestInterface');
        $this->event = $this->createMock('LaminasUser\Authentication\Adapter\AdapterChainEvent');

        $this->event->expects($this->once())->method('setRequest')->with($this->request);

        /**
         * @var $response \Laminas\EventManager\ResponseCollection
         */
        $responses = $this->createMock('Laminas\EventManager\ResponseCollection');

        $this->adapterChain->setEvent($this->event);

        return $responses;
    }

    /**
     * Provider for testPrepareForAuthentication()
     *
     * @return array
     */
    public function identityProvider()
    {
        return array(
            array(true, true),
            array(false, false),
        );
    }

    /**
     * Tests prepareForAuthentication when falls through events.
     *
     * @param mixed $identity
     * @param bool  $expected
     *
     * @dataProvider identityProvider
     * @covers \LaminasUser\Authentication\Adapter\AdapterChain::prepareForAuthentication
     */
    public function testPrepareForAuthentication($identity, $expected)
    {
        $result = $this->setUpPrepareForAuthentication();

        $result->expects($this->never())->method('stopped')->will($this->returnValue(false));

        $this->event->expects($this->once())->method('getIdentity')->will($this->returnValue($identity));

        $this->assertEquals(
            $expected,
            $this->adapterChain->prepareForAuthentication($this->request),
            'Asserting prepareForAuthentication() returns true'
        );
    }

    /**
     * Test prepareForAuthentication() when the returned collection contains stopped.
     *
     * @covers \LaminasUser\Authentication\Adapter\AdapterChain::prepareForAuthentication
     */
    public function testPrepareForAuthenticationWithStoppedEvent()
    {
        $result = $this->setUpPrepareForAuthentication();

        $result->expects($this->never())->method('stopped')->will($this->returnValue(true));

        $lastResponse = $this->createMock('Laminas\Stdlib\ResponseInterface');
        $result->expects($this->never())->method('last')->will($this->returnValue($lastResponse));

        $this->assertNotEquals(
            $lastResponse,
            $this->adapterChain->prepareForAuthentication($this->request),
            'Asserting the Response returned from the event is returned'
        );
    }

    /**
     * Test prepareForAuthentication() when the returned collection contains stopped.
     *
     * @covers \LaminasUser\Authentication\Adapter\AdapterChain::prepareForAuthentication
     * @expectException \LaminasUser\Exception\AuthenticationEventException
     */
    public function testPrepareForAuthenticationWithBadEventResult()
    {
        $result = $this->setUpPrepareForAuthentication();

        $result->expects($this->never())->method('stopped')->will($this->returnValue(true));

        $lastResponse = 'random-value';
        $result->expects($this->never())->method('last')->will($this->returnValue($lastResponse));

        $this->adapterChain->prepareForAuthentication($this->request);
    }

    /**
     * Test getEvent() when no event has previously been set.
     *
     * @covers \LaminasUser\Authentication\Adapter\AdapterChain::getEvent
     */
    public function testGetEventWithNoEventSet()
    {
        $event = $this->adapterChain->getEvent();

        $this->assertInstanceOf(
            'LaminasUser\Authentication\Adapter\AdapterChainEvent',
            $event,
            'Asserting the adapter in an instance of LaminasUser\Authentication\Adapter\AdapterChainEvent'
        );
        $this->assertEquals(
            $this->adapterChain,
            $event->getTarget(),
            'Asserting the Event target is the AdapterChain'
        );
    }

    /**
     * Test getEvent() when an event has previously been set.
     *
     * @covers \LaminasUser\Authentication\Adapter\AdapterChain::setEvent
     * @covers \LaminasUser\Authentication\Adapter\AdapterChain::getEvent
     */
    public function testGetEventWithEventSet()
    {
        $event = new \LaminasUser\Authentication\Adapter\AdapterChainEvent();

        $this->adapterChain->setEvent($event);

        $this->assertEquals(
            $event,
            $this->adapterChain->getEvent(),
            'Asserting the event fetched is the same as the event set'
        );
    }

    /**
     * Tests the mechanism for casting one event type to AdapterChainEvent
     *
     * @covers \LaminasUser\Authentication\Adapter\AdapterChain::setEvent
     */
    public function testSetEventWithDifferentEventType()
    {
        $testParams = array('testParam' => 'testValue');

        $event = new \Laminas\EventManager\Event;
        $event->setParams($testParams);

        $this->adapterChain->setEvent($event);
        $returnEvent = $this->adapterChain->getEvent();

        $this->assertInstanceOf(
            'LaminasUser\Authentication\Adapter\AdapterChainEvent',
            $returnEvent,
            'Asserting the adapter in an instance of LaminasUser\Authentication\Adapter\AdapterChainEvent'
        );

        $this->assertEquals(
            $testParams,
            $returnEvent->getParams(),
            'Asserting event parameters match'
        );
    }

    /**
     * Test the logoutAdapters method.
     *
     * @depends testGetEventWithEventSet
     * @covers \LaminasUser\Authentication\Adapter\AdapterChain::logoutAdapters
     */
    public function testLogoutAdapters()
    {
        $event = new AdapterChainEvent();

        $this->eventManager
            ->expects($this->once())
            ->method('triggerEvent')
            ->withAnyParameters('logout', $event);

        $this->adapterChain->setEvent($event);
        $this->adapterChain->logoutAdapters();
    }
}
