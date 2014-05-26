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
    "time": "",
    "params": {
        "any": true,
        "number": 1.3,
        "of": "foo",
        "keys": {
           "of any nesting level": true
        }
    }
}
```

