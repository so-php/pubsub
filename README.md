# pubsub

Publish Subscribe Pattern via php-amqplib

## Avoiding Technology Lockout
One of the goals of ths implementation is to prevent locking out other technologies like python, ruby and java from publishing and subscribing. This is not a terribly difficult task--it just means we need to use a non-proprietary message queue (php-amqplib + rabbitmq) and a message format that isn't language specific. Hence we choose to use a json serialized data structure rather than a PHP serialized string. 

That being said, there are no enforced restrictions on the content of the event params. It is up to developers to keep vigilent and be sure not to stuff anything in event parameters that is PHP or platform specific. 

## Message Structure
As stated above, the message is a plain Json string. The structure is an object with three keys (only) at the top level. 

  * `name` the event name
  * `time` the timestamp of the event in ISO 8601. The time should be in UTC.
  * `params` an object to hold event params. No imposed limits other than technical feasibility and reasonablenes. Params should be present even if empty `{}`.

```
{
    "name": "some-event-name",
    "time": "2005-08-15T15:52:01+0000",
    "params": {
        "any": true,
        "number": 1.3,
        "of": "foo",
        "keys": {
           "of any nesting level": "but be reasonable"
        }
    }
}
```

## Usage
Using PubSub is pretty straight forward.
Words in uppercase are values that need to be supplied/configured. 
### Publishing
```
// need a channel to work with
$conn = new AMQPConnection(HOST, PORT, USER, PASS, VHOST);
$ch = $conn->channel();

$pubSub = new PubSub($ch, EXCHANGE_NAME);
$pubSub->publish('foo', array('hello'=>'world'));
```

### Publishing
```
// need a channel to work with
$conn = new AMQPConnection(HOST, PORT, USER, PASS, VHOST);
$ch = $conn->channel();

function doSomethingOnEvent(Event e){
   echo "Received event " . $e->getName() . " which was triggered on " . $e->getDateTime()->format('Y-m-d H:i:s') . " with the following params: " . print_r($e->getParams(),true);
}

$pubSub = new PubSub($ch, EXCHANGE_NAME);
$pubSub->subscribe('foo', 'doSomethingOnEvent');

// then wait for (and handle events)
// either:
$pubSub->wait(); // wraps $ch->wait() internally
// or directly on the channel object
$ch->wait();
```

