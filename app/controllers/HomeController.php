<?php

namespace App\Controllers;

use Core\View;

class HomeController {
    public function index() {
        View::render('home', [
            'title' => 'Welcome to Auto Routing'
        ]);
    }
    
    public function about() {
        View::render('about');
    }
}