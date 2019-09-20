<?php

namespace src\System;

interface RequestInterface
{
    public function initialize(array $request, string $thash, string $public_key, string $signature): void;

    public function getValidity(): bool;

    public function getResponse(): array;
}
