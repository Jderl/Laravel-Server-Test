<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\User;
use App\Models\View;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function getPostContent(Request $request)
    {
        try {
            $userId = $request->user['userId'];

            $queryResult = Post::where('user_id', $userId)->orderBy('id', 'desc');

            // Pagination
            $page = $request->query('page', 1);
            $limit = $request->query('limit', 8);
            $skip = ($page - 1) * $limit;

            // Records count
            $totalPost = $queryResult->count();
            $numOfPage = ceil($totalPost / $limit);

            $posts = $queryResult->skip($skip)->take($limit)->get();

            return response()->json([
                'success' => true,
                'message' => 'Content Loaded successfully',
                'totalPost' => $totalPost,
                'data' => $posts,
                'page' => $page,
                'numOfPage' => $numOfPage,
            ], 200);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], 404);
        }
    }

    public function getPosts(Request $request)
    {
        try {
            $cat = $request->query('cat');
            $writerId = $request->query('writerId');

            $query = Post::where('status', true);

            if ($cat) {
                $query->where('cat', $cat);
            } elseif ($writerId) {
                $query->where('user_id', $writerId);
            }

            $queryResult = $query->with('user')->orderBy('id', 'desc');

            // Pagination
            $page = $request->query('page', 1);
            $limit = $request->query('limit', 5);
            $skip = ($page - 1) * $limit;

            // Records count
            $totalPost = $queryResult->count();
            $numOfPage = ceil($totalPost / $limit);

            $posts = $queryResult->skip($skip)->take($limit)->get();

            return response()->json([
                'success' => true,
                'totalPost' => $totalPost,
                'data' => $posts,
                'page' => $page,
                'numOfPage' => $numOfPage,
            ], 200);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], 404);
        }
    }

    public function getPopularContents()
    {
        try {
            $posts = Post::where('status', true)
                ->select('title', 'slug', 'img', 'cat', 'views', 'createdAt')
                ->withCount('views')
                ->orderByDesc('views_count')
                ->limit(5)
                ->get();

            $writers = User::where('accountType', '!=', 'User')
                ->select('name', 'image')
                ->withCount('followers')
                ->orderByDesc('followers_count')
                ->limit(5)
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Successful',
                'data' => ['posts' => $posts, 'writers' => $writers],
            ], 200);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], 404);
        }
    }

    public function getPost($postId)
    {
        try {
            $post = Post::with('user')->findOrFail($postId);

            $newView = View::create([
                'user_id' => $post->user->id,
                'post_id' => $postId,
            ]);

            $post->views()->attach($newView->id);

            return response()->json([
                'success' => true,
                'message' => 'Successful',
                'data' => $post,
            ], 200);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], 404);
        }
    }

    public function store(Request $request)
    {
        // Validate incoming request data
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|unique:posts',
            'cat' => 'required|string',
            'img' => 'nullable',
            'desc' => 'required|string',
        ]);

        // Create new post
        $post = new Post();
        $post->title = $validatedData['title'];
        $post->slug = $validatedData['slug'];
        $post->category = $validatedData['cat'];
        $post->image_url = $validatedData['img'];
        $post->description = $validatedData['desc'];
        // Add any other fields you may have

        // Save the post
        $post->save();

        // Return a response
        return response()->json(['message' => 'Post created successfully'], 201);
    }

    public function createPost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userId' => 'required',
            'desc' => 'required',
            'img' => 'nullable',
            'title' => 'required',
            'slug' => 'required',
            'cat' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 400);
        }

        try {
            $post = Post::create([
                'user_id' => $request->userId,
                'desc' => $request->desc,
                'img' => $request->img,
                'title' => $request->title,
                'slug' => $request->slug,
                'cat' => $request->cat,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Post created successfully',
                'data' => $post,
            ], 200);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], 404);
        }
    }

    public function updatePost(Request $request, $id)
    {
        try {
            $status = $request->status;

            $post = Post::findOrFail($id);
            $post->status = $status;
            $post->save();

            return response()->json([
                'success' => true,
                'message' => 'Action performed successfully',
                'data' => $post,
            ], 200);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], 404);
        }
    }

    public function deletePost(Request $request, $id)
    {
        try {
            $userId = $request->user['userId'];

            $post = Post::where('id', $id)->where('user_id', $userId)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Deleted successfully',
            ], 200);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], 404);
        }
    }
}
