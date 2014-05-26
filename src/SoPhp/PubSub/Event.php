<?php


namespace SoPhp\PubSub;


use DateTime;
use DateTimeZone;

class Event {
    /** @var  string */
    protected $name;
    /** @var  DateTime */
    protected $dateTime;
    /** @var  array */
    protected $params;

    /**
     * @param string $name
     * @param array $params
     * @param DateTime|null $datetime
     */
    public function __construct($name, $params = array(), $datetime = null){
        $this->setName($name);
        $this->setParams($params);
        $this->setDateTime($datetime ?: new DateTime('now', new DateTimeZone('UTC')));
    }
    /**
     * @return DateTime
     */
    public function getDateTime()
    {
        return $this->dateTime;
    }

    /**
     * @param DateTime $dateTime
     * @return self
     */
    public function setDateTime(DateTime $dateTime)
    {
        $this->dateTime = $dateTime;
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
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param string $name
     * @param null $default
     * @return mixed
     */
    public function getParam($name, $default = null){
        return isset($this->params[$name]) ? $this->params[$name] : $default;
    }

    /**
     * @param array $params
     * @return self
     */
    public function setParams(array $params)
    {
        $this->params = $params;
        return $this;
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function setParam($name, $value) {
        $this->params[$name] = $value;
    }

    /**
     * @return string
     */
    public function toJson(){
        return json_encode((object)array(
            'name' => $this->name,
            'datetime' => $this->getDateTime()->format(DateTime::ISO8601),
            'params' => $this->params
        ));
    }

    /**
     * @param $json
     * @return Event
     */
    public static function fromJson($json){
        $obj = json_decode($json);
        return new Event($obj->name, (array)$obj->params, new DateTime($obj->datetime, new DateTimeZone('UTC')));
    }

} 