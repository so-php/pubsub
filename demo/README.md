# PubSub demo
A simple demo for using PubSub. Creates an exchanged named `foo-exchange` and publishes a `hello` event with parameter of `who` supplied by first parameter passed to the publisher script.
Subscribers listen for the `hello` event and greet the `who` parameter.


Start by running any number of subscribers (you'll need at least one):

    php subscriber.php

Now you can publish to those subscribers:

    php publisher.php "Bob"

