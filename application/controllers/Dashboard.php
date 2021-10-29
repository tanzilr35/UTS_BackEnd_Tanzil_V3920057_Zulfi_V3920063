<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Line 5-45 = Kelas Dasboard sebagai controller halaman utama saat sudah berhasil login
class Dashboard extends CI_Controller
{
    // Line 8-14 = Membuat fungsi construct untuk mendeklarasikan variabel/objek yang sering digunakan
    public function __construct()
    {
        parent::__construct();
        cek_login();

        $this->load->model('Admin_model', 'admin');
    }

    // Line 17-43 = Untuk memanggil chart2 yang ada di dashboard dari beberapa file seperi barang_masuk, barang_keluar
    public function index()
    {
        // Line 20-31 = Untuk pemanggilan data dari database (?)
        $data['title'] = "Dashboard";
        $data['barang'] = $this->admin->count('barang');
        $data['barang_masuk'] = $this->admin->count('barang_masuk');
        $data['barang_keluar'] = $this->admin->count('barang_keluar');
        $data['supplier'] = $this->admin->count('supplier');
        $data['user'] = $this->admin->count('user');
        $data['stok'] = $this->admin->sum('barang', 'stok');
        $data['barang_min'] = $this->admin->min('barang', 'stok', 30);
        $data['transaksi'] = [
            'barang_masuk' => $this->admin->getBarangMasuk(5),
            'barang_keluar' => $this->admin->getBarangKeluar(5)
        ];

        // Line 34-41 = Grafik line chart
        $bln = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];
        $data['cbm'] = [];
        $data['cbk'] = [];

        foreach ($bln as $b) {
            $data['cbm'][] = $this->admin->chartBarangMasuk($b);
            $data['cbk'][] = $this->admin->chartBarangKeluar($b);
        }

        $this->template->load('templates/dashboard', 'dashboard', $data);
    }
}
