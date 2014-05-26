<?php


namespace SoPhp\Amqp;


use PhpAmqpLib\Channel\AMQPChannel;

class QueueDescriptor {
    /** @var  string */
    protected $name;
    /** @var  bool */
    protected $passive;
    /** @var  bool */
    protected $durable;
    /** @var  bool */
    protected $exclusive;
    /** @var  bool */
    protected $autoDelete;
    /** @var  bool */
    protected $nowait;
    /** @var  array */
    protected $arguments;
    /** @var  string */
    protected $ticket;

    /**
     * @param null $name
     * @param bool $passive
     * @param bool $durable
     * @param bool $exclusive
     * @param bool $autoDelete
     * @param bool $nowait
     * @param array $arguments
     * @param null $ticket
     */
    public function __construct($name = null, $passive = false, $durable = false,
                                $exclusive = false, $autoDelete = false,
                                $nowait = false, array $arguments = array(),
                                $ticket = null)
    {
        $this->setName($name);
        $this->setPassive($passive);
        $this->setDurable($durable);
        $this->setExclusive($exclusive);
        $this->setAutoDelete($autoDelete);
        $this->setNowait($nowait);
        $this->setArguments($arguments);
        $this->setTicket($ticket);
    }

    /**
     * @param AMQPChannel $channel
     */
    public function declareQueue(AMQPChannel $channel){
        list($queueName,,) = $channel->queue_declare($this->getName(),
            $this->isPassive(), $this->isDurable(), $this->isExclusive(),
            $this->isAutoDelete(), $this->isNowait(), $this->getArguments(),
            $this->getTicket());
        $this->setName($queueName);
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
    public function setTicket($ticket = null)
    {
        $this->ticket = $ticket;
        return $this;
    }


} 