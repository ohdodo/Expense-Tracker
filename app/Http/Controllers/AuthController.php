<?php 

namespace App\Http\Controllers;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Session;




class AuthController extends Controller{


    public function showRegistrationForm(){
        return view('auth.registrationView');
    }

    public function showLoginForm(){
        return view('auth.loginView');
    }

    public function showHomePage(){
        return view('home');
    }

    public function registration(Request $request){
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'confirm_password' => 'required|string|min:8',
        ]);

        if($request->password == $request->confirm_password){
            $save = User::insert([
                'name'=> $request->name,
                'email'=> $request->email,
                'password'=> Hash::make($request->password),
            ]);           

            return redirect()->route('auth.loginView')->with('success', 'Registration successful. Please login.');
        } else {
            return redirect()->back()->withErrors(['password' => 'Passwords do not match.']);
        }
    }

    public function login(Request $request){
        $checkUser = User::where('email', $request->email)->first();

        if($checkUser && Hash::check($request->password, $checkUser->password)){
            Session::push('user', $checkUser);
            return redirect('/');
        } else{
            return redirect('/login')->withErrors([
                'login' => 'Invalid email or password.'
            ])->withInput();
        }
}


    

}