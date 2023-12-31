<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class categoryController extends Controller
{
    public function show(Category $category)
    {
        $places = $category->places()->get();

        return view('list', compact('places'));
    }
}
