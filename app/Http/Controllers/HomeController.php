<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Memo;
use App\Models\Tag;
use App\Models\MemoTag;
use Illuminate\Support\Facades\DB;


class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $tags = Tag::where('user_id', '=', \Auth::id())
        ->whereNull('deleted_at')
        ->orderBy('id', 'DESC')
        ->get();

        return view('create', compact('tags')); //view側に値を渡す
    }

    public function store(Request $request)
    {
        $posts = $request->all();
        //バリデーション view側(create.blade.php)の<textarea name="content"...が入力必須であるということ
        $request->validate(['content' => 'required']);


        // ここからトランザクション開始
        DB::transaction(function() use($posts) {
            // メモIDをインサートして取得
            $memo_id = Memo::insertGetId(['content'=> $posts['content'], 'user_id' => \Auth::id()]);

            // 新規タグが既にtagsテーブルに存在するのかチェック
            $tag_exists = Tag::where('user_id', '=', \Auth::id())->where('name', '=', $posts['new_tag'])
            ->exists(); //trueかfalseで返ってくる

            // 新規タグが入力されているかチェック
            // 新規タグが既に存在しなければ、tagsテーブルにインサート->IDを取得
            if(!empty($posts['new_tag']) && !$tag_exists){
                $tag_id = Tag::insertGetId(['user_id' => \Auth::id(), 'name' => $posts['new_tag']]);
                // memo_tagsにインサートして、メモとタグを紐づける
                MemoTag::insert(['memo_id' => $memo_id, 'tag_id' => $tag_id]);
            }
            
            // 既存タグが紐付けられた場合->memo_tagsにインサート
            if(!empty($posts['tags'][0])){
                foreach($posts['tags'] as $tag){
                    MemoTag::insert(['memo_id' => $memo_id, 'tag_id' => $tag]);
                }
            }
        
        });
        // ここまでがトランザクションの範囲

        return redirect(route('home'));
    }

    public function edit($id)
    {
        $edit_memo = Memo::select('memos.*', 'tags.id AS tag_id')
            ->leftjoin('memo_tags', 'memo_tags.memo_id', '=', 'memos.id')
            ->leftjoin('tags', 'memo_tags.tag_id', '=', 'tags.id')
            ->where('memos.user_id', '=', \Auth::id())
            ->where('memos.id', '=', $id)
            ->whereNull('memos.deleted_at')
            ->get();
        
        $include_tags = [];
        foreach($edit_memo as $memo){
            array_push($include_tags, $memo['tag_id']);
        }
        $tags = Tag::where('user_id', '=', \Auth::id())
        ->whereNull('deleted_at')
        ->orderBy('id', 'DESC')
        ->get();

        return view('edit', compact('edit_memo', 'include_tags', 'tags')); //view側に値を渡す
    }

    public function update(Request $request)
    {
        $posts = $request->all();
        //バリデーション view側(create.blade.php)の<textarea name="content"...が入力必須であるということ
        $request->validate(['content' => 'required']);


        // トランザクションスタート
        DB::transaction(function() use($posts){
            Memo::where('id', $posts['memo_id'])->update(['content'=> $posts['content']]);
            // 一旦メモとタグの紐付けを削除
            MemoTag::where('memo_id', '=', $posts['memo_id'])->delete();
            // 再度メモとタグの紐付け
            foreach($posts['tags'] as $tag){
                MemoTag::insert(['memo_id' => $posts['memo_id'], 'tag_id' => $tag]);
            }
            // もし、新しいタグの入力があれば、インサートして紐づける
            $tag_exists = Tag::where('user_id', '=', \Auth::id())->where('name', '=', $posts['new_tag'])
            ->exists(); //trueかfalseで返ってくる

            // 新規タグが入力されているかチェック
            // 新規タグが既に存在しなければ、tagsテーブルにインサート->IDを取得
            if(!empty($posts['new_tag']) && !$tag_exists){
                $tag_id = Tag::insertGetId(['user_id' => \Auth::id(), 'name' => $posts['new_tag']]);
                // memo_tagsにインサートして、メモとタグを紐づける
                MemoTag::insert(['memo_id' => $posts['memo_id'], 'tag_id' => $tag_id]);
            }
        });
        // トランザクションここまで

        return redirect(route('home'));
    }

    public function destroy(Request $request)
    {
        $posts = $request->all();
        Memo::where('id', $posts['memo_id'])->update(['deleted_at' => date("Y-m-d H:i:s", time())]);
        
        return redirect(route('home'));
    }
}
