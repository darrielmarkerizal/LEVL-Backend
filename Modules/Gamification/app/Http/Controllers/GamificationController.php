<?php

namespace Modules\Gamification\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Gamification\Services\GamificationService;

class GamificationController extends Controller
{
    public function __construct(private readonly GamificationService $service) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->service->render('index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return $this->service->render('create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return $this->service->render('show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return $this->service->render('edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {}
}
