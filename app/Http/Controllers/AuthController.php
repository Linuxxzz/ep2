<?php

namespace App\Http\Controllers;

use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller{
    public function registerForm(){
        return view('auth.register');
    }

    // Metodo para guardar la informacion en la base de datos
    public function register(Request $request){
        // Recabar la información desde el formulario
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'phone' => 'required',
            'direccion' => 'nullable',
            'password' => 'required|confirmed|min: 8',
        ]);

        // Guardar la información en la base de datos
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'direccion' => $request->direccion,
            'password' => Hash::make($request->password),
            'is_admin' => $request->has('is_admin'),
            // Uso de has() para el manejo del checkbox
        ]);

        //Iniciar sesión de forma automática
        Auth::login($user);

        return redirect()->route('home')->with('success', '¡Registro exitoso! Bienvenido ' . $user->name);
    }

    // Método para regresar vista de inicio de sesión
    public function loginForm(){
        return view('auth.login');
    }

    // Método para iniciar sesión
    public function login(Request $request){
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Realizar intento de inicio de sesión
        if(Auth::attempt($data)){
            // Obtener información de la sesión y generar sus credenciales
            $request -> session()->regenerate();
            // Redireccionar al usuario con su sesión iniciada
            return redirect()->route('home')->with('success', '¡Bienvenido de nuevo!');
        }

        // Si los datos son incorrectos, mandar un error
        return back()->with('error', 'El correo o la contraseña son incorrectos. Inténtalo de nuevo.');
    }

    // Método para cerrar sesión e invalidar las credenciales
    public function logout(Request $request){
        // Cerrar sesión
        Auth::logout();
        
        // Cierre de credenciales en las sesiones
        $request -> session() -> invalidate();
        $request -> session() -> regenerateToken();

        return redirect('/')->with('info', 'Has cerrado sesión correctamente');
    }

    // Panel principal del administrador
    public function adminDashboard(){
        return view('admin.dashboard');
    }

    public function consultarUsuarios()
    {
        $usuarios = User::all();
        return view('admin.consultarusuarios', compact('usuarios'));
    }

    public function promoteToAdmin($id)
    {
        $usuario = User::findOrFail($id);
        $usuario->is_admin = '1';
        $usuario->save();

        return redirect()->back()->with('success', 'Usuario promovido a administrador correctamente.');
    }

    public function destroy($id)
    {
        $usuario = User::findOrFail($id);
        $usuario->delete();

        return redirect()->back()->with('success', 'Usuario eliminado correctamente.');
    }
}
