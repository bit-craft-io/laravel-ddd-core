<?php

declare(strict_types=1);

namespace BitCraft\Core\Libraries\Traits;

use BitCraft\Core\Libraries\Stateless\Static\StlStaDomain;
use BitCraft\Core\Libraries\Stateless\Static\StlStaUseCase;

trait TraitDomain
{
    public string|StlStaDomain $_Domain = StlStaDomain::class;
    public string|StlStaUseCase $_UseCase = StlStaUseCase::class;
}
