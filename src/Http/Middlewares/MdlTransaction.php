<?php

declare(strict_types=1);

namespace BitCraft\Core\Http\Middlewares;

use BitCraft\Core\Libraries\Traits\TraitDomain;
use BitCraft\Core\Libraries\Traits\TraitInfra;
use BitCraft\Core\Libraries\Traits\TraitCore;
use BitCraft\Core\Libraries\Traits\TraitUtil;
use Closure;

final class MdlTransaction
{
    use TraitCore;
    use TraitUtil;
    use TraitInfra;
    use TraitDomain;

    /**
     * @param $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        $this->_Log::info('begin');

        $tranLevel = $this->_Transaction::getLevel();

        // @note ロールバック
        if ($this->_Transaction::isRollback()) {
            for ($i = 0; $i < $tranLevel; $i++) {
                $this->_Log::info('rollback');
                $this->_Transaction::rollback();
            }
            $this->_Log::info('end');
            return $response;
        }

        // @note コミット
        for ($i = 0; $i < $tranLevel; $i++) {
            $this->_Log::info('commit');
            $this->_Transaction::commit();
        }
        $this->_Log::info('end');
        return $response;
    }
}
