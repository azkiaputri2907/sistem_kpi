<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    // =========================================================================
    // HELPER SPREADSHEET (Wajib ada untuk baca data)
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

    /**
     * Menampilkan halaman form login
     */
    public function showLogin()
    {
        // Ganti Auth::check() dengan pengecekan Session manual
        if (Session::has('is_logged_in')) {
            Session::flush(); // Bersihkan session lama
        }

        return view('auth.login');
    }

    /**
     * Memproses data login dari form
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
            'role_id'  => 'required'
        ]);

        // 1. Ambil semua data user dari Spreadsheet
        $users = $this->readSheet('master_user');

        // 2. Cari user berdasarkan email (Abaikan spasi & huruf besar/kecil)
        $user = $users->first(function($item) use ($request) {
            return strtolower(trim($item->email ?? '')) === strtolower(trim($request->email));
        });

        // Jika user ditemukan
        if ($user) {

            $inputPassword = $request->password;
            $dbPassword = trim($user->password ?? '');

            $isPasswordValid = false;

            // 3. CEK FORMAT PASSWORD (SUPER AMAN)
            // Cek apakah password di spreadsheet merupakan Hash Bcrypt (diawali $2y$ atau $2a$)
            $isBcrypt = str_starts_with($dbPassword, '$2y$') || str_starts_with($dbPassword, '$2a$');

            if ($isBcrypt) {
                // Jika bentuknya Hash, gunakan fungsi pengecekan bawaan Laravel
                $isPasswordValid = Hash::check($inputPassword, $dbPassword);
            } else {
                // Jika bentuknya Teks Biasa (seperti 'password123'), cocokkan langsung!
                $isPasswordValid = ($inputPassword === $dbPassword);
            }

            // Jika Password Benar
            if ($isPasswordValid) {

                $selectedRole = $request->role_id;
                $isAuthorized = false;
                $userRoleId = trim($user->role_id ?? '');

                // 4. Validasi Role
                if ($selectedRole === 'pimpinan') {
                    // Izinkan jika user adalah Kajur (3) atau Kaprodi (4)
                    if (in_array($userRoleId, [3, 4, '3', '4'])) {
                        $isAuthorized = true;
                    }
                } else {
                    // Untuk Admin (2) atau Super (1), harus tepat sama
                    if ($userRoleId == $selectedRole) {
                        $isAuthorized = true;
                    }
                }

                // 5. Login Sukses (Simpan ke Session Manual)
                if ($isAuthorized) {
                    // Jangan simpan password ke dalam session demi keamanan
                    unset($user->password);

                    Session::put('is_logged_in', true);
                    Session::put('user', $user);

                    return redirect()->intended('/dashboard');
                }

                return back()->withErrors(['email' => 'Pilihan Role tidak sesuai dengan akses akun Anda.'])->withInput();
            }
        }

        // Jika Email / Password salah
        return back()->withErrors(['email' => 'Email atau Password salah.'])->withInput();
    }

    /**
     * Memproses logout (keluar sistem)
     */
    public function logout(Request $request)
    {
        // Hapus semua session manual pengganti Auth::logout()
        Session::flush();

        return redirect('/login');
    }
}
