<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\User;
use App\Models\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function getPostContent(Request $request)
    {
        try {

            $userId = $request->user['userId'] ?? 13;
            $queryResult = Post::where('user_id', $userId)->orderBy('id', 'desc');

            // Pagination
            $page = $request->query('page', 1);
            $limit = $request->query('limit', 8);
            $skip = ($page - 1) * $limit;

            // Records count
            $totalPost = $queryResult->count();
            $numOfPage = ceil($totalPost / $limit);

            // Retrieve posts for the current page
            $posts = $queryResult->skip($skip)->take($limit)->get();

            return response()->json([
                'success' => true,
                'message' => 'Content loaded successfully',
                'totalPost' => $totalPost,
                'data' => $posts,
                'page' => $page,
                'numOfPage' => $numOfPage,
            ], 200);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], 500);
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

    // Create

    /** 
     public function create(Request $request)
    {
        try {
            // Set user ID to 15 if not provided in the request
            $userId = $request->input('user_id', 15);

            // Check if the user exists
            $userExists = User::where('id', $userId)->exists();

            if (!$userExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'User does not exist.',
                ], 400);
            }

            // Retrieve post information from the request
            $desc = $request->input('desc');
            $title = $request->input('title');
            $slug = $request->input('slug');
            $image = $request->file('image');
            $category = $request->input('category');

            // Validate the request data
            if (!$desc || !$title || !$slug || !$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'All fields except image are required.',
                ], 400);
            }

            // Handle image upload
            $imageName = null;
            if ($image) {
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('images'), $imageName);
            }

            // Create the post
            $post = Post::create([
                'title' => $title,
                'slug' => $slug,
                'desc' => $desc,
                'img' => $imageName,
                'cat' => $category,
                'user_id' => $userId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Post created successfully',
                'data' => $post,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

     */

    /**public function create(Request $request)
    {
        try {
            // Get the last user ID from the database
            $lastUserId = User::max('id');

            // Set user ID to 15 if not provided in the request
            $userId = $request->input('user_id', 15);

            // Check if the user exists
            if ($userId > 100 || $userId > $lastUserId) {
                // If the provided user ID exceeds 100 or the last user ID, set it to the last user ID
                $userId = $lastUserId;
            }

            $userExists = User::where('id', $userId)->exists();

            if (!$userExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'User does not exist.',
                ], 400);
            }

            // Retrieve post information from the request
            $desc = $request->input('desc');
            $title = $request->input('title');
            $slug = $request->input('slug');
            $image = $request->file('image');
            $category = $request->input('category');

            // Validate the request data
            if (!$desc || !$title || !$slug || !$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'All fields except image are required.',
                ], 400);
            }

            // Handle image upload
            $imageName = null;
            if ($image) {
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('images'), $imageName);
            }

            // Create the post
            $post = Post::create([
                'title' => $title,
                'slug' => $slug,
                'desc' => $desc,
                'img' => $imageName,
                'cat' => $category,
                'user_id' => $userId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Post created successfully',
                'data' => $post,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

     */
    /**  public function create(Request $request)
    {
        try {
            // Get the last user ID from the database
            $lastUserId = User::max('id');

            // Set user ID to 15 if not provided in the request
            $userId = $request->input('user_id', 13);

            // Check if the user ID exceeds 100 or the last user ID, set it to the last user ID
            if ($userId > 100 || $userId > $lastUserId) {
                $userId = $lastUserId;
            }

            // Check if the user exists
            $userExists = User::where('id', $userId)->exists();

            if (!$userExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'User does not exist.',
                ], 400);
            }

            // Retrieve post information from the request
            $desc = $request->input('desc');
            $title = $request->input('title');
            $slug = $request->input('slug');
            $image = $request->file('image');
            $category = $request->input('category');

            // Validate the request data
            if (!$desc || !$title || !$slug || !$category || !$image) {
                return response()->json([
                    'success' => false,
                    'message' => 'All fields including image are required.',
                ], 400);
            }

            // Handle image upload
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images'), $imageName);

            // Create the post
            $post = Post::create([
                'title' => $title,
                'slug' => $slug,
                'desc' => $desc,
                'img' => $imageName,
                'cat' => $category,
                'user_id' => $userId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Post created successfully',
                'data' => $post,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
     */


    public function create(Request $request)
    {
        try {
            // Get the last user ID from the database
            $lastUserId = User::max('id');

            // Set user ID to 15 if not provided in the request
            $userId = $request->input('user_id', 13);

            // Check if the user ID exceeds 100 or the last user ID, set it to the last user ID
            if ($userId > 100 || $userId > $lastUserId) {
                $userId = $lastUserId;
            }

            // Check if the user exists
            $userExists = User::where('id', $userId)->exists();

            if (!$userExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'User does not exist.',
                ], 400);
            }

            // Retrieve post information from the request
            $desc = $request->input('desc');
            $title = $request->input('title');
            $slug = $request->input('slug');
            $image = $request->file('image');
            $category = $request->input('category');

            // Validate the request data
            if (!$desc || !$title || !$slug || !$category || !$image) {
                return response()->json([
                    'success' => false,
                    'message' => 'All fields including image are required.',
                ], 400);
            }

            // Handle image upload
            $imageName = $image->storeAs('public/images', $image->getClientOriginalName()); // Save image with original filename

            // Create the post
            $post = Post::create([
                'title' => $title,
                'slug' => $slug,
                'desc' => $desc,
                'img' => $imageName,
                'cat' => $category,
                'user_id' => $userId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Post created successfully',
                'data' => $post,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
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

    public function getPaginatedPosts(Request $request)
    {
        try {
            // Extract query parameters
            $category = $request->query('cat', '');
            $writerId = $request->query('writerId', '');
            $page = $request->query('page', 1);

            // Define limit per page
            $limit = 10; // Adjust as needed

            // Calculate offset
            $offset = ($page - 1) * $limit;

            // Query posts based on optional parameters
            $query = Post::query();
            if (!empty($category)) {
                $query->where('cat', $category);
            }
            if (!empty($writerId)) {
                $query->where('user_id', $writerId);
            }

            // Get total count of posts
            $totalPost = $query->count();

            // Get paginated posts
            $posts = $query->skip($offset)->take($limit)->get();

            return response()->json([
                'success' => true,
                'data' => $posts,
                'page' => $page,
                'totalPost' => $totalPost,
                'numOfPages' => ceil($totalPost / $limit),
            ], 200);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], 500);
        }
    }

    public function getSinglePost($id)
    {
        try {
            $post = Post::findOrFail($id);
            return response()->json(['success' => true, 'data' => $post], 200);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], 404);
        }
    }
    public function getUserPosts()
    {
        try {
            $user = Auth::user();
            $posts = Post::where('user_id', $user->id)->get();
            return response()->json(['success' => true, 'data' => $posts], 200);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], 500);
        }
    }
}
