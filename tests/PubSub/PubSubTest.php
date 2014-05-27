<?php


namespace SoPhp\Test\PubSub;


use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit_Framework_MockObject_MockObject;
use SoPhp\Amqp\ExchangeDescriptor;
use SoPhp\PubSub\Event;
use SoPhp\PubSub\Exception\InvalidArgumentException;
use SoPhp\PubSub\PubSub;

class PubSubTest extends \PHPUnit_Framework_TestCase {
    /** @var  PubSub */
    protected $pubSub;
    /** @var  PHPUnit_Framework_MockObject_MockObject */
    protected $channelMock;
    /** @var  ExchangeDescriptor */
    protected $exchangeDescriptorMock;
    public function setUp() {
        parent::setUp();
        $this->channelMock = $this->getMock('\PhpAmqpLib\Channel\AMQPChannel', array(), array(), '', false);
        $this->exchangeDescriptorMock = $this->getMock('\SoPhp\Amqp\ExchangeDescriptor', null, array(), '', false);
        $this->pubSub = new PubSub($this->channelMock, $this->exchangeDescriptorMock);
    }

    public function testSetExchangeDescriptorHandlesStringParameter(){
        $exchangeName = uniqid();
        $pubSub = $this->pubSub;
        $this->assertEquals($this->exchangeDescriptorMock, $pubSub->getExchangeDescriptor());
        $this->pubSub->setExchangeDescriptor($exchangeName);
        $this->assertNotEquals($this->exchangeDescriptorMock, $pubSub->getExchangeDescriptor());
        $this->assertInstanceOf('\SoPhp\Amqp\ExchangeDescriptor', $pubSub->getExchangeDescriptor());
        $this->assertEquals($exchangeName, $pubSub->getExchangeDescriptor()->getName());
    }

    public function testSetExchangeDescriptorHandlesExchangeDescriptorParameter(){
        $exchangeName = uniqid();
        $ed = new ExchangeDescriptor($exchangeName);
        $pubSub = $this->pubSub;
        $this->assertEquals($this->exchangeDescriptorMock, $pubSub->getExchangeDescriptor());
        $this->pubSub->setExchangeDescriptor($ed);
        $this->assertEquals($ed, $pubSub->getExchangeDescriptor());
    }

    /**
     * @expectedException \SoPhp\PubSub\Exception\InvalidArgumentException
     */
    public function testSetExchangeDescriptorThrowsExceptionForBadParameter(){
        $this->pubSub->setExchangeDescriptor((object)array('foo'));
    }

    public function testPublishCreatesEventWhenProvidedStringForEventParam(){
        $event = uniqid();
        $params = preg_split('//', uniqid());

        // set up expectation so that if our callback never runs the test fails
        $objMock = $this->getMock('stdClass',array('test'));
        $objMock->expects($this->once())
            ->method('test')
            // set up expectation matches w/ our publish params
            ->with($event, $params);

        $this->exchangeDescriptorMock->setName(uniqid());
        $this->channelMock->expects($this->once())
            ->method('basic_publish')
            ->with($this->isInstanceOf('\PhpAmqpLib\Message\AMQPMessage'),
                $this->exchangeDescriptorMock->getName())
            ->will($this->returnCallback(function(AMQPMessage $msg) use($objMock){
                $reconstructedEvent = Event::fromJson($msg->body);
                // attempt to satisfy our expectation
                $objMock->test($reconstructedEvent->getName(),
                               $reconstructedEvent->getParams());
            }));

        $this->pubSub->publish($event, $params);
    }

    public function testPublishMergesParamsWithSuppliedEventParams(){
        $event = new Event(uniqid(), preg_split('//', uniqid()));
        $params2 = preg_split('//', uniqid());

        // set up expectation so that if our callback never runs the test fails
        $objMock = $this->getMock('stdClass',array('test'));
        $objMock->expects($this->once())
            ->method('test')
            // set up expectation matches w/ our publish params
            ->with($event->getName(), array_merge($event->getParams(), $params2));

        $this->exchangeDescriptorMock->setName(uniqid());
        $this->channelMock->expects($this->once())
            ->method('basic_publish')
            ->with($this->isInstanceOf('\PhpAmqpLib\Message\AMQPMessage'),
                $this->exchangeDescriptorMock->getName())
            ->will($this->returnCallback(function(AMQPMessage $msg) use($objMock){
                $reconstructedEvent = Event::fromJson($msg->body);
                // attempt to satisfy our expectation
                $objMock->test($reconstructedEvent->getName(),
                    $reconstructedEvent->getParams());
            }));

        $this->pubSub->publish($event, $params2);
    }

    /**
     * @expectedException \SoPhp\PubSub\Exception\InvalidArgumentException
     */
    public function testPublishThrowsExceptionWhenInvalidEventProvided(){
        $this->pubSub->publish((object)array('foo'), array());
    }

    public function testPublishInitializesMessageFirstTimeThrough(){
        $this->assertNull($this->pubSub->getMessage());
        $this->pubSub->publish(uniqid(), array());
        $this->assertNotNull($this->pubSub->getMessage());
        $this->assertInstanceOf('\PhpAmqpLib\Message\AMQPMessage', $this->pubSub->getMessage());
    }

    public function testPublishReusesMessageOnSubsequentPublishes(){
        $this->pubSub->publish(uniqid(), array());
        $message = $this->pubSub->getMessage();
        $this->pubSub->publish(uniqid(), array());
        $this->assertEquals($message, $this->pubSub->getMessage());
    }

    public function testSubscribe(){
        $event = new Event(uniqid());
        $listenerCount = rand(1, 4);
        $objMock = $this->getMock('stdClass', array('assert'));
        $objMock->expects($this->exactly($listenerCount))
            ->method('assert');

        for($i = 0; $i < $listenerCount; $i++){
            $this->pubSub->subscribe($event, function() use($objMock){
                $objMock->assert();
            });
        }

        $msg = new AMQPMessage($event->toJson());

        $this->pubSub->onMessage($msg);
    }

    /** @depends testSubscribe */
    public function testUnsubscribeCallbackForEvent(){
        $objMock = $this->getMock('stdClass', array('assert'));
        $objMock->expects($this->never())
            ->method('assert');

        $cb = function() use($objMock){
            $objMock->assert();
        };
        $event = new Event(uniqid());

        $this->pubSub->subscribe($event, $cb);
        $this->pubSub->unsubscribe($event, $cb);

        $msg = new AMQPMessage($event->toJson());

        $this->pubSub->onMessage($msg);
    }

    public function testUnsubscribeAllCallbacksForEvent(){
        $objMock = $this->getMock('stdClass', array('assert'));
        $objMock->expects($this->never())
            ->method('assert');

        $event = new Event(uniqid());
        // attach multiple different callbacks to event
        for($i = rand(1,4); $i>0; $i--){
            $this->pubSub->subscribe($event, function() use($objMock){
                $objMock->assert();
            });
        }

        $this->pubSub->unsubscribe($event);

        $msg = new AMQPMessage($event->toJson());

        $this->pubSub->onMessage($msg);
    }
}