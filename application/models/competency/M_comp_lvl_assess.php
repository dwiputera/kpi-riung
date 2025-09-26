<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_comp_lvl_assess extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->db->query("SET SESSION sql_mode = REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', '')");
    }

    public function get_comp_lvl_emp_assess($value = null, $by = 'md5(e.NRP)', $many = true)
    {
        // (Opsional) amankan $by bila mau terima kolom dinamis — saat ini dibiarkan seperti semula
        $where = '';
        if ($value) {
            // HATI-HATI: $by langsung disisipkan. Kalau sumbernya dari input user, sanitasi/whitelist kolom yang diperbolehkan.
            $where = "WHERE $by = " . $this->db->escape($value);
        }

        // 1) Set sesi agar GROUP_CONCAT tidak terpotong (pisahkan dari query utama)
        $this->db->simple_query("SET SESSION group_concat_max_len = 1048576");

        // 2) Query utama pakai HEREDOC (STRING, bukan backtick)
        $sql = <<<'SQL'
WITH RECURSIVE matrix_point_resolve AS (
  SELECT
    oalp.id AS start_id, oalp.id AS current_id, oalp.parent, oalp.matrix_point,
    oalp.name, oalp.type,
    CASE WHEN oalp.type = 'matrix_point' THEN oalp.name ELSE NULL END AS matrix_point_name,
    0 AS depth
  FROM org_area_lvl_pstn oalp
  UNION ALL
  SELECT
    m.start_id, o.id, o.parent, o.matrix_point, o.name, o.type,
    CASE WHEN o.type = 'matrix_point' THEN o.name ELSE m.matrix_point_name END AS matrix_point_name,
    m.depth + 1
  FROM matrix_point_resolve m
  JOIN org_area_lvl_pstn o
    ON o.id = m.parent OR o.id = m.matrix_point
  WHERE m.matrix_point_name IS NULL
),
final_matrix_point AS (
  SELECT start_id AS node_id, matrix_point_name
  FROM (
    SELECT
      start_id,
      matrix_point_name,
      ROW_NUMBER() OVER (PARTITION BY start_id ORDER BY depth ASC) AS rn
    FROM matrix_point_resolve
    WHERE matrix_point_name IS NOT NULL
  ) ranked
  WHERE rn = 1
)
SELECT
  e.NRP,
  MAX(e.FullName)                      AS FullName,
  cla.id                                AS comp_lvl_assess_id,
  MAX(cla.method_id)                    AS method_id,
  MAX(cla.tahun)                        AS tahun,
  MAX(cla.vendor)                       AS vendor,
  MAX(cla.recommendation)               AS recommendation,
  MAX(cla.score)                        AS assess_score,
  MAX(clam.name)                        AS method,
  MAX(oalp.id)                          AS oalp_id,
  MAX(oalp.name)                        AS oalp_name,
  MAX(oalp.parent)                      AS oalp_parent,
  MAX(oal.id)                           AS oal_id,
  MAX(oal.name)                         AS oal_name,
  MAX(oa.id)                            AS oa_id,
  MAX(oa.name)                          AS oa_name,
  MAX(fmp.matrix_point_name)            AS matrix_point_name,

  /* score: array JSON manual dari clas */
  CASE
    WHEN COUNT(clas.comp_lvl_id) = 0 THEN '[]'
    ELSE CONCAT(
      '[',
      GROUP_CONCAT(
        CONCAT(
          '{',
            '"score":', IFNULL(clas.score, 'null'), ',',
            '"comp_lvl_id":', IFNULL(clas.comp_lvl_id, 'null'),
          '}'
        )
        ORDER BY clas.comp_lvl_id
        SEPARATOR ','
      ),
      ']'
    )
  END AS score,

  ROUND(AVG(clas.score), 2) AS avg_score,

  /* pstn_scores: array JSON manual dari emp_ipa_score */
  (
    SELECT
      CASE
        WHEN COUNT(*) = 0 THEN '[]'
        ELSE CONCAT(
          '[',
          GROUP_CONCAT(
            CONCAT(
              '{',
                '"tahun":', IFNULL(eis.tahun, 'null'), ',',
                '"score":', IFNULL(eis.score, 'null'),
              '}'
            )
            ORDER BY eis.tahun
            SEPARATOR ','
          ),
          ']'
        )
      END
    FROM emp_ipa_score eis
    WHERE eis.NRP = e.NRP
  ) AS pstn_scores,

  (
    SELECT ROUND(AVG(eis.score), 2)
    FROM emp_ipa_score eis
    WHERE eis.NRP = e.NRP
  ) AS avg_ipa_score

FROM rml_sso_la.users e
LEFT JOIN comp_lvl_assess cla           ON cla.NRP = e.NRP
LEFT JOIN comp_lvl_assess_method clam   ON clam.id = cla.method_id
LEFT JOIN comp_lvl_assess_score clas    ON clas.comp_lvl_assess_id = cla.id
LEFT JOIN org_area_lvl_pstn_user oalpu  ON oalpu.NRP = e.NRP
LEFT JOIN org_area_lvl_pstn oalp        ON oalp.id = oalpu.area_lvl_pstn_id
LEFT JOIN org_area_lvl oal              ON oal.id = oalp.area_lvl_id
LEFT JOIN org_area oa                   ON oa.id = oalp.area_id
LEFT JOIN final_matrix_point fmp        ON fmp.node_id = oalp.id
/* {{WHERE}} */
GROUP BY e.NRP, cla.id
ORDER BY e.NRP, cla.id
SQL;

        // Sisipkan WHERE (kalau ada)
        if ($where) {
            $sql = str_replace('/* {{WHERE}} */', $where, $sql);
        } else {
            $sql = str_replace('/* {{WHERE}} */', '', $sql);
        }

        // Eksekusi
        $query = $this->db->query($sql);

        // Output handling seperti semula
        if (($value && !$many) || $many === false) {
            $row = $query->row_array();
            if (!$row) return false;
            $row['score']        = !empty($row['score']) ? json_decode($row['score'], true) : [];
            $row['pstn_scores']  = !empty($row['pstn_scores']) ? json_decode($row['pstn_scores'], true) : [];
            $row['avg_score']    = $row['avg_score'] !== null ? (float)$row['avg_score'] : null;
            $row['avg_ipa_score'] = $row['avg_ipa_score'] !== null ? (float)$row['avg_ipa_score'] : null;
            return $row;
        } else {
            $rows = $query->result_array();
            foreach ($rows as &$r) {
                $r['score']        = !empty($r['score']) ? json_decode($r['score'], true) : [];
                $r['pstn_scores']  = !empty($r['pstn_scores']) ? json_decode($r['pstn_scores'], true) : [];
                $r['avg_score']    = $r['avg_score'] !== null ? (float)$r['avg_score'] : null;
                $r['avg_ipa_score'] = $r['avg_ipa_score'] !== null ? (float)$r['avg_ipa_score'] : null;
            }
            return $rows;
        }
    }

    function submit($NRP_hash)
    {
        $submitted_data = json_decode($this->input->post('target_json'), true);
        $cla = $this->db->get_where('comp_lvl_assess', array('md5(NRP)' => $NRP_hash, 'method_id' => $this->input->post('method_id')))->row_array();
        $cla_data = [
            'NRP' => $this->input->post('NRP'),
            'score' => $submitted_data['assess_score'],
            'method_id' => $this->input->post('method_id'),
            'recommendation' => $submitted_data['recommendation'],
            'tahun' => $submitted_data['tahun'],
            'vendor' => $submitted_data['vendor'],
        ];

        if (!$cla) {
            $success = $this->db->insert('comp_lvl_assess', $cla_data);
            $cla_id = $this->db->insert_id();
        } else {
            $this->db->where('id', $cla['id']);
            $success = $this->db->update('comp_lvl_assess', $cla_data);
            $cla_id = $cla['id'];
        }

        if ($success) {
            $comp_lvls = $this->db->get('comp_lvl')->result_array();
            foreach ($comp_lvls as $i_cl => $cl_i) {
                $clas_data = [
                    'comp_lvl_assess_id' => $cla_id,
                    'comp_lvl_id' => $cl_i['id'],
                    'score' => $submitted_data['score'][$cl_i['id']],
                ];
                $clas = $this->db->get_where('comp_lvl_assess_score', ['comp_lvl_assess_id' => $cla_id, 'comp_lvl_id' => $cl_i['id']])->row_array();
                echo '<pre>', print_r($clas_data, true);
                if (!$clas) {
                    $success = $this->db->insert('comp_lvl_assess_score', $clas_data);
                    echo '<pre>', print_r("insert", true);
                } else {
                    $this->db->where('comp_lvl_assess_id', $cla_id);
                    $this->db->where('comp_lvl_id', $cl_i['id']);
                    $success = $this->db->update('comp_lvl_assess_score', $clas_data);
                    echo '<pre>', print_r("update", true);
                }
                echo '<pre>', print_r($success, true);
            }
        }
        return $success;
    }
}
