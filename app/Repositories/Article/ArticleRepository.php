<?php

namespace App\Repositories\Article;

use App\Interfaces\Article\ArticleInterface;
use App\Traits\{BugsnagTrait, ResponseBuilder, FileManagerTrait};
use App\Models\Article\Post;
use App\Http\Resources\Article\{
    ArticleResource,
    ArticleCollection
};
use Throwable;
use DB;
use Auth;
use Illuminate\Support\Facades\Cache;

class ArticleRepository implements ArticleInterface
{
    use BugsnagTrait, ResponseBuilder, FileManagerTrait;

    const PER_PAGE = 10;
    const CURRENT_PAGE = 1;

    public function index($request) {
        try {
            $perPage = $request->perPage?:self::PER_PAGE;
            $currentPage = $request->currentPage?:self::CURRENT_PAGE;

            $user_id = 0;
            if(!auth()->user()->hasRole('administrator'))
                $user_id = auth()->user()->id;

            $data = Cache::rememberForever('article'.$user_id.$perPage.$currentPage, function () use ($perPage, $currentPage) {
                return $this->paginate($perPage, $currentPage);
            });

            return new ArticleCollection($data);
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

	public function show($id){
        try {
            $id = dekrip($id);
            // jika bukan administrator
            if(!auth()->user()->hasRole('administrator'))
                $article = Post::where('article_creator', auth()->user()->id)->where('id', $id)->first();
            else
                $article = Post::find($id);

            if(!$article) {
                return $this->sendError(404, 'Data not found #update');
            }

            DB::commit();
            return $this->sendResponse(new ArticleResource($article));
		} catch(\Throwable $e) {
            DB::rollback();
            $this->report($e);

            return $this->sendError(400, 'Whoops, looks like something went wrong #destroy');
        }
	}

    public function store($request){
        DB::BeginTransaction();
        try{
            $filename = '';
            if($request->filled('image'))
                $filename = $this->uploadFileBase64($request->image, 'image');

            $item = array(
                'title'      => $request->title,
                'content' => $request->content,
                'article_creator' => auth()->user()->id,
                'thumbnail_image' => $filename
            );

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

    public function update($id, $request){
        DB::BeginTransaction();
        try{
            $id = dekrip($id);
            // jika bukan administrator
            if(!auth()->user()->hasRole('administrator'))
                $article = Post::where('article_creator', auth()->user()->id)->where('id', $id)->first();
            else
                $article = Post::find($id);

            if(!$article) {
                return $this->sendError(404, 'Data not found #update');
            }

            $filename = $article->thumbnail_image;
            if($request->filled('image')) {
                if ($article->thumbnail_image) {
                    \File::delete(storage_path('app\\public\\image\\' . $article->thumbnail_image));
                }

                //upload file
                $filename = $filename = $this->uploadFileBase64($request->image, 'image');
            }

            $item = array(
                'title'   => $request->title ?? $article->title,
                'content' => $request->content ?? $article->content,
                'thumbnail_image' => $filename
            );

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
            $id = dekrip($id);
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
                \File::delete(storage_path('app\\public\\image\\' . $image));
            }

            // rebuild cache
            if($article->delete()){
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
}
