<?php


namespace SoPhp\Amqp;


use PhpAmqpLib\Channel\AMQPChannel;

class ExchangeDescriptor {
    const TYPE_DIRECT = 'direct';
    const TYPE_FAN_OUT = 'fanout';
    const TYPE_TOPIC = 'topic';
    const TYPE_HEADERS = 'headers';

    /** @var  string */
    protected $name;
    /** @var  string */
    protected $type;
    /** @var bool */
    protected $passive;
    /** @var bool  */
    protected $durable;
    /** @var bool  */
    protected $autoDelete;
    /** @var bool  */
    protected $internal;
    /** @var bool  */
    protected $nowait;
    /** @var null  */
    protected $arguments;
    /** @var null  */
    protected $ticket;

    /**
     * @param string $name
     * @param string $type
     * @param bool $passive
     * @param bool $durable
     * @param bool $autoDelete
     * @param bool $internal
     * @param bool $nowait
     * @param array $arguments
     * @param null $ticket
     */
    public function __construct($name, $type = self::TYPE_FAN_OUT, $passive = false, $durable = false,
                                $autoDelete = true, $internal = false,
                                $nowait = false, array $arguments = array(), $ticket = null)
    {
        $this->setName($name);
        $this->setType($type);
        $this->setPassive($passive);
        $this->setDurable($durable);
        $this->setAutoDelete($autoDelete);
        $this->setInternal($internal);
        $this->setNowait($nowait);
        $this->setArguments($arguments);
        $this->setTicket($ticket);
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
     * @return boolean
     */
    public function isAutoDelete()
    {
        return $this->autoDelete;
    }

    /**
     * @param boolean $autoDelete
     * @return self
     */
    public function setAutoDelete($autoDelete)
    {
        $this->autoDelete = $autoDelete;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isDurable()
    {
        return $this->durable;
    }

    /**
     * @param boolean $durable
     * @return self
     */
    public function setDurable($durable)
    {
        $this->durable = $durable;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isInternal()
    {
        return $this->internal;
    }

    /**
     * @param boolean $internal
     * @return self
     */
    public function setInternal($internal)
    {
        $this->internal = $internal;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isNowait()
    {
        return $this->nowait;
    }

    /**
     * @param boolean $nowait
     * @return self
     */
    public function setNowait($nowait)
    {
        $this->nowait = $nowait;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isPassive()
    {
        return $this->passive;
    }

    /**
     * @param boolean $passive
     * @return self
     */
    public function setPassive($passive)
    {
        $this->passive = $passive;
        return $this;
    }

    /**
     * @return null
     */
    public function getTicket()
    {
        return $this->ticket;
    }

    /**
     * @param null $ticket
     * @return self
     */
    public function setTicket($ticket)
    {
        $this->ticket = $ticket;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Calls declare_exchange on channel with arguments defined in descriptor
     * @param AMQPChannel $channel
     */
    public function declareExchange(AMQPChannel $channel)
    {
        $channel->exchange_declare($this->getName(), $this->getType(),
            $this->isPassive(), $this->isDurable(), $this->isAutoDelete(),
            $this->isInternal(), $this->isNowait(), $this->getArguments(),
            $this->getTicket());
    }


} 