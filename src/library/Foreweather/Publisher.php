<?php

namespace Foreweather;

use Phalcon\Di\FactoryDefault\Cli as FactoryDefault;
use Phalcon\Di\ServiceProviderInterface;
use Pheanstalk\Pheanstalk;

class Publisher
{
    /**
     * @var FactoryDefault
     */
    protected $di;

    /**
     * @var Pheanstalk
     */
    protected $queue;

    /**
     * @var array
     */
    protected $providers = [];

    /**
     * @param array $providers
     */
    public function setup(array $providers = []): void
    {
        $this->di        = new FactoryDefault();
        $this->providers = $providers;
        $this->di->set('metrics', microtime(true));

        $this->registerServices();

        /**
         * @todo make active after the connection
         */
        $this->queue = $this->di->get('queue');
        $this->queue->useTube('publisher');
    }

    /**
     * Registers available services
     *
     * @return void
     */
    private function registerServices(): void
    {
        foreach ($this->providers as $provider) {
            /**
             * @var ServiceProviderInterface $object
             */
            $object = new $provider();
            $object->register($this->di);
        }
    }

    public function run(): void
    {
        echo 'Publisher Service started.' . PHP_EOL;
        $i = 1;
        while (true) {
            echo 'checking... ' . $i . PHP_EOL;

            sleep(2);
            $i++;
        }
    }
}
