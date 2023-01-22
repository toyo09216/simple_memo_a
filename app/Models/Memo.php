<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Memo extends Model
{
    use HasFactory;

    public function getMyMemo(){
        $query_tag = \Request::query('tag');

            // ===ベースのメソッド===
            $query = Memo::query()->select('memos.*') // memosテーブルの全てを選択
            ->where('user_id', '=', \Auth::id()) //現在ログイン中のユーザーのものを
            ->whereNull('deleted_at') //deleted_at がnullのものを
            ->orderBy('updated_at', 'DESC'); //ASC=昇順、DESC=降順
            // ===ベースのメソッドここまで===

            // もしクエリパラメーターtagがあれば
            if(!empty($query_tag)){
                // タグで絞り込み
                
                $query->leftjoin('memo_tags', 'memo_tags.memo_id', '=', 'memos.id')
                ->where('memo_tags.tag_id', '=', $query_tag);
            }

            $memos = $query->get();

            return $memos;
    }
}
