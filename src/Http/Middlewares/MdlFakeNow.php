<?php

namespace BitCraft\Core\Http\Middlewares;

use BitCraft\Core\Libraries\Traits\TraitDomain;
use BitCraft\Core\Libraries\Traits\TraitInfra;
use BitCraft\Core\Libraries\Traits\TraitCore;
use BitCraft\Core\Libraries\Traits\TraitUtil;
use Closure;

final class MdlFakeNow
{
    use TraitCore;
    use TraitUtil;
    use TraitInfra;
    use TraitDomain;

    public function handle($request, Closure $next)
    {
        if ($this->_Config::app()->debug && auth()->check()) {
            $this->_Date::applyFakeNow(auth()->id());
        }
        return $next($request);
    }
}
