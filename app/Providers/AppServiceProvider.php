<?php

namespace App\Providers;
use App\Models\Memo;
use App\Models\Tag;

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
            // 自分のメモ取得はMemoモデルに任せる
            // 自分で作ったメソッドはインスタンス化する必要がある
            $memo_model = new Memo();
            // メモ取得
            $memos = $memo_model->getMyMemo();

            $tags = Tag::where('user_id', '=', \Auth::id())
            ->whereNull('deleted_at')
            ->orderBy('id', 'DESC')
            ->get();

            $view->with('memos', $memos)->with('tags', $tags); 
            // 第一引数は、viewで使うときの命名、第二引数は渡したい変数or配列
        });
    }
}
