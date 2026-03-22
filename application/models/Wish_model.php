<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Wish_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    // Ambil semua data ucapan, urutkan dari yang terbaru
    public function get_all_wishes()
    {
        $this->db->order_by('created_at', 'DESC');
        $query = $this->db->get('wishes');
        return $query->result_array();
    }

    // Simpan ucapan baru
    public function insert_wish($data)
    {
        return $this->db->insert('wishes', $data);
    }

    public function get_all_wishesxml()
    {
        $file = FCPATH . 'application/data/wishes.xml';
        if (!file_exists($file)) return [];

        $xml = simplexml_load_file($file);
        $items = [];
        foreach ($xml->wish as $item) {
            $items[] = $item;
        }
        // Urutkan dari yang terbaru (reverse)
        return array_reverse($items);
    }

    public function insert_wishxml($data)
    {
        $file = FCPATH . 'application/data/wishes.xml';
        $xml = file_exists($file) ? simplexml_load_file($file) : new SimpleXMLElement('<wishes/>');

        $wish = $xml->addChild('wish');
        $wish->addChild('name', htmlspecialchars($data['name']));
        $wish->addChild('message', htmlspecialchars($data['message']));
        $wish->addChild('created_at', $data['created_at']);

        return $xml->asXML($file);
    }


    /**
     * Mengambil semua ucapan dari file JSON.
     * Karena kita menggunakan array_unshift saat menyimpan, 
     * data terbaru sudah otomatis berada di urutan teratas.
     */
    public function get_all_wishes_json()
    {
        $file_path = FCPATH . 'application/data/wishes.json';
        if (!file_exists($file_path)) {
            return [];
        }

        $json_content = file_get_contents($file_path);
        $data = json_decode($json_content, true);

        if (!is_array($data)) {
            return [];
        }

        // Kita hanya melakukan usort jika data tidak sengaja berantakan.
        // Jika performa berat, bagian ini bisa dihapus karena array_unshift 
        // di insert_wish sudah menjamin data terbaru ada di paling atas index [0].
        usort($data, function ($a, $b) {
            $dateA = isset($a['created_at']) ? strtotime($a['created_at']) : 0;
            $dateB = isset($b['created_at']) ? strtotime($b['created_at']) : 0;

            if ($dateA == $dateB) return 0;
            return ($dateB < $dateA) ? -1 : 1;
        });

        return $data;
    }

    public function insert_wish_json($data)
    {
        $file_path = FCPATH . 'application/data/wishes.json';
        $directory = dirname($file_path);

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $current_data = [];
        if (file_exists($file_path)) {
            $json_content = file_get_contents($file_path);
            if ($json_content !== false) {
                $current_data = json_decode($json_content, true);
                if (!is_array($current_data)) {
                    $current_data = [];
                }
            }
        }

        $new_entry = [
            'name'       => isset($data['name']) ? $data['name'] : 'Anonim',
            'message'    => isset($data['message']) ? $data['message'] : '',
            'created_at' => date('Y-m-d H:i:s')
        ];

        // Memasukkan ke index paling depan
        array_unshift($current_data, $new_entry);

        // Simpan kembali
        $result = file_put_contents($file_path, json_encode($current_data, JSON_PRETTY_PRINT), LOCK_EX);

        return $result;
    }
}
