<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostRequest;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostController extends Controller
{
    
    public function store(PostRequest $request)
    {
        $validatedData=$request->validated();
        $tagsArray = $request->input('tags', []); 
        $tagIds = collect($tagsArray)->pluck('id')->toArray();
        $imagePath=$this->handleImageUpload($request);
       
        try{
            DB::transaction(function () use ($validatedData, $imagePath, $tagIds) {
                
                $post = Post::create([
                    'title' => $validatedData['title'],
                    'body' => $validatedData['body'],
                    'cover_image' => $imagePath, 
                    'pinned' => $validatedData['pinned'],
                    'user_id' => Auth::id(),
                ]);
    
                if (!empty($tagIds)) {
                    $post->tags()->attach($tagIds);
                }
            });
            return response()->json([
                'status' => 'success',
                'message' => 'Post created successfully',
            ], 201);
        }catch(\Exception $e)
        {
            if (isset($imagePath) && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
            foreach($tagIds as $tagId)
            {
                if(!Tag::find($tagId))
                {
                    return response()->json([
                        'status'=>'failed',
                        'message'=>'there is no tags with that IDs please enter valid tags IDs'
                    ],404);
                }
            }
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create post. ' . $e->getMessage(),
            ], 500);
        }
        
    }
    public function getUserPosts()
    {
        
        $posts=Post::where('user_id',Auth::id())
        ->orderBy('pinned','desc')
        ->get();
        if($posts->isEmpty())
        {
            return response()->json([
                'status'=>'failed',
                'message'=>'No posts Existing for that user'
            ],404);
        }
        return response()->json([
            'status'=>'success',
            'posts'=>$posts
        ]);
    }  
    public function show(Post $post)
    {

        if($post->user_id!== Auth::id())
        {
            return response()->json([
                'status'=>'failed',
                'message'=>'No posts found for you'
            ],403);
        }
        return response()->json([
            'status'=>'success',
            'post'=>$post
        ],200);
    } 
    public function update(Post $post, Request $request)
    {
        $imagePath = $post->cover_image;
        $tagsArray = $request->input('tags', []); 
        $tagIds = collect($tagsArray)->pluck('id')->toArray();
        $validatedData=$request->validate([
            'title'=>['required','string','max:255'],
            'body'=>['required','string'],
            'cover_image'=>['image','mimes:jpg,jpeg,png,gif','max:2048'],
            'pinned'=>['required','boolean'],
            'tags_ids' => ['array'],
            'tags_ids.*.id' => ['required', 'integer'],
        ]);
        try{
            if(isset($validatedData['cover_image']))
             {
            // dd($imagePath&&Storage::disk('public')->exists($post->cover_image));
            if($imagePath&&Storage::disk('public')->exists($post->cover_image))
            {
                
                Storage::disk('public')->delete($imagePath);
            }
                
            $imagePath=$this->handleImageUpload($request);
             }

        DB::transaction(function() use($validatedData, $imagePath, $tagIds,$post){
            $post->update([
                'title' => $validatedData['title'],
                'body' => $validatedData['body'],
                'cover_image' => $imagePath, 
                'pinned' => $validatedData['pinned'],
            ]);
            if (!empty($tagIds)) {
               
                $post->tags()->attach($tagIds);
            }
            return response()->json([
                'status'=>'success',
                'message'=>'post updated successfully'
            ],200); 
        });
        }catch(\Exception $e)
        {
             foreach($tagIds as $tagId)
                {
                    if(!Tag::find($tagId))
                    {
                        return response()->json([
                            'status'=>'failed',
                            'message'=>'there is no tags with that IDs please enter valid tags IDs'
                        ],404);
                    }
                }
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create post. ' . $e->getMessage(),
            ], 500);

        }
        
            
        
    }
    public function destroy(Post $post)
    {
        if($post->user_id!==Auth::id())
        {
            return response()->json([
                'status'=>'failed',
                'message'=>'You are not authorized to delete this post'
            ],403);
        }
        $post->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Post deleted successfully',
        ], 200);
    }
    public function viewDeleted()
    {
        $posts=Post::where('user_id',Auth::id())->onlyTrashed()->get();
       // dd($posts);
        if($posts->isEmpty())
        {
            return response()->json([
                'status'=>'failed',
                'message'=>'no posts Deleted found'
            ],404);
        }
        return response()->json([
            'status'=>'success',
            'posts'=>$posts
        ],200); 
    }
    public function restore($id)
    {
        $post=Post::withTrashed()->where('id',$id)->first();
        if(!$post)
        {
            return response()->json([
                'status'=>'failed',
                'message'=>'No Post deleted with that ID to restore'
            ],404);
        }
        $post->restore();
        return response()->json([
            'status'=>'success',
            'message'=>'Post restored successfully'
        ],200);
    }
    private function handleImageUpload($request)
    {
        if ($request->hasFile('cover_image')) {
            $image = $request->file('cover_image');
            $imageName = Str::random(40) . '.' . $image->getClientOriginalExtension();
            $directory = 'Images';
            if (!Storage::exists($directory)) {
                Storage::makeDirectory($directory);
            }
            $imagePath = $image->storeAs($directory, $imageName, 'public');
            return $imagePath;
        }

        return null;
    }
}
