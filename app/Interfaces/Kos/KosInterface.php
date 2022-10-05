<?php

namespace App\Interfaces\Kos;

interface KosInterface
{
    public function index(oject $data);

    public function show(int $id);

    public function store(oject $data);

    public function update(int $id, oject $data);

    public function destroy(int $id);

    public function roomAvailibility(int $id);

    public function indexUser(oject $data);

    public function showUser(int $id);

    public function dashboard();
}
