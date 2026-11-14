<?php

namespace BitCraft\Core\Libraries\Stateless\Static;

use BitCraft\Core\Libraries\Stateless\Static\Enum\TypeLogText;
use Illuminate\Support\Facades\Log;

final class StlStaLog
{
    /**
     * @param string|int $message
     * @param TypeLogText $type_log_text
     * @return void
     */
    public static function emergency(string|int $message = '--', TypeLogText $type_log_text = TypeLogText::Api): void
    {
        self::_log($message, $type_log_text, 'emergency');
    }

    /**
     * @param string|int $message
     * @param TypeLogText $type_log_text
     * @return void
     */
    public static function alert(string|int $message = '--', TypeLogText $type_log_text = TypeLogText::Api): void
    {
        self::_log($message, $type_log_text, 'alert');
    }

    /**
     * @param string|int $message
     * @param TypeLogText $type_log_text
     * @return void
     */
    public static function critical(string|int $message = '--', TypeLogText $type_log_text = TypeLogText::Api): void
    {
        self::_log($message, $type_log_text, 'critical');
    }

    /**
     * @param string|int $message
     * @param TypeLogText $type_log_text
     * @return void
     */
    public static function error(string|int $message = '--', TypeLogText $type_log_text = TypeLogText::Api): void
    {
        self::_log($message, $type_log_text, 'error');
    }

    /**
     * @param string|int $message
     * @param TypeLogText $type_log_text
     * @return void
     */
    public static function warning(string|int $message = '--', TypeLogText $type_log_text = TypeLogText::Api): void
    {
        self::_log($message, $type_log_text, 'warning');
    }

    /**
     * @param string|int $message
     * @param TypeLogText $type_log_text
     * @return void
     */
    public static function notice(string|int $message = '--', TypeLogText $type_log_text = TypeLogText::Api): void
    {
        self::_log($message, $type_log_text, 'notice');
    }

    /**
     * @param string|int $message
     * @param TypeLogText $type_log_text
     * @return void
     */
    public static function info(string|int $message = '--', TypeLogText $type_log_text = TypeLogText::Api): void
    {
        self::_log($message, $type_log_text, 'info');
    }

    /**
     * @param string|int $message
     * @param TypeLogText $type_log_text
     * @return void
     */
    public static function debug(string|int $message = '--', TypeLogText $type_log_text = TypeLogText::Api): void
    {
        self::_log($message, $type_log_text);
    }

    /**
     * @param string|int $message
     * @param TypeLogText $type_log_text
     * @param string $level
     * @return void
     */
    private static function _log(string|int $message = '--', TypeLogText $type_log_text = TypeLogText::Api, string $level = 'debug'): void
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $caller = $trace[1] ?? [];
        $class_fqcn = $caller['class'] ?? 'global scope';
        $line = $caller['line'] ?? 'none';

        Log::{$level}("----------------------------------------");
        if ($type_log_text->isApi()) {
            $parts = explode('\\', $class_fqcn);
            $app = end($parts);
            $func = $caller['function'];
            Log::{$level}("$app::$func($line)::$message");
            return;
        }

        $class_path = preg_replace('/^App\\\\/', '', $class_fqcn);
        Log::{$level}("$class_path($line)::$message");
    }
}
