<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use App\Models\SystemSetting;

trait BelongsToTenant
{
/**
     * トレイトを読み込んだモデルが起動した時の処理
     */
    protected static function bootBelongsToTenant()
    {
        // 1. データの保存（作成）時、ログインユーザーの所属企業IDを自動でセットする
        static::creating(function ($model) {
            if (auth()->hasUser() && !$model->system_setting_id) {
                $model->system_setting_id = auth()->user()->system_setting_id;
            }
        });

        // 2. データの取得時、ログインユーザーの所属企業IDのデータだけに自動で絞り込む
        static::addGlobalScope('tenant', function (Builder $builder) {
            // 💡 auth()->check() の代わりに auth()->hasUser() を使うことで、
            // ログイン処理中のUserモデルへの無限再帰クエリ（無限ループ）を完全に回避します。
            if (auth()->hasUser()) {
                // プロのTips: テーブル名を明示することで、結合（Join）クエリ時のカラム名重複エラーを防ぎます
                $tableName = $builder->getModel()->getTable();
                $builder->where($tableName . '.system_setting_id', auth()->user()->system_setting_id);
            }
        });
    }

    /**
     * 所属している自社（SystemSetting）とのリレーション定義
     */
    public function systemSetting()
    {
        return $this->belongsTo(\App\Models\SystemSetting::class, 'system_setting_id');
    }
}