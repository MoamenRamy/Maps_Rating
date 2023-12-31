<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewFormRequest;
use App\Models\Review;
use Illuminate\Http\Request;

class reviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ReviewFormRequest $request)
    {
        if ($request->user()->reviews()->wherePlace_id($request->place_id)->exists()) {
            return redirect(url()->previous() . '#review-div')->with('fail', 'لقد قييمت المكان من قبل');
        }
        $review = Review::create($request->all()+ ['user_id' => auth()->id()]);
        return redirect(url()->previous() . '#review-div')->with('success', 'تم إضافة التقييم بنجاح');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
