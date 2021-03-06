<?php

namespace Foreweather;

use Exception;
use League\OAuth2\Client\Provider\GenericProvider;
use Phalcon\Cli\TaskInterface;
use Phalcon\Di\FactoryDefault\Cli as FactoryDefault;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Logger;
use Pheanstalk\Job;
use Pheanstalk\Pheanstalk;
use Psr\Log\LoggerInterface;

class Publisher
{
    /**
     * @var FactoryDefault
     */
    protected $di;

    /**
     * @var bool
     */
    protected $shouldClose = false;

    /**
     * @var Pheanstalk
     */
    protected $queue;

    /**
     * @var Job
     */
    protected $job;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var TaskInterface
     */
    protected $task;

    /**
     * @var array
     */
    protected $providers = [];

    /**
     * @param array $providers
     */
    public function setup(array $providers = []): void
    {
        $this->di = new FactoryDefault();
        $this->di->set('metrics', microtime(true));

        $this->providers = $providers;

        $this->registerServices();

        $this->queue = $this->di->get('queue');
        $this->queue->useTube('publisher');
    }

    /**
     * Registers available services
     *
     * @return void
     */
    private function registerServices()
    {
        foreach ($this->providers as $provider) {
            /**
             * @var ServiceProviderInterface $object
             */
            $object = new $provider();
            $object->register($this->di);
        }
    }

    /**
     * @param     $message
     * @param int $level
     */
    private function log($message, int $level = Logger::INFO)
    {
        $loggy      = [
            Logger::CRITICAL => 'CRITICAL',
            Logger::ALERT    => 'ALERT',
            Logger::ERROR    => 'ERROR',
            Logger::WARNING  => 'WARNING',
            Logger::NOTICE   => 'NOTICE',
            Logger::INFO     => 'INFO',
            Logger::DEBUG    => 'DEBUG',
            Logger::CUSTOM   => 'CUSTOM',
        ];
        $timeString = date('Y-m-d H:i:s');
        $message    = "[{$loggy[$level]}] [{$timeString}] {$message}";
        if (!empty($this->logger)) {
            $this->logger->log(1, $message);
        }

        echo $message . PHP_EOL;
    }
    
    /**
     * @param string $message
     */
    public function console(string $message)
    {
        echo $message . PHP_EOL;
    }

    public function run(): void
    {
        $this->console('Publisher is running!');

        $config = $this->di->get('config')->toArray();

        /**
         * @var GenericProvider $client
         */
        $client = $this->di->get('oauth_client');

        $token = $client->getAccessToken('client_credentials');

        $hour    = $config['notify']['hour'];
        $api_url = $config['oauth_client']['url'];

        $url = $api_url . '/user/subscribed_timezone?clock=' . $hour;

        while (true) {
            try {
                $request  = $client->getAuthenticatedRequest(
                    'GET',
                    $url,
                    $token
                );
                $response = $client->getParsedResponse($request);

                if (!isset($response['length'])) {
                    $this->console(json_encode($response));
                    throw new Exception('Subscriber selection timezone service not working: ' . $url);
                }

                if ($response['length'] > 0) {
                    $this->log('Send notification to timezone subscribers');
                    $this->queue->useTube('default')->put(
                        json_encode(
                            [
                                'job'     => 'notify:subscriberAction',
                                'payload' => $response['items'],
                            ]
                        )
                    );
                }
            } catch (Exception $e) {
                $this->log($e->getMessage());
                sleep(5);
                continue;
            }

            sleep(60);
        }
    }

    public function shouldClose(): void
    {
        if ($this->shouldClose) {
            die('closed!');
        }
    }
}
