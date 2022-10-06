<?php

namespace App\Http\Controllers\Article;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Article\Post;
use App\Http\Requests\Article\{
    StoreRequest,
    UpdateRequest
};
use App\Traits\{BugsnagTrait, ResponseBuilder};
use Illuminate\Filesystem\Filesystem;
use DB;
use File;
use Illuminate\Support\Facades\Cache;

class ArticleController extends Controller
{
    use BugsnagTrait, ResponseBuilder;

    const PER_PAGE = 1;
    const CURRENT_PAGE = 1;

    public function index(Request $request) {
        try {
            $perPage = self::PER_PAGE;
            $currentPage = $request->page ?? self::CURRENT_PAGE;

            $paging = (($perPage * $currentPage)-$perPage)+1;

            $user_id = 0;
            if(!auth()->user()->hasRole('administrator'))
                $user_id = auth()->user()->id;

            $data = Cache::rememberForever('article'.$user_id.$perPage.$currentPage, function () use ($perPage, $currentPage) {
                return $this->paginate($perPage, $currentPage);
            });

            return view('pages/article/index', compact('data', 'paging'));
        }catch(\Throwable $e) {
            $this->report($e);

            return $this->sendError(400, 'Whoops, looks like something went wrong #index');
        }
    }

    public function rebuildCache($user_id=0) {
        try {
            if(!auth()->user()->hasRole('administrator'))
                $this->rebuildCacheUser();
            elseif(auth()->user()->hasRole('administrator') && $user_id != auth()->user()->id)
                $this->rebuildCacheUser($user_id);

            //for administrator
            $post_count = Post::count();

            for($i=0;$i<$post_count;$i++) {
                $perPage = self::PER_PAGE;
                $currentPage = $i+1;

                $user_id = 0;
                // rebuild cache for administrator
                Cache::forget('article'.$user_id.$perPage.$currentPage);

                Cache::rememberForever('article'.$user_id.$perPage.$currentPage, function () use ($perPage, $currentPage) {
                    return $this->paginate($perPage, $currentPage, true);
                });
            }
        }catch(\Throwable $e) {
            $this->report($e);

            return $this->sendError(400, 'Whoops, looks like something went wrong #rebuildCache');
        }
    }

    public function rebuildCacheUser($user_id = 0) {
        try {
            if($user_id)
                $post_count = Post::where('article_creator', $user_id)->count();
            else
                $post_count = Post::where('article_creator', auth()->user()->id)->count();

            for($i=0;$i<$post_count;$i++) {
                $perPage = self::PER_PAGE;
                $currentPage = $i+1;

                if(!auth()->user()->hasRole('administrator'))
                    $user_id = auth()->user()->id;

                    Cache::forget('article'.$user_id.$perPage.$currentPage);

                    Cache::rememberForever('article'.$user_id.$perPage.$currentPage, function () use ($perPage, $currentPage) {
                        if(auth()->user()->hasRole('administrator'))
                            return $this->paginate($perPage, $currentPage, true, $user_id);
                        else
                            return $this->paginate($perPage, $currentPage);
                    });
                // }
            }
        }catch(\Throwable $e) {
            $this->report($e);

            return $this->sendError(400, 'Whoops, looks like something went wrong #rebuildCacheUser');
        }
    }

    public function paginate($perPage, $currentPage, $is_admin=false, $user_id = 0) {
        try {
            $query = Post::with(['user']);

            $query->latest();

            // jika bukan sebagai administrator
            if(!$is_admin) {
                if(!auth()->user()->hasRole('administrator'))
                $query->where('article_creator', auth()->user()->id);
            }

            if($user_id) {
                $query->where('article_creator', $user_id);
            }

            $data = $query->paginate($perPage, ['*'], 'page', $currentPage);
            $data->withPath(url('/articles'));

            return $data;
        }catch(\Throwable $e) {
            $this->report($e);

            return $this->sendError(400, 'Whoops, looks like something went wrong #paginate');
        }
    }

    public function store(StoreRequest $request){
        DB::BeginTransaction();
        try{
            $item = array(
                'title'      => $request->title,
                'content' => $request->content,
                'article_creator'     => auth()->user()->id,
            );

            if ($request->hasFile('image')) {
                if(file_exists(public_path('temp_image/avatar.jpg'))) {
                    $filename = $request->image->getClientOriginalName(); // getting file name
                    $extension = $request->image->getClientOriginalExtension(); // getting file extension
                    $filename = preg_replace('/\s/', '-', $filename);

                    $new_name = time() . "_" . $filename;
                    $item['thumbnail_image'] = $new_name;
                    File::move(public_path('temp_image/avatar.jpg'), storage_path('app\\public\\image\\' . $new_name));
                }
            }

            $article = Post::create($item);

            // rebuild cache
            if($article) {
                $this->rebuildCache($article->article_creator);
            }

            DB::commit();
            return $this->sendResponse(null, 'Tambah data berhasil', 201);
		}catch(\Throwable $e) {
            DB::rollback();
            $this->report($e);

            return $this->sendError(400, 'Whoops, looks like something went wrong #store');
        }
	}

    public function update($id, UpdateRequest $request){
        DB::BeginTransaction();
        try{
            // jika bukan administrator
            if(!auth()->user()->hasRole('administrator'))
                $article = Post::where('article_creator', auth()->user()->id)->where('id', $id)->first();
            else
                $article = Post::find($id);

            if(!$article) {
                return $this->sendError(404, 'Data not found #update');
            }

            $item = array(
                'title'      => $request->title,
                'content' => $request->content
            );

            if ($request->hasFile('image')) {
                $image_old = $article->thumbnail_image;

                if($image_old != $request->file('image')->getClientOriginalName() && file_exists(public_path('temp_image/avatar.jpg'))) {
                    $filename = $request->image->getClientOriginalName(); // getting file extension
                    $filename = preg_replace('/\s/', '-', $filename);

                    $new_name = time() . "_" . $filename;
                    $item['thumbnail_image'] = $new_name;
                    File::move(public_path('temp_image/avatar.jpg'), storage_path('app\\public\\image\\' . $new_name));

                    // delete old image
                    if($image_old) {
                        File::delete(storage_path('app\\public\\image\\' . $image_old));
                    }
                }
            }

            $data = $article->update($item);

            // rebuild cache
            if($data) {
                $this->rebuildCache($article->article_creator);
            }

            DB::commit();
            return $this->sendResponse(null, 'Update data berhasil');
		}catch(\Throwable $e) {
            DB::rollback();
            $this->report($e);

            return $this->sendError(400, 'Whoops, looks like something went wrong #update');
        }
	}

	public function destroy($id){
        DB::BeginTransaction();
        try {
            // jika bukan administrator
            if(!auth()->user()->hasRole('administrator'))
                $article = Post::where('article_creator', auth()->user()->id)->where('id', $id)->first();
            else
                $article = Post::find($id);

            if(!$article) {
                return $this->sendError(404, 'Data not found #update');
            }

            $image = $article->thumbnail_image;

            // delete old image
            if($image) {
                File::delete(storage_path('app\\public\\image\\' . $image));
            }

            // rebuild cache
            if($article->delete()) {
                $this->rebuildCache($article->article_creator);
            }

            DB::commit();
            return $this->sendResponse(null, 'Delete data berhasil');
		} catch(\Throwable $e) {
            DB::rollback();
            $this->report($e);

            return $this->sendError(400, 'Whoops, looks like something went wrong #destroy');
        }
	}

    public function uploadImage(Request $request)
    {
        try{
            $file = new Filesystem;
            $file->cleanDirectory(public_path('temp_image'));
            if ($request->hasFile('avatar')) {

                $filename = $request->avatar->getClientOriginalName(); // getting file extension
                $filename = preg_replace('/\s/', '-', $filename);

                $destinationPath = 'temp_image'; // upload path
                $extension = $request->avatar->getClientOriginalExtension(); // getting file extension

                $new_name = $filename;

                $request->avatar->move(public_path("$destinationPath"), $new_name);
            }
        } catch(\Throwable $e) {
            $this->report($e);

            return $this->sendError(400, 'Whoops, looks like something went wrong #upload_image');
        }
    }
}
