<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use App\Http\Requests\User\{
    StoreRequest,
    UpdateRequest
};
use App\Traits\{BugsnagTrait, ResponseBuilder};
use DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use BugsnagTrait, ResponseBuilder;

    const PER_PAGE = 10;
    const CURRENT_PAGE = 1;

    public function index(Request $request) {
        try {
            $perPage = self::PER_PAGE;
            $currentPage = $request->page ?? self::CURRENT_PAGE;

            $paging = (($perPage * $currentPage)-$perPage)+1;

            $query = User::select('users.id', 'users.name', 'users.email', 'roles.name as role');
            $query->leftJoin('model_has_roles', 'model_has_roles.model_id', '=', 'users.id');
            $query->leftJoin('roles', 'roles.id', '=', 'model_has_roles.role_id');

            $query->latest('users.created_at');

            $data = $query->paginate($perPage, ['*'], 'page', $currentPage);

            $role = Role::all()->pluck('name');

            return view('pages/user/index', compact('data', 'paging', 'role'));
        }catch(\Throwable $e) {
            $this->report($e);

            return $this->sendError(400, 'Whoops, looks like something went wrong #index');
        }
    }

    public function store(StoreRequest $request){
        DB::BeginTransaction();
        try{
            $item = array(
                'name'  => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            );

            $user = User::create($item);

            if($user) {
                if($request->role)
                    $user->assignRole($request->role);
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
            $user = User::find($id);

            if(!$user) {
                return $this->sendError(404, 'Data not found #update');
            }

            $item = array(
                'name'  => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            );

            $data = $user->update($item);

            if($data) {
                if($request->role)
                    $user->syncRoles($request->role);
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
            $user = User::find($id);

            if(!$user) {
                return $this->sendError(404, 'Data not found #update');
            }

            $user->delete();

            DB::commit();
            return $this->sendResponse(null, 'Delete data berhasil');
		} catch(\Throwable $e) {
            DB::rollback();
            $this->report($e);

            return $this->sendError(400, 'Whoops, looks like something went wrong #destroy');
        }
	}
}
