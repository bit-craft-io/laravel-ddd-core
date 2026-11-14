<?php

declare(strict_types=1);

namespace BitCraft\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property-read ?integer $user_id
 */
abstract class BaseReq extends FormRequest
{
    protected object $_props;

    // @note BaseReq を継承したクラスをコンストラクタインジェクションして無い場合
    //       BaseReq の prepareForValidation が実行されて無い為
    //       ミドルウェアで request->user_id は null になる
    //       prepareForValidation の実行済フラグで制御
    public static bool $_is_merged_user_id = false;

    /**
     * @return array
     */
    public function params(): array
    {
        return $this->toArray();
    }

    /**
     * @return void
     */
    protected function prepareForValidation(): void
    {
        self::$_is_merged_user_id = true;
        $this->merge(['user_id' => ($this->user()->id ?? null)]);
    }

    /**
     * @note バリデーション後に初期化
     * @return void
     */
    protected function passedValidation(): void
    {
        $this->_props = json_decode(json_encode($this->toArray() ?? []));
    }

    /**
     * @param $key
     * @return mixed|null
     */
    public function __get($key)
    {
        return $this->_props->$key ?? null;
    }
}
