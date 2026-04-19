<?php

namespace Modules\Operations\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Modules\Operations\Services\OperationsService;


class OperationsController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly OperationsService $service) {}

    
    public function index()
    {
        return $this->service->render('index');
    }

    
    public function create()
    {
        return $this->service->render('create');
    }

    
    public function store(Request $request) {}

    
    public function show($id)
    {
        return $this->service->render('show');
    }

    
    public function edit($id)
    {
        return $this->service->render('edit');
    }

    
    public function update(Request $request, $id) {}

    
    public function destroy($id) {}
}
