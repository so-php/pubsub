<?php

use SoPhp\PubSub\Event;
use SoPhp\PubSub\PubSub;

require_once 'bootstrap.php';

$pubSub = new PubSub($ch, EXCHANGE);
$pubSub->subscribe('hello', function(Event $e){
    echo "Hello " . $e->getParam('who', 'guest') . PHP_EOL;
});

while (count($ch->callbacks)) {
    $ch->wait();
}
