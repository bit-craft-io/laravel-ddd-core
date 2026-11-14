<?php

declare(strict_types=1);

namespace BitCraft\Core\Libraries\Traits;

use BitCraft\Core\Libraries\Stateless\Static\StlStaDate;
use BitCraft\Core\Libraries\Stateless\Static\StlStaExcept;
use BitCraft\Core\Libraries\Stateless\Static\StlStaLog;

trait TraitCore
{
    public string|StlStaDate $_Date = StlStaDate::class;
    public string|StlStaExcept $_Except = StlStaExcept::class;
    public string|StlStaLog $_Log = StlStaLog::class;
}
