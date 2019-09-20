<?php

namespace src\API;

use src\API;
use src\System\ResourceManager;
use src\Util\Logger;

class Resource extends API
{
    private $logger;

    private $resourceManager;

    private $resource;
    private $public_key;
    private $signature;

    public function __construct()
    {
        $this->logger = Logger::getLogger('api');

        $this->resourceManager = new ResourceManager();
    }

    public function _init()
    {
        $this->resource = json_decode($this->getParam($_REQUEST, 'resource', ['default' => '{}']), true);
        $this->public_key = $this->getParam($_REQUEST, 'public_key', ['default' => '']);
        $this->signature = $this->httpRequest->getParam('signature', '');
    }

    public function _process()
    {
        $this->logger->debug('resource', [$this->resource]);

        $type = $this->getParam($this->resource, 'type');
        $thash = hash('sha256', json_encode($this->resource));

        $this->logger->debug('manager', [$type, $this->resource, $thash, $this->public_key, $this->signature]);

        $this->resourceManager->initialize(
            $type,
            $this->resource,
            $thash,
            $this->public_key,
            $this->signature
        );
        $validity = $this->resourceManager->getValidity();

        if ($validity === false) {
            $this->Error('Invalid request');
        }

        $this->resourceManager->process();
    }

    public function _end()
    {
        $this->data = $this->resourceManager->getResponse();
    }
}
