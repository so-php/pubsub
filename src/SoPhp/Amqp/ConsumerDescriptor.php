<?php


namespace SoPhp\Amqp;


use PhpAmqpLib\Channel\AMQPChannel;
use SoPhp\Amqp\Exception\InvalidArgumentException;

class ConsumerDescriptor {
    /** @var  string */
    protected $queueName;
    /** @var  string */
    protected $tag;
    /** @var  bool */
    protected $noLocal;
    /** @var  bool */
    protected $noAck;
    /** @var  bool */
    protected $exclusive;
    /** @var  bool */
    protected $noWait;
    /** @var  callable */
    protected $callback;
    /** @var  string */
    protected $ticket;
    /** @var  array */
    protected $arguments;

    /**
     * @param callable $callback
     * @param string $queueName
     * @param string $tag
     * @param bool $noLocal
     * @param bool $noAck
     * @param bool $exclusive
     * @param bool $noWait
     * @param string $ticket
     * @param array $arguments
     */
    public function __construct($callback, $queueName = '', $tag = '',
                                $noLocal = false, $noAck = false,
                                $exclusive = false, $noWait = false, $ticket = '',
                                array $arguments = array())
    {
        $this->setCallback($callback);
        $this->setQueueName($queueName);
        $this->setTag($tag);
        $this->setNoLocal($noLocal);
        $this->setNoAck($noAck);
        $this->setExclusive($exclusive);
        $this->setNoWait($noWait);
        $this->setTicket($ticket);
        $this->setArguments($arguments);
    }

    /**
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @param array $arguments
     * @return self
     */
    public function setArguments(array $arguments = array())
    {
        $this->arguments = $arguments;
        return $this;
    }

    /**
     * @return callable
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * @param $callback
     * @return $this
     * @throws InvalidArgumentException
     */
    public function setCallback($callback)
    {
        if(!is_callable($callback)){
            throw new InvalidArgumentException("Callback must be callable");
        }

        $this->callback = $callback;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isExclusive()
    {
        return $this->exclusive;
    }

    /**
     * @param boolean $exclusive
     * @return self
     */
    public function setExclusive($exclusive)
    {
        $this->exclusive = $exclusive;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isNoAck()
    {
        return $this->noAck;
    }

    /**
     * @param boolean $noAck
     * @return self
     */
    public function setNoAck($noAck)
    {
        $this->noAck = $noAck;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isNoLocal()
    {
        return $this->noLocal;
    }

    /**
     * @param boolean $noLocal
     * @return self
     */
    public function setNoLocal($noLocal)
    {
        $this->noLocal = $noLocal;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isNoWait()
    {
        return $this->noWait;
    }

    /**
     * @param boolean $noWait
     * @return self
     */
    public function setNoWait($noWait)
    {
        $this->noWait = $noWait;
        return $this;
    }

    /**
     * @return string
     */
    public function getQueueName()
    {
        return $this->queueName;
    }

    /**
     * @param string $queueName
     * @return self
     */
    public function setQueueName($queueName)
    {
        $this->queueName = $queueName;
        return $this;
    }

    /**
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @param string $tag
     * @return self
     */
    public function setTag($tag)
    {
        $this->tag = $tag;
        return $this;
    }

    /**
     * @return string
     */
    public function getTicket()
    {
        return $this->ticket;
    }

    /**
     * @param string $ticket
     * @return self
     */
    public function setTicket($ticket)
    {
        $this->ticket = $ticket;
        return $this;
    }

    /**
     * @param AMQPChannel $channel
     */
    public function consume(AMQPChannel $channel)
    {
        $channel->basic_consume($this->getQueueName(), $this->getTag(),
            $this->isNoLocal(), $this->isNoAck(), $this->isExclusive(),
            $this->isNoWait(), $this->getCallback(), $this->getTicket(),
            $this->getArguments());
    }


}