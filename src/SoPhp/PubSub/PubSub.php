<?php


namespace SoPhp\PubSub;



use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use SoPhp\Amqp\ConsumerDescriptor;
use SoPhp\Amqp\Exception\InvalidArgumentException;
use SoPhp\Amqp\ExchangeDescriptor;
use SoPhp\Amqp\QueueDescriptor;

class PubSub implements PubSubInterface {
    /** @var  AMQPChannel */
    protected $channel;
    /** @var  ExchangeDescriptor */
    protected $exchangeDescriptor;
    /** @var  QueueDescriptor */
    protected $queueDescriptor;
    /** @var  AMQPMessage */
    protected $message;
    /** @var bool  */
    protected $declared = false;
    /** @var array */
    protected $listeners = array();

    /**
     * @return AMQPChannel
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @param AMQPChannel $channel
     * @return self
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;
        return $this;
    }

    /**
     * @return ExchangeDescriptor
     */
    public function getExchangeDescriptor()
    {
        return $this->exchangeDescriptor;
    }

    /**
     * @param ExchangeDescriptor|string $exchangeDescriptor
     * @return self
     */
    public function setExchangeDescriptor($exchangeDescriptor)
    {
        if($exchangeDescriptor instanceof ExchangeDescriptor){
            $this->exchangeDescriptor = $exchangeDescriptor;
        } else if(is_string($exchangeDescriptor)) {
            $this->exchangeDescriptor = new ExchangeDescriptor($exchangeDescriptor);
        } else {
            throw new InvalidArgumentException("Exchange should be a string name of exchange or an ExchangeDescriptor");
        }
        return $this;
    }

    /**
     * @return QueueDescriptor
     */
    public function getQueueDescriptor()
    {
        return $this->queueDescriptor;
    }

    /**
     * @param QueueDescriptor $descriptor
     */
    public function setQueueDescriptor(QueueDescriptor $descriptor){
        $this->queueDescriptor = $descriptor;
    }


    /**
     * @param AMQPChannel $channel
     * @param ExchangeDescriptor|string $exchangeDescriptor
     */
    public function __construct(AMQPChannel $channel, $exchangeDescriptor){
        $this->setChannel($channel);
        $this->setExchangeDescriptor($exchangeDescriptor);
    }

    /**
     * @param Event|string $event
     * @param array $params
     * @throws \SoPhp\PubSub\Exception\InvalidEventException
     */
    public function publish($event, $params = array())
    {
        if($event instanceof Event) {
            $body = $event->toJson();
        } else if(is_string($event)){
            $evt = new Event($event, $params);
            $body = $evt->toJson();
        } else {
            throw new InvalidArgumentException("Event must either be an instance of Event or a string");
        }

        if(!$this->message){
            $this->message = new AMQPMessage($body, array('content_type' => 'text/plain', 'delivery_mode' => 2));
        } else {
            $this->message->setBody($body);
        }

        $this->getChannel()->basic_publish($this->message, $this->getExchangeDescriptor()->getName());
    }

    /**
     * @param $event
     * @param callable $callback
     * @throws \SoPhp\PubSub\Exception\InvalidCallbackException
     */
    public function subscribe($event, $callback)
    {
        if(!isset($this->listeners[$event])) {
            $this->listeners[$event] = array();
        }
        $this->listeners[$event][] = $callback;

        $this->initAmqp();
    }

    /**
     * Remove listener(s) attached to event
     * @param $event
     * @param callable|null $callback
     */
    public function unsubscribe($event, $callback = null)
    {
        if(!isset($this->listeners[$event])) {
            return;
        }

        if($callback == null){
            unset($this->listeners[$event]);
            return;
        }

        while(in_array($callback, $this->listeners[$event])){
            $index = array_search($callback, $this->listeners[$event]);
            unset($this->listeners[$event][$index]);
        }
        return;
    }

    /**
     * @param AMQPMessage $message
     */
    public function onMessage(AMQPMessage $message){
        $event = Event::fromJson($message->body);

        if(isset($this->listeners[$event->getName()])){
            foreach($this->listeners[$event->getName()] as $callback){
                call_user_func($callback, $event, $message);
            }
        }
    }

    protected function initAmqp(){
        if(!$this->declared){
            $ed = $this->getExchangeDescriptor();
            $ch = $this->getChannel();
            $ed->declareExchange($ch);

            $qd = new QueueDescriptor();
            $qd->setAutoDelete(true);
            $this->setQueueDescriptor($qd);
            $qd->declareQueue($ch);

            $ch->queue_bind($qd->getName(), $ed->getName());

            $cd = new ConsumerDescriptor(array($this, 'onMessage'), $qd->getName());
            $cd->setNoAck(true);
            $cd->consume($ch);
        }
    }
}