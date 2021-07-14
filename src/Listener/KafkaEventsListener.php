<?php

namespace Kleinia\KafkaEvents\Listener;

use Cake\Core\Configure;
use Cake\Core\InstanceConfigTrait;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\Log\Log;
use Exception;
use RdKafka\Conf;
use RdKafka\Producer;

/**
 * Class KafkaEventsListener
 *
 * @package Kleinia\KafkaEvents\Event
 * @author Roberto Muñoz <rmunglez@gmail.com>
 * @license MIT
 */
class KafkaEventsListener implements EventListenerInterface
{
    use InstanceConfigTrait;

    /**
     * Default configuration.
     * - events: an indexed array by events name with priorities for each event
     * - brokers: an array of available brokers addresses
     * - config: rdfkafka configuration settings
     *
     * @var array
     */
    protected $_defaultConfig = [
        'events' => [],
        'brokers' => [],
        'config' => [
            'log_level' => LOG_DEBUG,
            'debug' => 'all'
        ]
    ];

    /**
     * @var Producer|null kafka producer
     */
    private $_producer = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $config = Configure::read('KafkaEvents', []);
        $this->setConfig($config);

        $this->_initClient();
    }

    private function _initClient()
    {
        if (!$this->_producer) {
            try {
                // prepare kafka options
                $rdConf = new Conf();
                $config = $this->getConfig('config', []);
                foreach ($config as $key => $val) {
                    if (!empty($val)) {
                        $rdConf->set($key, $val);
                    }
                }
                $rdConf->setErrorCb(function ($kafka, $err, $reason) {
                    $this->_error(sprintf("%s (reason: %s)\n", rd_kafka_err2str($err), $reason));
                });

                // set the available brokers
                $producer = new Producer($rdConf);
                if ($producer->addBrokers(implode(',', $this->getConfig('brokers'))) == 0) {
                    $this->_error("Could not add any Kafka brokers");
                }

                $this->_producer = $producer;
            } catch (Exception $e) {
                $this->_error('Exception: ' . $e->getMessage() . "\n");
            }
        }
    }

    /**
     * Logs an error message.
     *
     * @param string $message
     */
    private function _error(string $message)
    {
        Log::error($message);
    }

    /**
     * {@inheritDoc}
     */
    public function implementedEvents()
    {
        return array_map(function ($priority) {
            $callable = 'handleEvent';

            return compact('callable', 'priority');
        }, $this->getConfig('events', []));
    }

    /**
     * Universal callback. Stream the event to Kafka
     *
     * @param Event $event Event.
     * @return void
     */
    public function handleEvent(Event $event)
    {
        $topicName = $event->getName();
        $data = $event->getData();

        $this->_publishJson($topicName, $data);
    }

    private function _publishJson(string $topicName, array $data)
    {
        try {
            $body = json_encode($data);
            if (!$body) {
                $this->_error("Error encoding to JSON: " . $data);
            }

            if (!empty($this->_producer)) {
                $topic = $this->_producer->newTopic($topicName);
                $topic->produce(RD_KAFKA_PARTITION_UA, 0, $body);
                $this->_producer->poll(0);
            }
        } catch (Exception $e) {
            $this->_error('Exception: ' . $e->getMessage() . "\n");
        }
    }
}
