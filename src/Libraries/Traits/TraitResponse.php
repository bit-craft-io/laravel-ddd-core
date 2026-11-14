<?php

declare(strict_types=1);

namespace BitCraft\Core\Libraries\Traits;

use BitCraft\Core\Libraries\Stateful\Static\StfStaResponseModify;
use BitCraft\Core\Libraries\Stateful\Static\StfStaResponseParam;

trait TraitResponse
{
    public string|StfStaResponseParam $_ResponseParam = StfStaResponseParam::class;
    public string|StfStaResponseModify $_ResponseModify = StfStaResponseModify::class;
}
