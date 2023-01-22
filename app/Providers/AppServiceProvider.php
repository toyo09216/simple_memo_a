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
            $query_tag = \Request::query('tag');
            // もしクエリパラメーターtagがあれば
            if(!empty($query_tag)){
                // タグで絞り込み
                $memos = Memo::select('memos.*') // memosテーブルの全てを選択
                ->leftjoin('memo_tags', 'memo_tags.memo_id', '=', 'memos.id')
                ->where('memo_tags.tag_id', '=', $query_tag)
                ->where('user_id', '=', \Auth::id()) //現在ログイン中のユーザーのものを
                ->whereNull('deleted_at') //deleted_at がnullのものを
                ->orderBy('updated_at', 'DESC') //ASC=昇順、DESC=降順
                ->get(); //取得

            }else{
                // タグがなければ全て取得
                $memos = Memo::select('memos.*') // memosテーブルの全てを選択
                ->where('user_id', '=', \Auth::id()) //現在ログイン中のユーザーのものを
                ->whereNull('deleted_at') //deleted_at がnullのものを
                ->orderBy('updated_at', 'DESC') //ASC=昇順、DESC=降順
                ->get(); //取得
            }

            $tags = Tag::where('user_id', '=', \Auth::id())
            ->whereNull('deleted_at')
            ->orderBy('id', 'DESC')
            ->get();

            $view->with('memos', $memos)->with('tags', $tags); 
            // 第一引数は、viewで使うときの命名、第二引数は渡したい変数or配列
        });
    }
}
