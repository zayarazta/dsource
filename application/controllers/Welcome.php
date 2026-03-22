<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Welcome extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		// Load model dan helper yang dibutuhkan
		$this->load->model('Wish_model');
		$this->load->helper('url');

		// Mengatur zona waktu agar sesuai dengan waktu lokal Indonesia
		date_default_timezone_set('Asia/Jakarta');
	}

	// Menampilkan halaman utama
	public function index()
	{
		$data['wishes'] = $this->Wish_model->get_all_wishes_json();
		$data['tamu']	= 'Tamu';

		$this->load->view('undangan', $data);
	}

	public function yr()
	{
		$data['title']	= '';
		$data['tamu']	= $_GET['untuk'];
		$data['wishes'] = $this->Wish_model->get_all_wishes_json();

		$this->load->view('undangan', $data);
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

		$name = $this->input->post('wc_name', TRUE); // TRUE untuk XSS filtering
		$message = $this->input->post('wc_comment', TRUE);

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
