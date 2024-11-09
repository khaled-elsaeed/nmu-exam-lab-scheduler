<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller; // Import the base Controller
use Illuminate\Http\Request;

class AdminHomeController extends Controller
{
    public function showHomePage()
    {
        return view('admin.home');
    }
}
