<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TagController extends Controller
{
    public function index()
    {
        $tags = Tag::all();
        if($tags->isEmpty())
        {
            return response()->json([
                'status'=>'failed',
                'message'=>'Not Tags found'
            ],404);
        }
        return response()->json([
            'status'=>'success',
            'Tags'=>$tags
        ],200);
    }
    public function store(Request $request)
    {
        $validatedData= $request->validate([
            'name'=>['required','string','min:3','unique:tags,name']
        ]);
        $tag= Tag::create([
            'name'=>$validatedData['name'],
            'user_id'=>Auth::id()
        ]);
        return response()->json([
            'status'=>'success',
            'message'=>'Tag created successfully'
        ],201);
    }
    public function update(Tag $tag,Request $request)
    {
        $validatedData=$request->validate([
            'name'=>['required','string','min:3']
        ]);

            $tag->update([
                'name'=>$validatedData['name'],
                'user_id'=>Auth::id()
            ]);
            return response()->json([
                'status'=>'success',
                'message'=>'Tag updated successfully'
            ],200);
        
    }
    public function destroy(Tag $tag)
    {
            $tag->delete();
            return response()->json([
                'status'=>'success',
                'message'=>'Tag deleted successfully'
            ],200);
      
    }

}
