<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('apply_where')) {
    /**
     * Apply flexible where clause to raw SQL or query builder.
     *
     * @param CI_DB_query_builder|CI_DB_driver|null $db
     * @param array $conditions
     * @param bool $use_builder If true, apply to builder. If false, return raw SQL clause.
     * @return string|null
     */
    function apply_where($conditions = [], $db = null, $use_builder = true)
    {
        if (!$db) {
            $CI = &get_instance();
            $db = $CI->db;
        }

        $wheres = [];

        foreach ($conditions as $key => $val) {
            if (is_array($val)) {
                [$op, $v] = $val + [null, null];
                $op = strtolower($op);

                if ($use_builder) {
                    switch ($op) {
                        case 'in':
                            $db->where_in($key, $v);
                            break;
                        case 'not_in':
                            $db->where_not_in($key, $v);
                            break;
                        case 'is_null':
                            $db->where("$key IS NULL", null, false);
                            break;
                        case 'is_not_null':
                            $db->where("$key IS NOT NULL", null, false);
                            break;
                        case 'like':
                            $db->like($key, $v);
                            break;
                        case 'not_like':
                            $db->not_like($key, $v);
                            break;
                        default:
                            $db->where("$key $op", $v, false);
                    }
                } else {
                    switch ($op) {
                        case 'in':
                            $escaped = array_map([$db, 'escape'], $v);
                            $wheres[] = "$key IN (" . implode(',', $escaped) . ")";
                            break;
                        case 'not_in':
                            $escaped = array_map([$db, 'escape'], $v);
                            $wheres[] = "$key NOT IN (" . implode(',', $escaped) . ")";
                            break;
                        case 'is_null':
                            $wheres[] = "$key IS NULL";
                            break;
                        case 'is_not_null':
                            $wheres[] = "$key IS NOT NULL";
                            break;
                        case 'like':
                            $wheres[] = "$key LIKE " . $db->escape($v);
                            break;
                        case 'not_like':
                            $wheres[] = "$key NOT LIKE " . $db->escape($v);
                            break;
                        default:
                            $wheres[] = "$key $op " . $db->escape($v);
                    }
                }
            } else {
                if ($use_builder) {
                    $db->where($key, $val);
                } else {
                    $wheres[] = "$key = " . $db->escape($val);
                }
            }
        }

        return $use_builder ? null : (count($wheres) ? 'WHERE ' . implode(' AND ', $wheres) : '');
    }
}

// $this->db->select('*')->from('table_name');
// apply_where([
//     'status' => 'active',
//     'created_at' => ['>=', '2024-01-01'],
//     'deleted_at' => ['is_null'],
// ], $this->db, true);

// return $this->db->get()->result_array();

// ------------------------------------------------------------------------------------

// $where = apply_where([
//     'status' => 'active',
//     'category_id' => ['in', [1, 2, 3]],
//     'deleted_at' => ['is_not_null']
// ], $this->db, false);

// $query = $this->db->query("SELECT * FROM products $where");
// return $query->result_array();
