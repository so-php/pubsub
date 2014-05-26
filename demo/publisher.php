<?php

use SoPhp\PubSub\PubSub;

require_once 'bootstrap.php';

$pubSub = new PubSub($ch, EXCHANGE);
$pubSub->publish('hello', array('who' => @$argv[1] ?: 'world'));
