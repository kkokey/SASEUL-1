<?php

namespace src\System;

class ResourceManager
{
    private $resource;

    public function __construct()
    {
        $this->resource = new Resource();
    }

    public function initialize($type, $request, $thash, $public_key, $signature): void
    {
        $class = 'src\\Resource\\' . $type;
        $this->resource = new $class();
        $this->resource->initialize($request, $thash, $public_key, $signature);
    }

    public function process(): void
    {
        $this->resource->process();
    }

    public function getValidity(): bool
    {
        return $this->resource->getValidity();
    }

    public function getResponse(): array
    {
        return $this->resource->getResponse();
    }
}
