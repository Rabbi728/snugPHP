<?php

namespace App\Controllers;

use Core\View;
use Core\Database;
use Core\Request;
use Core\Response;

class UserController {
    public function index() {
        $users = Database::table('users')
            ->orderBy('created_at', 'DESC')
            ->get();
        
        View::render('users/index', ['users' => $users]);
    }
    
    public function show($id) {
        $user = Database::table('users')->find($id);
        
        if (!$user) {
            View::render('errors/404');
            return;
        }
        
        View::render('users/show', ['user' => $user]);
    }
    
    public function create() {
        View::render('users/create');
    }
    
    public function store() {
        $request = new Request();
        $response = new Response();
        
        $data = [
            'name' => $request->post('name'),
            'email' => $request->post('email'),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $id = Database::table('users')->insert($data);
        
        $response->redirect("/user/show/{$id}");
    }
    
    public function edit($id) {
        $user = Database::table('users')->find($id);
        View::render('users/edit', ['user' => $user]);
    }
    
    public function update($id) {
        $request = new Request();
        $response = new Response();
        
        $data = [
            'name' => $request->post('name'),
            'email' => $request->post('email')
        ];
        
        Database::table('users')->where('id', $id)->update($data);
        
        $response->redirect("/user/show/{$id}");
    }
    
    public function delete($id) {
        $response = new Response();
        
        Database::table('users')->where('id', $id)->delete();
        
        $response->redirect('/user/index');
    }
}