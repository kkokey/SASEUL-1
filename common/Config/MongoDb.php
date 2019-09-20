<?php

namespace src\Config;

class MongoDb
{
    public const HOST = 'mongo';
    public const PORT = 27017;

    public const NAMESPACE_PRECOMMIT = 'saseul_precommit';
    public const NAMESPACE_COMMITTED = 'saseul_committed';
    public const NAMESPACE_TRACKER = 'saseul_tracker';
}
