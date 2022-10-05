<?php

namespace App\Repositories;

use App\Interfaces\AuthInterface;
use App\Traits\{BugsnagTrait, ResponseBuilder};
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\User\UserCredit;
use App\Http\Resources\User\UserResource;
use Throwable;
use DB;
use Auth;

class AuthRepository implements AuthInterface
{
    use BugsnagTrait, ResponseBuilder;

    public function register($request)
    {
        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            if($user) {
                $user->assignRole('regular');
            }

            $data = array(
                'data' => new UserResource($user),
                'access_token' => $token,
                'token_type' => 'Bearer'
            );

            DB::commit();
            return $this->sendResponse($data, 'Pendaftaran berhasil', 201);
        } catch (Throwable $e) {
            DB::rollback();
            $this->report($e);

            return $this->sendError(400, 'Whoops, looks like something went wrong #register');
        }
    }

    public function login($request)
    {
        try {
            if (!Auth::attempt($request->only('email', 'password')))
            {
                return $this->sendError(401, 'Unauthorized');
            }

            $user = User::where('email', $request['email'])->firstOrFail();

            $token = $user->createToken('auth_token')->plainTextToken;

            $data = array(
                'message' => 'Hi '.$user->name.', welcome to home',
                'access_token' => $token,
                'token_type' => 'Bearer'
            );

            return $this->sendResponse($data, 'Login berhasil');
        } catch (Throwable $e) {
            $this->report($e);

            return $this->sendError(400, 'Whoops, looks like something went wrong #login');
        }
    }

    public function profile()
    {
        try {
            return $this->sendResponse(new UserResource(auth()->user()), null);
        } catch (Throwable $e) {
            $this->report($e);

            return $this->sendError(400, 'Whoops, looks like something went wrong #profile');
        }
    }

    public function logout()
    {
        try {
            auth()->user()->tokens()->delete();

            return $this->sendResponse(null, 'You have successfully logged out and the token was successfully deleted');
        } catch (Throwable $e) {
            $this->report($e);

            return $this->sendError(400, 'Whoops, looks like something went wrong #logout');
        }
    }
}
