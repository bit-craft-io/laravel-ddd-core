<?php

declare(strict_types=1);

namespace BitCraft\Core\Domains\Repository;

use BitCraft\Core\Libraries\Traits\TraitDomain;
use BitCraft\Core\Libraries\Traits\TraitInfra;

abstract class BaseRep
{
    use TraitInfra;
    use TraitDomain;
}
