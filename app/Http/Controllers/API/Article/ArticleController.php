<?php
namespace App\Http\Controllers\API\Article;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Interfaces\Article\ArticleInterface;
use App\Http\Requests\Article\{
    StoreRequest,
    UpdateRequest,
};

class ArticleController extends Controller
{
    private $articleInterface;

    public function __construct(ArticleInterface $articleInterface)
    {
        $this->articleInterface = $articleInterface;
    }

    public function index(Request $request)
    {
        return $this->articleInterface->index($request);
    }

    public function show($id)
    {
        return $this->articleInterface->show($id);
    }

    public function store(StoreRequest $request)
    {
        return $this->articleInterface->store($request);
    }

    public function update($id, UpdateRequest $request)
    {
        return $this->articleInterface->update($id, $request);
    }

    public function destroy($id)
    {
        return $this->articleInterface->destroy($id);
    }
}
