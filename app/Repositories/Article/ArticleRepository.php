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

class ArticleRepository implements ArticleInterface
{
    use BugsnagTrait, ResponseBuilder, FileManagerTrait;

    const PER_PAGE = 20;
    const CURRENT_PAGE = 1;

    public function index($request) {
        try {
            $perPage = $request->perPage?:self::PER_PAGE;
            $currentPage = $request->currentPage?:self::CURRENT_PAGE;

            $query = Post::with(['user']);

            if ($request->filled('q')) {
                $q = strtoupper(strip_tags(trim($request->q)));
                $query->where(function ($query) use($q) {
                    $query->where(DB::raw('upper(title)'), 'like', '%' . $q . '%')
                       ->orWhere(DB::raw('content'), 'like', '%' . $q . '%');
                  });
            }

            $query->latest();

            // jika bukan sebagai administrator
            if(!auth()->user()->hasRole('administrator'))
            $query->where('article_creator', auth()->user()->id);

            $data = $query->paginate($perPage, ['*'], 'page', $currentPage);

            return new ArticleCollection($data);
        }catch(\Throwable $e) {
            $this->report($e);

            return $this->sendError(400, 'Whoops, looks like something went wrong #index');
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

            $article->delete();

            DB::commit();
            return $this->sendResponse(null, 'Delete data berhasil');
		} catch(\Throwable $e) {
            DB::rollback();
            $this->report($e);

            return $this->sendError(400, 'Whoops, looks like something went wrong #destroy');
        }
	}
}
