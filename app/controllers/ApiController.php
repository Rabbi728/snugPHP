<?php

namespace App\Controllers;

use Core\Response;
use Core\Database;

class ApiController {
    public function users() {
        $response = new Response();
        $users = Database::table('users')->get();
        $response->json($users);
    }
    
    public function user($id) {
        $response = new Response();
        $user = Database::table('users')->find($id);
        
        if (!$user) {
            $response->json(['error' => 'User not found'], 404);
            return;
        }
        
        $response->json($user);
    }
}