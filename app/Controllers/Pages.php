<?php

namespace App\Controllers;

use App\Database\Migrations\Kategori;
use App\Database\Migrations\Pemasok;
use App\Models\BarangMasukModel;
use App\Models\BarangModel;
use App\Models\KategoriModel;
use App\Models\KonsumenModel;
use App\Models\PemasokModel;
use CodeIgniter\Database\Config;
use CodeIgniter\Database\Query;

class Pages extends BaseController
{
    protected $db;
    protected $barangmodel;
    protected $kategorimodel;
    protected $barangmasukmodel;
    protected $konsumenmodel;
    protected $pemasokmodel;
    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->barangmodel = new BarangModel();
        $this->kategorimodel = new KategoriModel();
        $this->barangmasukmodel = new BarangMasukModel();
        $this->konsumenmodel = new KonsumenModel();
        $this->pemasokmodel = new PemasokModel();
    }

    public function index()
    {
        $query = $this->db->query('SELECT count(stok) as stok_kosong FROM barang WHERE stok=0');
        $stok_kosonng = $query->getResultArray();
        $data = [
            'stok_kosong' => $stok_kosonng,
            "title" => "Beranda",
            "active" => "beranda"
        ];
        return view('Pages/dashboard', $data);
    }


    public function stokmenipis()
    {
        $query = $this->db->query("SELECT barang.id_barang, 
                                    barang.nama_barang AS barang,
                                    kategori.nama_kategori AS kategori,
                                    barang.stok AS stok, barang.min_stok AS min_stok,
                                    barang.satuan as satuan 
                                    FROM barang JOIN kategori
                                    ON barang.id_kategori = kategori.id_kategori
                                    WHERE barang.stok < barang.min_stok AND barang.deleted_at is null;
        ");
        $stok_menipis = $query->getResultArray();
        $data = [
            "title" => "Stok Menipis",
            "active" => "stok-menipis",
            'stok_menipis' => $stok_menipis
        ];
        return view('Pages/stokmenipis', $data);
    }

    // details Barang
    public function details_barang($id)
    {
        $query = $this->db->query("SELECT barang.nama_barang as barang, 
                                    kategori.nama_kategori as kategori,
                                    barang.stok as stok, barang.min_stok,
                                    barang.satuan as satuan, barang.kondisi as kondisi
                                    from barang join kategori
                                        on barang.id_kategori = kategori.id_kategori
                                        where barang.id_barang='$id';
        ");
        $details = $query->getResultArray();
        $data = [
            'title' => 'Detail Data Barang',
            'detail' => $details
        ];
        return view('Pages/detail_barang', $data);
    }

    // details Barang Ke 2 (Manajemen Barang)
    public function details_barang2($id)
    {
        $query = $this->db->query("SELECT barang.nama_barang as barang,
        kategori.nama_kategori as kategori,
        pemasok.nama_pemasok as pemasok,
        konsumen.nama_konsumen as konsumen,
        barang.stok as stok,
        barang.min_stok as min_stok,
        barang.created_at as tanggal_masuk,
        barang.satuan as satuan,
        barang.kondisi as kondisi
    from barang
        join kategori on barang.id_kategori = kategori.id_kategori
        join pemasok on barang.id_pemasok = pemasok.id_pemasok
        join konsumen on barang.id_konsumen = konsumen.id_konsumen
    where barang.id_barang ='$id';
        ");
        $details = $query->getResultArray();
        $data = [
            'title' => 'Detail Data Barang',
            'detail' => $details
        ];
        return view('Pages/detail_barang2', $data);
    }

    // Edit Barang
    public function edit_barang($id)
    {
        $barang = $this->barangmodel->where('id_barang', $id)->findAll();
        $kategori = $this->kategorimodel->findAll();
        $data = [
            'title' => 'Edit Data Barang',
            'barang' => $barang,
            'kategori' => $kategori
        ];

        session()->setFlashdata('edit_success', 'Data Berhasil di Ubah');

        return view('Pages/edit_barang', $data);
    }

    public function save_edit_barang()
    {
        $id_barang = $this->request->getVar('id_barang');
        $data = [
            'nama_barang' => $this->request->getVar('nama'),
            'id_kategori' => $this->request->getVar('kategori'),
            'stok' => $this->request->getVar('stok'),
            'min_stok' => $this->request->getVar('min_stok'),
            'satuan' => $this->request->getVar('satuan'),
            'kondisi' => $this->request->getVar('kondisi'),
        ];
        $this->barangmodel->update($id_barang, $data);

        return redirect()->to('Pages/edit_barang/' . $id_barang);
    }

    // Edit Barang 2
    public function edit_barang2($id)
    {
        $barang = $this->barangmodel->where('id_barang', $id)->findAll();
        $kategori = $this->kategorimodel->findAll();
        $data = [
            'title' => 'Edit Data Barang',
            'barang' => $barang,
            'kategori' => $kategori
        ];

        return view('Pages/edit_barang2', $data);
    }

    public function save_edit_barang2()
    {
        $id_barang = $this->request->getVar('id_barang');
        $data = [
            'nama_barang' => $this->request->getVar('nama'),
            'id_kategori' => $this->request->getVar('kategori'),
            'stok' => $this->request->getVar('stok'),
            'min_stok' => $this->request->getVar('min_stok'),
            'satuan' => $this->request->getVar('satuan'),
            'kondisi' => $this->request->getVar('kondisi'),
        ];
        $this->barangmodel->update($id_barang, $data);
        return redirect()->to('/manajemenbarang');
    }

    public function hapus_barang($id)
    {
        $this->barangmodel->delete($id);
        return redirect()->to('/stokmenipis');
    }

    public function manajemenbarang()
    {
        $query = $this->db->query("SELECT barang.id_barang, 
                                        barang.nama_barang AS barang,
                                        kategori.nama_kategori AS kategori,
                                        barang.stok AS stok, barang.min_stok AS min_stok,
                                        barang.satuan as satuan 
                                        FROM barang JOIN kategori
                                        ON barang.id_kategori = kategori.id_kategori
                                        WHERE barang.deleted_at is null;
            ");
        $stok_menipis = $query->getResultArray();
        $data = [
            "title" => "Manajemen Barang",
            'stok_menipis' => $stok_menipis
        ];
        return view('Pages/manajemen_barang', $data);
    }

    // Tambah Barang
    public function tambahbarang()
    {
        $kategori = $this->kategorimodel->findAll();
        $konsumen = $this->konsumenmodel->findAll();
        $pemasok = $this->pemasokmodel->findAll();

        $data = [
            'title' => 'Tambah Data Barang',
            'kategori' => $kategori,
            'konsumen' => $konsumen,
            'pemasok' => $pemasok
        ];
        return view('Pages/tambah_barang', $data);
    }

    public function savebarang()
    {
        $barang = [
            'id_barang' => $this->uuid(),
            'id_konsumen' => $this->request->getVar('id_konsumen'),
            'id_pemasok' => $this->request->getVar('id_pemasok'),
            'id_kategori' => $this->request->getVar('id_kategori'),
            'nama_barang' => $this->request->getVar('nama_barang'),
            'stok' => $this->request->getVar('qty'),
            'min_stok' => $this->request->getVar('min_stok'),
            'satuan' => $this->request->getVar('satuan'),
            'kondisi' => $this->request->getVar('kondisi')
        ];

        $barangmasuk = [
            'id_barang_masuk' => $this->uuid(),
            'id_barang' => $barang['id_barang'],
            'qty' => $this->request->getVar('qty'),
            'no_faktur' => $this->request->getVar('no_faktur'),
            'id_kategori' => $this->request->getVar('id_kategori')
        ];




        $this->barangmasukmodel->insert($barangmasuk);
        $this->barangmodel->insert($barang);

        return redirect()->to('/manajemenbarang');
    }
    public function hapus_barang2($id)
    {
        $this->barangmodel->delete($id);
        return redirect()->to('/manajemenbarang');
    }
}
