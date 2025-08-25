<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Correlation_matrix extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('competency/m_comp_position', 'm_c_pstn');
        $this->load->model('organization/m_position', 'm_pstn');
    }

    public function index()
    {
        $data['correlation_matrix'] = $this->m_c_pstn->get_correlation_matrix();
        $data['content'] = "competency/correlation_matrix";
        $this->load->view('templates/header_footer', $data);
    }

    public function matrix()
    {
        // Ambil data
        $matrix_points = $this->db->get_where('org_area_lvl_pstn', ['type' => 'matrix_point'])->result_array();
        $comp_pstns     = $this->db->get('comp_position')->result_array();

        // Map: area_lvl_pstn_id -> [nama posisi...]
        $comp_pstns_mp = [];
        foreach ($matrix_points as $mp) {
            $list = array_filter($comp_pstns, fn($cp) => $cp['area_lvl_pstn_id'] == $mp['id']);
            $comp_pstns_mp[$mp['id']] = array_values(array_column($list, 'name')); // pastikan reindex
        }
        unset($mp, $list);

        // Bangun correlation matrix (persen overlap terhadap baris)
        $correlation_matrix = $matrix_points; // copy nilai dasar
        foreach ($correlation_matrix as &$cm) {
            $rowId = $cm['id'];
            $rowList = $comp_pstns_mp[$rowId] ?? [];
            $rowCount = count($rowList);

            // siapkan array correlations
            $cm['correlations'] = [];

            foreach ($matrix_points as $mp) { // tidak perlu by-ref
                $colId = $mp['id'];
                $colList = $comp_pstns_mp[$colId] ?? [];

                // hitung irisan
                $common = count(array_intersect($rowList, $colList));

                // hitung persen relatif thd baris (hindari /0)
                $pct = 0;
                if ($rowCount > 0 && $common > 0) {
                    $pct = $common / $rowCount * 100;
                }

                // simpan 2 desimal, titik sebagai decimal separator
                $cm['correlations'][$colId] = number_format($pct, 2, '.', '');
            }
        }
        unset($cm);

        // Render tabel
        echo "<table border='1' cellspacing='0' cellpadding='6'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>korelasi</th>";
        foreach ($matrix_points as $mp) {
            echo "<th>" . htmlspecialchars($mp['name'] ?? '', ENT_QUOTES, 'UTF-8') . "</th>";
        }
        echo "</tr>";
        echo "</thead>";

        echo "<tbody>";
        foreach ($correlation_matrix as $cm) {
            echo "<tr>";
            echo "<td><strong>" . htmlspecialchars($cm['name'] ?? '', ENT_QUOTES, 'UTF-8') . "</strong></td>";
            foreach ($matrix_points as $mp) {
                $val = $cm['correlations'][$mp['id']] ?? '0.00';
                echo "<td style='text-align:right'>" . $val . "</td>";
            }
            echo "</tr>";
        }
        echo "</tbody>";
        echo "</table>";
    }
}
