<?php

declare(strict_types=1);

namespace BitCraft\Core\Libraries\Traits;

use BitCraft\Core\Libraries\Stateless\Static\StlStaUtilCompress;
use BitCraft\Core\Libraries\Stateless\Static\StlStaUtilNanoId;

trait TraitUtil
{
    public string|StlStaUtilCompress $_UtilCompress = StlStaUtilCompress::class;
    public string|StlStaUtilNanoId $_UtilNanoId = StlStaUtilNanoId::class;
}
