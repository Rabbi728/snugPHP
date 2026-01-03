<?php

namespace App\Controllers;

class HomeController {

    public function index() {
        json([
            'message' => 'Hello SlugPHP!'
        ]);
    }
    
}