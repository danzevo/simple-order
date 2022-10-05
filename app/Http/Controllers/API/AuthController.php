<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Interfaces\AuthInterface;
use App\Http\Requests\Auth\RegisterRequest;

class AuthController extends Controller
{
    private $authInterface;

    public function __construct(AuthInterface $authInterface)
    {
        $this->authInterface = $authInterface;
    }

    public function register(RegisterRequest $request)
    {
        return $this->authInterface->register($request);
    }

    public function login(Request $request)
    {
        return $this->authInterface->login($request);
    }

    public function logout()
    {
        return $this->authInterface->logout();
    }

    public function profile()
    {
        return $this->authInterface->profile();
    }
}
