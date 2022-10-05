<?php
namespace App\Http\Controllers\API\Kos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Interfaces\Kos\KosInterface;
use App\Http\Requests\Kos\{
    StoreRequest,
    UpdateRequest,
};

class KosController extends Controller
{
    private $kosInterface;

    public function __construct(KosInterface $kosInterface)
    {
        $this->kosInterface = $kosInterface;
    }

    public function index(Request $request)
    {
        return $this->kosInterface->index($request);
    }

    public function show($id)
    {
        return $this->kosInterface->show($id);
    }

    public function store(StoreRequest $request)
    {
        return $this->kosInterface->store($request);
    }

    public function update($id, UpdateRequest $request)
    {
        return $this->kosInterface->update($id, $request);
    }

    public function destroy($id)
    {
        return $this->kosInterface->destroy($id);
    }

    public function indexUser(Request $request)
    {
        return $this->kosInterface->indexUser($request);
    }

    public function showUser($id)
    {
        return $this->kosInterface->showUser($id);
    }

    public function roomAvailibility($id)
    {
        return $this->kosInterface->roomAvailibility($id);
    }

    public function dashboard()
    {
        return $this->kosInterface->dashboard();
    }
}
