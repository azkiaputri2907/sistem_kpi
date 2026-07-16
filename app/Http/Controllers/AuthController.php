<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    // =========================================================================
    // HELPER SPREADSHEET
    // =========================================================================
    
    private function getApiUrl()
    {
        return env('GOOGLE_SCRIPT_URL');
    }

    private function readSheet($sheetName)
    {
        $response = Http::get($this->getApiUrl(), [
            'action' => 'read',
            'sheet'  => $sheetName
        ]);
        
        $data = $response->json('data') ?? [];
        return collect(json_decode(json_encode($data), FALSE));
    }

    // =========================================================================
    // CONTROLLER LOGIC
    // =========================================================================

    public function showLogin()
    {
        if (Session::has('is_logged_in')) {
            Session::flush();
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        // 1. Validasi input (role_id dihapus)
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        // 2. Ambil data user
        $users = $this->readSheet('master_user');

        // 3. Cari user berdasarkan email
        $user = $users->first(function($item) use ($request) {
            return strtolower(trim($item->email ?? '')) === strtolower(trim($request->email));
        });

        // 4. Jika user ditemukan
        if ($user) {
            $inputPassword = $request->password;
            $dbPassword = trim($user->password ?? '');

            $isPasswordValid = false;

            // Cek apakah password di spreadsheet merupakan Hash Bcrypt
            $isBcrypt = str_starts_with($dbPassword, '$2y$') || str_starts_with($dbPassword, '$2a$');

            if ($isBcrypt) {
                $isPasswordValid = Hash::check($inputPassword, $dbPassword);
            } else {
                $isPasswordValid = ($inputPassword === $dbPassword);
            }

            // 5. Jika Password Benar, Langsung Login
            if ($isPasswordValid) {
                // Jangan simpan password ke session
                unset($user->password); 
                
                Session::put('is_logged_in', true);
                Session::put('user', $user);

                return redirect()->intended('/dashboard');
            }
        }

        // Jika Email / Password salah
        return back()->withErrors(['email' => 'Email atau Password salah.'])->withInput();
    }

    public function logout(Request $request)
    {
        Session::flush();
        return redirect('/login');
    }
}