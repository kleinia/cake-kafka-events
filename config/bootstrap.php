<?php

use Cake\Event\EventManager;
use Kleinia\KafkaEvents\Listener\KafkaEventsListener;

/**
 * Register events listener
 */
EventManager::instance()->on(new KafkaEventsListener());
