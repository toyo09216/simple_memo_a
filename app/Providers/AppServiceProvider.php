<?php

namespace App\Providers;
use App\Models\Memo;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // 全てのメソッドが呼ばれる前に先に呼ばれるメソッド
        view()->composer('*', function($view){
            // ここでメモを取得
        $memos = Memo::select('memos.*') // memosテーブルの全てを選択
        ->where('user_id', '=', \Auth::id()) //現在ログイン中のユーザーのものを
        ->whereNull('deleted_at') //deleted_at がnullのものを
        ->orderBy('updated_at', 'DESC') //ASC=昇順、DESC=降順
        ->get(); //取得

        $view->with('memos', $memos); // 第一引数は、viewで使うときの命名、第二引数は渡したい変数or配列
        });
    }
}
