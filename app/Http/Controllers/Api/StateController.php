<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class StateController extends Controller
{
    public function index()
    {
        $cacheKey = 'stats_data';

        
        $stats = Cache::remember($cacheKey, 60, function (){

            $totalUsers=User::count();
            $totalPosts=Post::count();
            $totalUsersWithZeroPost=User::doesntHave('posts')->count();
            return [
                'total_posts' => $totalPosts,
                'total_users' => $totalUsers,
                'total_users_with_zero_posts' => $totalUsersWithZeroPost
            ];
        });
        return response()->json([
        'status'=>'success',
        'cached_data'=>$stats],200);
    }
}
