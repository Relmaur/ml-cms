<?php

namespace App\Controllers;

use Core\View;
use Core\Http\RedirectResponse;

use App\Controllers\BaseController;

class DashboardController extends BaseController
{
    public function index()
    {
        return View::render('dashboard/home', [
            'pageTitle' => 'ML CMS | Dashboard'
        ], 'dashboard');
    }
}
