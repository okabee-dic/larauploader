<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function create_user(Request $request){
        $user = Auth::user();

        $success = false;
        if($user->admin == 1){
            //access from admin user
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6',
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
            ]);

            $success = true;

            return response()->json([
                'message' => 'ユーザーの作成に成功しました!',
                'success' => $success,
                'user' => $user
            ]);

            
        } else {
            $success = false;

            return response()->json([
                'message' => 'ユーザーの作成に失敗しました',
                'success' => $success
            ]);
        }
    }
}
