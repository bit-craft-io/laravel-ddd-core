<?php

declare(strict_types=1);

namespace BitCraft\Core\Http\Applications;

use BitCraft\Core\Libraries\Traits\TraitDomain;
use BitCraft\Core\Libraries\Traits\TraitInfra;
use BitCraft\Core\Libraries\Traits\TraitCore;
use BitCraft\Core\Libraries\Traits\TraitDevelop;
use BitCraft\Core\Libraries\Traits\TraitResponse;
use BitCraft\Core\Libraries\Traits\TraitUtil;

abstract class BaseApp
{
    use TraitCore;
    use TraitUtil;
    use TraitInfra;
    use TraitDomain;
    use TraitResponse;

    use TraitDevelop;
}
