<?php

declare(strict_types=1);

namespace BitCraft\Core\Libraries\Traits;

use BitCraft\Core\Libraries\Stateless\Static\StlStaDevelopTool;

trait TraitDevelop
{
    public string|StlStaDevelopTool $_DevTool = StlStaDevelopTool::class;
}
