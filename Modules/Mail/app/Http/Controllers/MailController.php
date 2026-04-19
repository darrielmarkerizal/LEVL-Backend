<?php

namespace Modules\Mail\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MailController extends Controller
{
    
    public function index()
    {
        return view('mail::index');
    }

    
    public function create()
    {
        return view('mail::create');
    }

    
    public function store(Request $request)
    {
        
    }

    
    public function show($id)
    {
        return view('mail::show');
    }

    
    public function edit($id)
    {
        return view('mail::edit');
    }

    
    public function update(Request $request, $id)
    {
        
    }

    
    public function destroy($id)
    {
        
    }
}
