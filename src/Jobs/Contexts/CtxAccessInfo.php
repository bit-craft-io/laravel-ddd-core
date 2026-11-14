<?php

namespace BitCraft\Core\Jobs\Contexts;

/**
 * @property-read integer $_user_id
 * @property-read string $_public_id
 * @property-read integer $_level
 * @property-read string $_api
 * @property-read array $_param
 * @property-read string $_file
 * @property-read integer $_line
 * @property-read array $_option_info
 */
class CtxAccessInfo
{
    public function __construct(
        public int $_user_id = 0,
        public string $_public_id = '',
        public int $_level = 0,
        public string $_api = '',
        public array $_param = [],
        public string $_file = '',
        public int $_line = 0,
        public array $_option_info = [],
    ) {}

    public static function make(int $user_id, array $option_info): self
    {
        // @note 呼び出し元
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $caller = $backtrace[1] ?? [];
        $file = isset($caller['file'])
            ? str_replace(base_path() . DIRECTORY_SEPARATOR, '', $caller['file'])
            : '';
        $line = $caller['line'] ?? 0;

        $api = request()->path() ?? '';
        $param = request()->all();

        // TODO 20260609 ここの設定は保留
        $public_id = '';

        return new self(
            _user_id: $user_id,
            _public_id: $public_id,
            _api: $api,
            _param: $param,
            _file: $file,
            _line: $line,
            _option_info: $option_info
        );
    }
}
