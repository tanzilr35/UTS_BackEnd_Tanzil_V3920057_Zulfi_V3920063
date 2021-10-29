<?php
// Line 3 = Untuk mencegah akses langsung ke file controller.
defined('BASEPATH') or exit('No direct script access allowed');

// Line 5-119 = Buat kelas Auth sebagai controller halaman utama saat masuk ke website
class Auth extends CI_Controller
{
    // Line 9-15 = Membuat fungsi construct untuk mendeklarasikan variabel/objek yang sering digunakan
    public function __construct()
    {
        // Line 12-15 = Untuk memperbanyak objek-objek yg ingin dideklarasi dari parent construct
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('Auth_model', 'auth');
        $this->load->model('Admin_model', 'admin');
    }

    // Line 19-24 = Membuat private fungsi has login sebagai bagian dari fungsi index 
    private function _has_login()
    {
        // Line 22-24 = Jika data user sudah valid makan akan di arahkan ke halaman dashboard
        if ($this->session->has_userdata('login_session')) {
            redirect('dashboard');
        }
    }

    // Line 28-74 = Membuat fungsi index utama untuk login
    public function index()
    {
        // Line 31 = Memanggil fungsi has login
        $this->_has_login();

        // Line 34-35 = Membuat aturan form login untuk username dan password
        $this->form_validation->set_rules('username', 'Username', 'required|trim');
        $this->form_validation->set_rules('password', 'Password', 'required|trim');

        // Line 38-40 = Jika login ada yg salah, maka akan diarahkan ke halaman login
        if ($this->form_validation->run() == false) {
            $data['title'] = 'Login Aplikasi';
            $this->template->load('templates/auth', 'auth/login', $data);
        }

        // Line 43-73 = Ketentuan login lainnya
        else {
            $input = $this->input->post(null, true);

            // Line 48-72 = Untuk mengecek apakah username dan password yg diinput sudah benar atau belum
            $cek_username = $this->auth->cek_username($input['username']);
            if ($cek_username > 0) {
                $password = $this->auth->get_password($input['username']);
                if (password_verify($input['password'], $password)) {
                    $user_db = $this->auth->userdata($input['username']);
                    if ($user_db['is_active'] != 1) {
                        set_pesan('akun anda belum aktif/dinonaktifkan. Silahkan hubungi admin.', false);
                        redirect('login');
                    } else {
                        $userdata = [
                            'user'  => $user_db['id_user'],
                            'role'  => $user_db['role'],
                            'timestamp' => time()
                        ];
                        $this->session->set_userdata('login_session', $userdata);
                        redirect('dashboard');
                    }
                } else {
                    set_pesan('password salah', false);
                    redirect('auth');
                }
            } else {
                set_pesan('username belum terdaftar', false);
                redirect('auth');
            }
        }
    }

    // Line 77-83 = Membuat fungsi logout dan jika sudah logout akan di arahkan ke halaman login (auth)
    public function logout()
    {
        $this->session->unset_userdata('login_session');

        set_pesan('anda telah berhasil logout');
        redirect('auth');
    }

    // Line 86-118 = Buat fungsi register untuk daftar akun
    public function register()
    {
        // Line 89-94 = Membuat aturan form register untuk username, password, konfirmasi password, nama, email, dan no.telp
        $this->form_validation->set_rules('username', 'Username', 'required|trim|is_unique[user.username]|alpha_numeric');
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[3]|trim');
        $this->form_validation->set_rules('password2', 'Konfirmasi Password', 'matches[password]|trim');
        $this->form_validation->set_rules('nama', 'Nama', 'required|trim');
        $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email|is_unique[user.email]');
        $this->form_validation->set_rules('no_telp', 'Nomor Telepon', 'required|trim');

        // Line 97-117 = Ketentuan regristrasi
        if ($this->form_validation->run() == false) {
            $data['title'] = 'Buat Akun';
            $this->template->load('templates/auth', 'auth/register', $data);
        } else {
            $input = $this->input->post(null, true);
            unset($input['password2']);
            $input['password']      = password_hash($input['password'], PASSWORD_DEFAULT);
            $input['role']          = 'gudang';
            $input['foto']          = 'user.png';
            $input['is_active']     = 0;
            $input['created_at']    = time();

            $query = $this->admin->insert('user', $input);
            if ($query) {
                set_pesan('daftar berhasil. Selanjutnya silahkan hubungi admin untuk mengaktifkan akun anda.');
                redirect('login');
            } else {
                set_pesan('gagal menyimpan ke database', false);
                redirect('register');
            }
        }
    }
}