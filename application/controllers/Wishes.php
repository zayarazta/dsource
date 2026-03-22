<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Wishes extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        // Memuat model, helper, dan library yang dibutuhkan
        $this->load->model('Wish_model');
        $this->load->helper(['url', 'form']);

        // Mengatur zona waktu agar sesuai dengan waktu lokal Indonesia
        date_default_timezone_set('Asia/Jakarta');
    }

    /**
     * Menampilkan halaman utama dengan daftar ucapan
     */
    public function index()
    {
        // Mengambil semua data ucapan dari model, urutkan berdasarkan yang terbaru
        $data['wishes'] = $this->Wish_model->get_all_wishes_json();

        // Memuat view wishes_view
        $this->load->view('wishes_json', $data);
    }

    /**
     * Menangani pengiriman ucapan baru melalui AJAX
     */
    public function submit()
    {
        // Pastikan request adalah POST
        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            show_error('Metode akses tidak diizinkan', 405);
            return;
        }

        $name = $this->input->post('name', TRUE); // TRUE untuk XSS filtering
        $message = $this->input->post('message', TRUE);

        // Validasi input sederhana
        if (!empty($name) && !empty($message)) {
            $data_insert = array(
                'name'       => $name,
                'message'    => $message,
                'created_at' => date('Y-m-d H:i:s')
            );

            // Simpan ke database melalui model
            $insert = $this->Wish_model->insert_wish_json($data_insert);

            if ($insert) {
                // Siapkan respon JSON sukses
                $response = array(
                    'status' => 'success',
                    'data'   => array(
                        'name'    => htmlspecialchars($name),
                        'message' => nl2br(htmlspecialchars($message)),
                        'time'    => date('d M Y, H:i')
                    ),
                    // Kirim balik token CSRF baru agar pengiriman berikutnya tidak error
                    'csrf_name' => $this->security->get_csrf_token_name(),
                    'csrf_hash' => $this->security->get_csrf_hash()
                );
            } else {
                $response = array(
                    'status'  => 'error',
                    'message' => 'Gagal menyimpan ke database'
                );
            }
        } else {
            $response = array(
                'status'  => 'error',
                'message' => 'Nama dan pesan tidak boleh kosong'
            );
        }

        // Set header content type ke JSON
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }
}
