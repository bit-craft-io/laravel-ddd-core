<?php

namespace BitCraft\Core\Jobs;

use BitCraft\Core\Jobs\Contexts\CtxAccessInfo;
use App\Models\LogAccessInfo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class JobAccessInfo implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public CtxAccessInfo $_access_info,
    ) {}

    public function handle(): void
    {
        // TODO 20260609 ここをもう少し考える on('log_db')
        LogAccessInfo::on('log_db')->create([
            'user_id' => $this->_access_info->_user_id,
            'public_id' => $this->_access_info->_public_id,
            'level' => $this->_access_info->_level,
            'api' => $this->_access_info->_api,
            'param' => $this->_access_info->_param,
            'file' => $this->_access_info->_file,
            'line' => $this->_access_info->_line,
            'option_info' => $this->_access_info->_option_info,
        ]);
    }
}
