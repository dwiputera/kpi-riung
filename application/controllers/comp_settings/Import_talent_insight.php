<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Import_talent_insight extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Contoh endpoint untuk test hasil parsing
     * http://localhost/kpi/level_score/read_talent_insight
     */
    public function read_talent_insight()
    {
        $file = APPPATH . 'views/competency/talent_insight.html';

        // Mengambil data dan mengubahnya menjadi array associative
        $rows = $this->parse_html_table_to_assoc($file);

        // Menyiapkan hasil JSON
        $talent_insight = [
            'count' => count($rows),
            'data'  => $rows
        ];

        foreach ($rows as $i_row => $row_i) {
            echo '<pre>', print_r($row_i['nrp'], true);
            $data = [];
            $cla = $this->db->query("
                SELECT * FROM comp_lvl_assess
                WHERE NRP = '$row_i[nrp]'
                AND tahun = $row_i[assessment_year]
            ")->result_array();
            if (count($cla) < 1) {
                $data = [
                    'NRP' => $row_i['nrp'],
                    'method_id' => 1,
                    'vendor' => $row_i['vendor'],
                    'tahun' => $row_i['assessment_year'],
                    'recommendation' => $row_i['recommendation'],
                    'assessment_insight_strength' => $row_i['assesment_insight_area_kekuatan'],
                    'assessment_insight_development' => $row_i['assessment_insight_area_pengembangan'],
                    'talent_insight' => $row_i['talent_insight_rev_01'],
                ];
                $this->db->insert('comp_lvl_assess', $data);
            } else {
                $data = [
                    'NRP' => $row_i['nrp'],
                    'method_id' => 1,
                    'vendor' => $row_i['vendor'],
                    'tahun' => $row_i['assessment_year'],
                    'recommendation' => $row_i['recommendation'],
                    'assessment_insight_strength' => $row_i['assesment_insight_area_kekuatan'],
                    'assessment_insight_development' => $row_i['assessment_insight_area_pengembangan'],
                    'talent_insight' => $row_i['talent_insight_rev_01'],
                ];
                $this->db->where('id', $cla[0]['id'])->update('comp_lvl_assess', $data);
            }
        }
        die;
    }

    /**
     * Parse HTML table -> associative array.
     * - header row (<tr> pertama) jadi keys
     * - baris berikutnya jadi data rows
     */
    private function parse_html_table_to_assoc($filePath)
    {
        if (!is_file($filePath)) {
            show_error("File tidak ditemukan: " . $filePath, 404);
        }

        $html = file_get_contents($filePath);
        if ($html === false) {
            show_error("Gagal membaca file: " . $filePath, 500);
        }

        // Rapihin: pastikan ada wrapper HTML minimal (DOMDocument lebih stabil)
        $wrapped = '<!doctype html><html><head><meta charset="utf-8"></head><body>' . $html . '</body></html>';

        libxml_use_internal_errors(true);

        $dom = new DOMDocument();
        // Konversi encoding supaya karakter non-ascii aman
        $dom->loadHTML(mb_convert_encoding($wrapped, 'HTML-ENTITIES', 'UTF-8'), LIBXML_NOWARNING | LIBXML_NOERROR);

        libxml_clear_errors();

        $xpath = new DOMXPath($dom);

        // Ambil table pertama (kalau HTML kamu cuma punya 1 table)
        $table = $xpath->query('//table')->item(0);
        if (!$table) {
            return [];
        }

        $trs = $xpath->query('.//tr', $table);
        if ($trs->length < 2) {
            return [];
        }

        // --- Header (tr pertama) ---
        $headerTr = $trs->item(0);
        $headerTds = $xpath->query('./td|./th', $headerTr);

        $headers = [];
        foreach ($headerTds as $cell) {
            $headers[] = $this->normalize_header($this->header_text($cell));
        }

        // --- Data rows ---
        $result = [];
        for ($i = 1; $i < $trs->length; $i++) {
            $tr = $trs->item($i);
            $cells = $xpath->query('./td|./th', $tr);

            if ($cells->length === 0) continue;

            $row = [];
            for ($c = 0; $c < $cells->length; $c++) {
                $key = $headers[$c] ?? ('col_' . $c);
                $row[$key] = $this->cell_text($cells->item($c));
            }

            // skip baris kosong total
            if ($this->row_is_empty($row)) continue;

            $result[] = $row;
        }

        return $result;
    }

    /**
     * Ambil text isi cell:
     * - <br> jadi newline
     * - decode entity (&amp; -> &)
     * - trim & rapihin whitespace
     */
    private function header_text(DOMNode $cell)
    {
        $html = '';

        foreach ($cell->childNodes as $child) {
            if ($child->nodeName === 'br') {
                $html .= "\n";
            } else {
                $html .= $cell->ownerDocument->saveHTML($child);
            }
        }

        // strip tags, decode entity
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // rapihin whitespace (biar tidak banyak spasi aneh)
        $text = str_replace("\xC2\xA0", ' ', $text); // &nbsp;
        $text = preg_replace("/[ \t]+/", ' ', $text);
        $text = preg_replace("/\n{3,}/", "\n\n", $text);

        return trim($text);
    }

    private function cell_text(DOMNode $cell)
    {
        $html = '';

        // Loop through all child nodes of the cell
        foreach ($cell->childNodes as $child) {
            if ($child->nodeName === 'br') {
                // Preserve <br> tags as line breaks
                $html .= "<br>";
            } else {
                // Keep all other HTML content intact
                $html .= $cell->ownerDocument->saveHTML($child);
            }
        }

        // If you want to keep all HTML entities and tags intact, just return the HTML as it is.
        return $html;
    }

    /**
     * Normalisasi header biar enak dipakai sebagai key
     * contoh:
     * "FULL NAME" -> "full_name"
     * "ASSESSMENT YEAR" -> "assessment_year"
     * "ASSESMENT INSIGHT(AREA KEKUATAN)" -> "assesment_insight_area_kekuatan"
     */
    private function normalize_header($header)
    {
        $h = strtolower($header);
        $h = preg_replace('/[^a-z0-9]+/i', '_', $h);
        $h = trim($h, '_');

        // kalau ada header kosong, kasih default
        return $h !== '' ? $h : 'col';
    }

    private function row_is_empty(array $row)
    {
        foreach ($row as $v) {
            if (trim((string)$v) !== '') return false;
        }
        return true;
    }
}
