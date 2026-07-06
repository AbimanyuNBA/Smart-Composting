<?php


namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class AuthController extends Controller
{


    public function login()
    {

        return view('auth.login');

    }




    public function prosesLogin(Request $request)
    {


        $request->validate([

            'username'=>'required',
            'password'=>'required'

        ]);




        $credential = [

            'username'=>$request->username,

            'password'=>$request->password

        ];



        if(Auth::attempt($credential)){


            $request->session()->regenerate();


            return redirect('/');


        }



        return back()->with(

            'error',

            'Username atau password salah'

        );

    }





    public function logout(Request $request)
    {


        Auth::logout();


        $request->session()->invalidate();


        $request->session()->regenerateToken();


        return redirect('/login');


    }


}
