# cakephp-kafka-events
Adds support for sourcing events into Kafka in a CakePHP 3 application.

## Install

Using [Composer][composer]:

```
composer require kleinia/cakephp-kafka-events
```

After installation, [Load the plugin](http://book.cakephp.org/3.0/en/plugins.html#loading-a-plugin)
```sh
$ bin/cake plugin load --bootstrap Kleinia/KafkaEvents
```

## Setup

Set your desired events that should be propagated in Kafka in `KafkaEvents` settings in app.php
```php
'KafkaEvents' => [
    'events' => [  // key / value pairs with event name and priority for each event
        'Model.Event1' => 0,
        'Model.Event2' => 100,
        'Model.Event3' => 100,
    ],
    'brokers' => [ // list of ip address of the available kafka brokers
        'ip1',
        'ip2'
    ],
    'config' => [ // rdfkafka configuration parameters
        'debug' => 'all'
    ]
]
```

## Bugs & Feedback

https://github.com/kleinia/cake-kafka-events/issues

## License

Copyright (c) 2021, [Roberto Muñoz][personal] and licensed under [The MIT License][mit].

[cakephp]:http://cakephp.org
[composer]:http://getcomposer.org
[mit]:http://www.opensource.org/licenses/mit-license.php
[personal]:https://github.com/kleinia
