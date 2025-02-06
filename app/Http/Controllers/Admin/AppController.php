<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class AppController extends Controller
{
    public function __construct() {}

    public function index(): View | Response
    {
        return render_view("_admin.app.list");
    }
}
