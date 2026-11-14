<?php

declare(strict_types=1);

namespace BitCraft\Core\Libraries\Traits;

use BitCraft\Core\Libraries\Stateful\Static\StfStaCache;
use BitCraft\Core\Libraries\Stateful\Static\StfStaDataSource;
use BitCraft\Core\Libraries\Stateful\Static\StfStaTransaction;
use BitCraft\Core\Libraries\Stateless\Static\StlStaConfig;

trait TraitInfra
{
    public string|StfStaCache $_Cache = StfStaCache::class;
    public string|StlStaConfig $_Config = StlStaConfig::class;
    public string|StfStaDataSource $_DataSource = StfStaDataSource::class;
    public string|StfStaTransaction $_Transaction = StfStaTransaction::class;
}
