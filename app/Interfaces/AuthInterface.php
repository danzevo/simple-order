<?php

namespace App\Interfaces;

interface AuthInterface
{
    public function register(oject $data);

    public function login(oject $data);

    public function profile();

    public function logout();
}
