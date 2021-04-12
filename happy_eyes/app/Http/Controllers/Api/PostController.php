<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $posts = Post::where('user_id', Auth::id())
            ->orderBy('id', 'desc')
            ->get();
        return response()->json(['status' => true, 'data' => $posts]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $payload = $request->all();
        $payload['user_id'] = Auth::id();
        $postCreate = Post::create($payload);
        return response()->json([
            'status' => true,
            'data'   => $postCreate
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        $post = Post::where('id', $id)
            ->first();
        return response()->json(['status' => true, 'data' => $post]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        $payloads = $request->except(['_method']);
        if (Post::where('id', $id)->update($payloads)) {
            return response()->json([
                'status' => true,
            ]);
        }
        return response()->json([
            'status' => false,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        if(Post::where('id', $id)->delete()){
            return response()->json([
                'status' => true,
            ]);
        }
        return response()->json([
            'status' => false,
        ]);
    }
}
