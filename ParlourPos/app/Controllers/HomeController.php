<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Support\Request;
use App\Support\Response;

final class HomeController extends Controller
{
    public function index(Request $request): Response
    {
        return $this->render('home.index', ['title' => 'Application Foundation']);
    }
}
