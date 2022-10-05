<?php

namespace App\Interfaces\Article;

interface ArticleInterface
{
    public function index(oject $data);

    public function show(int $id);

    public function store(oject $data);

    public function update(int $id, oject $data);

    public function destroy(int $id);
}
