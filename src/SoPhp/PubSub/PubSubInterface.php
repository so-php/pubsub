<?php


namespace SoPhp\PubSub;


interface PubSubInterface {
    /**
     * @param $event
     * @param array $params
     * @throws \SoPhp\PubSub\Exception\InvalidEventException
     */
    public function publish($event, $params = array());

    /**
     * @param $event
     * @param callable $callback
     * @throws \SoPhp\PubSub\Exception\InvalidCallbackException
     */
    public function subscribe($event, $callback);
} 