<?php

namespace App\Http\Controllers\Api;

use App\Models\Post;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;


class PostController extends Controller
{
    /**
    * index
    *
    * @return void
    */
    public function index() {
        $posts = Post::latest()->paginate();

        return new PostResource(true, 'List Data Post', $posts);
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'image'     => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'title'     => 'required',
            'content'   => 'required',
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        //upload
        $image = $request->file('image');
        $image->storeAs('public/posts', $image->hashName());

        //create
        $post = Post::create([
            'image'     => $image->hashName(),
            'title'     => $request->title,
            'content'   => $request->content,
        ]);

        //mengembalikan respon
        return new PostResource(true, 'Data Post Berhasil di Tambahkan!', $post);
    }

    public function show(Post $post) {
        return new PostResource(true, 'Data Post Ditemukan!', $post);
    }

    public function update(Request $request, Post $post) {
        $validator = Validator::make($request->all(), [
            'title'     => 'required',
            'content'   => 'required',
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if($request->hasFile('image')) {

            $image = $request->file('image');
            $image->storeAs('public/posts', $image->hashName());

            Storage::delete('public/posts', $post->image);

            $post->update([
                'image'     => $image->hashName(),
                'title'     => $request->title,
                'cotent'    => $request->content,
            ]);
        } else {

            $post->update([
                'title'     => $request->title,
                'content'   => $request->content,
            ]);
        }

        return new PostResource(true, 'Data Post Berhasil di Perbarui!', $post);
    }
    
    public function destroy(Post $post) {
        Storage::delete('public/post/'.$post->image);

        $post->delete();

        return new PostResource(true, 'Post Berhasil di Delete!', null);
    }
}
