<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ConsultationController extends Controller
{
    public function index(): View
    {
        return view('consultation.index');
    }
}
