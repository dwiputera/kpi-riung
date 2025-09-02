<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Testing extends MY_Controller
{
    public function tetris()
    {
        $this->load->view('tetris');
    }

    public function chess()
    {
        $this->load->view('chess');
    }

    public function index()
    {
        $datasets = [
            'users' => $this->db->get('rml_sso_la.users')->result_array(),
            // 'positions' => $this->db->get('org_area_lvl_pstn_user')->result_array(),
            // 'info' => 'Contoh string biasa',
            // 'count' => 12345,
            // 'nested' => [
            //     ['a' => 1, 'b' => ['x' => 10, 'y' => ['z' => 20]]],
            //     ['a' => 3, 'b' => ['x' => 30, 'y' => ['z' => 40]]],
            // ],
        ];

        // Fungsi rekursif untuk convert nested array jadi string rapi
        function nested_to_string($data, $level = 0)
        {
            if (!is_array($data)) return htmlspecialchars((string)$data);

            $indent = str_repeat('  ', $level);
            $out = "[\n";
            foreach ($data as $k => $v) {
                $key = is_int($k) ? $k : "'$k'";
                $out .= $indent . "  $key => " . nested_to_string($v, $level + 1) . ",\n";
            }
            $out .= $indent . "]";
            return $out;
        }

        // Fungsi untuk convert cell value ke string, pakai nested_to_string jika array
        function cell_to_str($val)
        {
            if (is_array($val)) {
                return "<pre style='white-space: pre-wrap; font-size: 11px; max-width: 300px;'>" . nested_to_string($val) . "</pre>";
            }
            return htmlspecialchars((string)$val);
        }

        echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Nested Debug</title>";
        echo "<link rel='stylesheet' href='https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css'>";
        echo "<style>
        table, th, td {
            border: 3px double black;
            padding: 3px 6px;
            border-collapse: separate;
            border-spacing: 4px;
            white-space: nowrap;
            font-size: 12px;
            margin: 20px auto;
            max-width: 900px;
            width: auto;
        }
        .simple-output {
            max-width: 900px;
            margin: 20px auto 40px;
            padding: 10px;
            border: 3px double black;
            font-family: monospace;
            white-space: pre-wrap;
            background: #f5f5f5;
        }
    </style></head><body>";

        $i = 1;
        foreach ($datasets as $name => $data) {
            echo "<h3>Data $i: $name</h3>";

            if (is_array($data) && count($data) > 0 && isset($data[0]) && is_array($data[0])) {
                // Array of arrays => tabel
                echo "<table id='table$i'><thead><tr>";
                foreach (array_keys($data[0]) as $header) {
                    echo "<th>$header</th>";
                }
                echo "</tr></thead><tbody>";

                foreach ($data as $row) {
                    echo "<tr>";
                    foreach ($row as $col) {
                        echo "<td>" . cell_to_str($col) . "</td>";
                    }
                    echo "</tr>";
                }

                echo "</tbody></table>";
                $i++;
            } else {
                // Scalar / array biasa
                if (is_array($data)) {
                    echo "<div class='simple-output'>" . json_encode($data, JSON_PRETTY_PRINT) . "</div>";
                } else {
                    echo "<div class='simple-output'>" . htmlspecialchars((string)$data) . "</div>";
                }
            }
        }

        if ($i > 1) {
            echo "<script src='https://code.jquery.com/jquery-3.7.0.min.js'></script>";
            echo "<script src='https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js'></script>";
            echo "<script>
            $(function() {";
            for ($j = 1; $j < $i; $j++) {
                echo "$('#table$j').DataTable({pageLength:25,lengthMenu:[10,25,50,100],order:[]});";
            }
            echo "});
        </script>";
        }

        echo "</body></html>";
        die;
    }
}
