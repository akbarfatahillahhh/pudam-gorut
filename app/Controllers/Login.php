<?php

namespace App\Controllers;

use App\Models\ModelLogin;

class Login extends BaseController
{

    public function __construct()
    {
        if (session('logged_in')) {
            return redirect()->to('/home');
        }
    }

    public function index()
    {
        return view('login');
    }

    public function login()
    {
        if ($this->request->isAJAX()) {
            $username = $this->request->getPost('username');
            $password = $this->request->getPost('password');

            $users = $this->db->table('users')
                ->where('users.username', $username)
                ->get()
                ->getRow();

            if ($users) {
                if ($users->blokir == 'N') {
                    if (md5($password) == $users->password) {
                        $data = [
                            'id_session' => $users->id_session,
                            'username' => $users->username,
                            'nama_lengkap' => $users->nama_lengkap,
                            'level' => $users->level,
                            'id_pegawai' => $users->id_pegawai,
                            'logged_in' => TRUE
                        ];

                        session()->set($data);
                        $response = [
                            'status' => 'success',
                            'message' => 'Login Berhasil'
                        ];
                    } else {
                        $response = [
                            'status' => 'error',
                            'message' => 'Password Salah'
                        ];
                    }
                } else {
                    $response = [
                        'status' => 'error',
                        'message' => 'Akun Tidak Aktif'
                    ];
                }
            } else {
                $response = [
                    'status' => 'error',
                    'message' => 'Username Tidak Ditemukan'
                ];
            }



            return $this->response->setJSON($response);
        } else {
            return redirect()->to('login');
        }
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}
