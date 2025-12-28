<?php

namespace App\Middlewares;

use Core\Middleware;
use Core\Response;
use Core\Session;

class AuthMiddleware extends Middleware {
    public function handle() {
        Session::start();
        
        if (!Session::has('user_id')) {
            $response = new Response();
            $response->redirect('/login');
            return false;
        }
        
        return true;
    }
}