<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    /*public function appAuth(Request $request){
        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('appToken')->accessToken;
            return response()->json(['token' => $token], 200);
        }
        return response()->json(['error' => 'Credenciales no válidas'], 401);
    }*/

    public function verificarUser($name, $email, $password){
        $user = User::select('email')->where('email','=',$email)->get();

        $user_auth = User::select('*')->where('email', '=', $email)->first();

        if($user_auth){
            $token = $user_auth->createToken('api_token')->plainTextToken;

            $message = array(
                "user" => $name,
                "token" => $token 
            );
            return response()->json($message);
        }else{
            $usuario = new User();
            $usuario->name = $name;
            $usuario->email = $email;
            $usuario->password = Hash::make($password);
            $usuario->save();
        }
    }

    public function acceder(Request $request){
        $email = $request->input("email");
        $password = $request->input("password");

        $datos = [
            "email" => $email,
            "password" => $password
        ];

        $password = $datos['password'];
        $response = Http::asForm()->post('https://dygav.es/api/login/appauth', $datos);
        $decodificacion = json_decode($response, true);

        if($decodificacion['message'] == "Login successful"){
            $name = $decodificacion['user']['fullname'];
            return $this->verificarUser($name, $email, $password);
        }else{
            return $response->json();
        }
    }
}
