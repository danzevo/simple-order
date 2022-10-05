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

class ArticleController extends Controller
{
    use BugsnagTrait, ResponseBuilder;

    const PER_PAGE = 10;
    const CURRENT_PAGE = 1;

    public function index(Request $request) {
        try {
            $perPage = $request->perPage?:self::PER_PAGE;
            $currentPage = $request->page?:self::CURRENT_PAGE;

            $paging = (($perPage * $currentPage)-$perPage)+1;

            $query = Post::with(['user']);

            $query->latest();

            // jika bukan sebagai administrator
            if(!auth()->user()->hasRole('administrator'))
            $query->where('article_creator', auth()->user()->id);

            $data = $query->paginate($perPage, ['*'], 'page', $currentPage);

            return view('pages/article/index', compact('data', 'paging'));
        }catch(\Throwable $e) {
            $this->report($e);

            return $this->sendError(400, 'Whoops, looks like something went wrong #index');
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
                    File::move(public_path('temp_image/avatar.jpg'), public_path('image/' . $new_name));
                }
            }

            $article = Post::create($item);

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
                    File::move(public_path('temp_image/avatar.jpg'), public_path('image/' . $new_name));

                    // delete old image
                    if($image_old) {
                        File::delete(public_path('image/' . $image_old));
                    }
                }
            }

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
                File::delete(public_path('image/' . $image));
            }

            $article->delete();

            $message = array();
            $message['message'] = 'Data deleted successfully';

            DB::commit();
            return $this->sendResponse(null, 'Update data berhasil');
		} catch(\Throwable $e) {
            DB::rollback();
            $this->report($e);

            return $this->sendError(400, 'Whoops, looks like something went wrong #update');
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
