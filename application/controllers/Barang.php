<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Line 5 = Buat kelas Barang harus diwariskan ke CI_Controller karena berada di folder controllers
class Barang extends CI_Controller
{
    // line 8 : membuat public fungsi construct
    public function __construct()
    {
        // line 11-16 : untuk memperbanyak deklarasi load
        parent::__construct(); 
        cek_login();

        $this->load->model('Admin_model', 'admin');
        $this->load->library('form_validation');
    }
    // line 18-23 : membuat fungsi index untuk menu barang serta pemanggilan data
    public function index()
    {
        $data['title'] = "Barang";
        $data['barang'] = $this->admin->getBarang();
        $this->template->load('templates/dashboard', 'barang/data', $data);
    }
    // line 25-30 : membuat form validasi nama barang, jenis dan satuan yang dipanggil dari library
    private function _validasi()
    {
        $this->form_validation->set_rules('nama_barang', 'Nama Barang', 'required|trim');
        $this->form_validation->set_rules('jenis_id', 'Jenis Barang', 'required');
        $this->form_validation->set_rules('satuan_id', 'Satuan Barang', 'required');
    }
    // line 32-39 : membuat fungsi add untuk CRUD pada tabel barang
    public function add()
    {
        $this->_validasi();

        if ($this->form_validation->run() == false) {
            $data['title'] = "Barang";
            $data['jenis'] = $this->admin->get('jenis');
            $data['satuan'] = $this->admin->get('satuan');

            // line 43-47 : Mengenerate ID Barang
            $kode_terakhir = $this->admin->getMax('barang', 'id_barang');
            $kode_tambah = substr($kode_terakhir, -6, 6);
            $kode_tambah++;
            $number = str_pad($kode_tambah, 6, '0', STR_PAD_LEFT); 
            $data['id_barang'] = 'B' . $number;

            $this->template->load('templates/dashboard', 'barang/add', $data);
            
            // line 51-63 : jika data berhasil diinput maka akan secara otomatis ditambahkan pada database 'barang'. 
        } else {
            $input = $this->input->post(null, true);
            $insert = $this->admin->insert('barang', $input);

            if ($insert) {
                set_pesan('data berhasil disimpan');
                redirect('barang');
            } else {
                set_pesan('gagal menyimpan data');
                redirect('barang/add');
            }
        }
    }
    // Line 65-88 membuat fungsi edit merupakan bagian CRUD dengan mendapatkan id pada data yang akan diedit
    public function edit($getId)
    {
        $id = encode_php_tags($getId);
        $this->_validasi();

        if ($this->form_validation->run() == false) {
            $data['title'] = "Barang";
            $data['jenis'] = $this->admin->get('jenis');
            $data['satuan'] = $this->admin->get('satuan');
            $data['barang'] = $this->admin->get('barang', ['id_barang' => $id]);
            $this->template->load('templates/dashboard', 'barang/edit', $data);
        } else {
            $input = $this->input->post(null, true);
            $update = $this->admin->update('barang', 'id_barang', $id, $input);

            if ($update) {
                set_pesan('data berhasil disimpan');
                redirect('barang');
            } else {
                set_pesan('gagal menyimpan data');
                redirect('barang/edit/' . $id);
            }
        }
    }
    // Line 90-99 : membuat fungsi delete merupakan bagian CRUD dengan mendapatkan id pada data yang akan dihapus
    public function delete($getId)
    {
        $id = encode_php_tags($getId);
        if ($this->admin->delete('barang', 'id_barang', $id)) {
            set_pesan('data berhasil dihapus.');
        } else {
            set_pesan('data gagal dihapus.', false);
        }
        redirect('barang');
    }
    // line 101-107 : membuat fungsi cek stok
    public function getstok($getId)
    {
        $id = encode_php_tags($getId);
        $query = $this->admin->cekStok($id);
        output_json($query);
    }
}
