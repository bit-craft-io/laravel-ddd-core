<?php

declare(strict_types=1);

namespace BitCraft\Core\Libraries\Stateless\Static;

use BitCraft\Core\Exceptions\ITypeExcept;
use Throwable;

/**
 * 例外コード(8桁 TT DDD NNN)の検査。
 *
 * - V1: コードは8桁(TT 10〜99、DDD 100〜999、NNN 001〜999)
 * - V2: TT は、定義された範囲(モデル10〜19、アプリ20〜29、開発90〜99)にあり、割当済み
 * - V3: 利用側の enum は、1つの (TT, DDD) だけを持つ(core の enum には適用しない)
 * - V4: TT の所有者と、enum の所有者が一致する
 * - V5: enum をまたいで、コードの重複がない
 * - V6: 全ケースに、メッセージがある
 * - V7: ケース名は {種別}{領域}{内容}。種別は TT から、領域は enum 名(TypeExcept{領域})から決まる
 */
final class StlStaExceptCode
{
    private const string CORE_NAMESPACE = 'BitCraft\\Core\\';

    /** TT の割当(core が定義する)。値は、その TT を管理する側 */
    private const array TT_OWNER = [
        10 => 'core',     // モデル
        20 => 'project',  // アプリ(利用側が管理)
        21 => 'core',     // アプリ基盤
        90 => 'core',     // 開発
    ];

    /** 種別の名前(ケース名の先頭) */
    private const array KIND_NAME = [
        'モデル' => 'Model',
        'アプリ' => 'App',
        '開発' => 'Develop',
    ];

    /** enum 名の接頭辞 */
    private const string ENUM_PREFIX = 'TypeExcept';

    /** TT の範囲(種別) */
    private const array TT_RANGE = [
        'モデル' => [10, 19],
        'アプリ' => [20, 29],
        '開発' => [90, 99],
    ];

    /**
     * ディレクトリ内の TypeExcept*.php から、enum のクラス名を集める。
     *
     * @param string $dir
     * @param string $namespace
     * @return array<class-string>
     */
    public static function discover(string $dir, string $namespace): array
    {
        $files = glob(rtrim($dir, '/') . '/TypeExcept*.php') ?: [];
        sort($files);
        return array_map(
            fn (string $file) => rtrim($namespace, '\\') . '\\' . basename($file, '.php'),
            $files
        );
    }

    /**
     * @param array<class-string> $enums
     * @return array{violations: array<string>, count: int}
     */
    public static function check(array $enums): array
    {
        $violations = [];
        $count = 0;
        $seen = [];

        foreach ($enums as $enum) {
            if (!enum_exists($enum) || !is_subclass_of($enum, ITypeExcept::class)) {
                $violations[] = "{$enum} は、ITypeExcept を実装した enum ではない";
                continue;
            }

            $is_core = str_starts_with($enum, self::CORE_NAMESPACE);
            $owner = $is_core ? 'core' : 'project';
            $short = self::shortName($enum) . '(' . ($is_core ? 'core' : '利用側') . ')';
            $first = null;

            // V7(enum 名)
            $enum_name = self::shortName($enum);
            $domain = '';
            if (!str_starts_with($enum_name, self::ENUM_PREFIX)) {
                $violations[] = "{$enum_name}: enum 名は TypeExcept{領域} の形";
            } else {
                $domain = substr($enum_name, strlen(self::ENUM_PREFIX));
                if (!$is_core && $domain === '') {
                    $violations[] = "{$short}: 利用側の enum 名は TypeExcept{領域} の形(領域がない)";
                }
            }

            foreach ($enum::cases() as $case) {
                $count++;
                $code = $case->code();
                $label = self::shortName($enum) . '::' . $case->name;

                // V1
                if ($code < 10000000 || $code > 99999999) {
                    $violations[] = "{$label} = {$code}: 8桁ではない";
                    continue;
                }
                $tt = intdiv($code, 1000000);
                $ddd = intdiv($code, 1000) % 1000;
                $nnn = $code % 1000;
                if ($ddd < 100 || $nnn < 1) {
                    $violations[] = "{$label} = {$code}: DDD は 100〜999、NNN は 001〜999";
                    continue;
                }

                // V2
                $kind = self::kindOf($tt);
                if ($kind === null) {
                    $reserved = ($tt >= 30 && $tt <= 89) ? '予約(30〜89)' : '範囲外';
                    $violations[] = "{$label} = {$code}: TT={$tt} は{$reserved}";
                    continue;
                }
                if (!isset(self::TT_OWNER[$tt])) {
                    $violations[] = "{$label} = {$code}: TT={$tt}({$kind})は未割当(core が定義していない)";
                    continue;
                }

                // V4
                if (self::TT_OWNER[$tt] !== $owner) {
                    $tt_owner = self::TT_OWNER[$tt] === 'core' ? 'core' : '利用側';
                    $violations[] = "{$short} に TT={$tt} のコード {$code} がある(TT={$tt} は{$tt_owner}管理)";
                }

                // V3(利用側の enum のみ)
                if (!$is_core) {
                    if ($first === null) {
                        $first = [$tt, $ddd, $code];
                    } elseif ($first[0] !== $tt || $first[1] !== $ddd) {
                        $violations[] = "{$short}: (TT, DDD) が揃っていない。{$first[2]} は ({$first[0]}, {$first[1]})、{$code} は ({$tt}, {$ddd})";
                    }
                }

                // V7(ケース名)
                $prefix = self::KIND_NAME[$kind] . $domain;
                $name = $case->name;
                if (!str_starts_with($name, $prefix) || strlen($name) === strlen($prefix) || !ctype_upper($name[strlen($prefix)])) {
                    $violations[] = "{$label} = {$code}: ケース名は {種別}{領域}{内容} の形(期待する先頭: {$prefix}、続けて大文字で始まる内容)";
                }

                // V5
                if (isset($seen[$code])) {
                    $violations[] = "{$code} が重複: {$seen[$code]} / {$label}";
                } else {
                    $seen[$code] = $label;
                }

                // V6
                try {
                    $message = $case->message();
                    if ($message === '') {
                        $violations[] = "{$label} = {$code}: メッセージが空";
                    }
                } catch (Throwable $e) {
                    $violations[] = "{$label} = {$code}: メッセージが未定義(" . get_class($e) . ')';
                }
            }
        }

        return ['violations' => $violations, 'count' => $count];
    }

    /**
     * @param int $tt
     * @return string|null
     */
    private static function kindOf(int $tt): ?string
    {
        foreach (self::TT_RANGE as $kind => [$min, $max]) {
            if ($tt >= $min && $tt <= $max) {
                return $kind;
            }
        }
        return null;
    }

    /**
     * @param string $class
     * @return string
     */
    private static function shortName(string $class): string
    {
        $pos = strrpos($class, '\\');
        return $pos === false ? $class : substr($class, $pos + 1);
    }
}
