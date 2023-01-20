<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Memo;


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
        // ここでメモを取得
        $memos = Memo::select('memos.*') // memosテーブルの全てを選択
            ->where('user_id', '=', \Auth::id()) //現在ログイン中のユーザーのものを
            ->whereNull('deleted_at') //deleted_at がnullのものを
            ->orderBy('updated_at', 'DESC') //ASC=昇順、DESC=降順
            ->get(); //取得

        return view('create', compact('memos')); //view側に値を渡す
    }

    public function store(Request $request)
    {
        $posts = $request->all();

        Memo::insert(['content'=> $posts['content'], 'user_id' => \Auth::id()]);
        
        return redirect(route('home'));
    }

    public function edit($id)
    {
        // ここでメモを取得
        $memos = Memo::select('memos.*') // memosテーブルの全てを選択
            ->where('user_id', '=', \Auth::id()) //現在ログイン中のユーザーのものを
            ->whereNull('deleted_at') //deleted_at がnullのものを
            ->orderBy('updated_at', 'DESC') //ASC=昇順、DESC=降順
            ->get(); //取得

        $edit_memo = Memo::find($id);

        return view('edit', compact('memos', 'edit_memo')); //view側に値を渡す
    }

    public function update(Request $request)
    {
        $posts = $request->all();
        Memo::where('id', $posts['memo_id'])->update(['content'=> $posts['content']]);
        
        return redirect(route('home'));
    }
}
