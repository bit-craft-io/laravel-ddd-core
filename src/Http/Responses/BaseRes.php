<?php

declare(strict_types=1);

namespace BitCraft\Core\Http\Responses;

use BitCraft\Core\Libraries\Traits\TraitDomain;
use BitCraft\Core\Libraries\Traits\TraitInfra;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

abstract class BaseRes extends JsonResponse
{
    use TraitInfra;
    use TraitDomain;

    protected array $_result = [];

    abstract public function toResponse(Request $req): JsonResponse;

    /**
     * @param string $name
     * @return mixed|null
     */
    public function __get(string $name)
    {
        return $this?->_result[$name] ?? null;
    }

    /**
     * @param array $params
     * @return void
     */
    public function setParams(array $params): void
    {
        $this->_result += $params;
    }
}
