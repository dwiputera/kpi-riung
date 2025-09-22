<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Quiz_admin extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Quiz_multi_model', 'quiz');
        $this->load->helper(['url', 'form']);
        $this->load->library('session');
    }

    /* ===== PAGES (ADMIN) ===== */
    public function index()
    {
        $host_nrp = $this->session->userdata('NRP');
        $data['my_quizzes'] = $host_nrp ? $this->quiz->list_quiz_by_host($host_nrp, 200) : [];
        $data['content'] = 'quiz_admin/index_content'; // dashboard admin (daftar quiz + modal buat baru)
        $this->load->view('templates/header_footer', $data);
    }

    public function host($quiz_id = null)
    {
        if ($quiz_id) {
            $data['quiz'] = $this->quiz->get_quiz((int)$quiz_id);
            $q = $data['quiz'];
            if ($q && $q['host_nrp'] === $this->session->userdata('NRP')) {
                $this->session->set_userdata('host_quiz_id', (int)$quiz_id);
            } else {
                $this->session->unset_userdata('host_quiz_id');
            }
        }
        $data['content'] = 'quiz_admin/host_content';
        $this->load->view('templates/header_footer', $data);
    }

    public function builder($quiz_id)
    {
        $quiz = $this->quiz->get_quiz((int)$quiz_id);
        if (!$quiz) {
            show_404();
            return;
        }
        if ($quiz['host_nrp'] !== $this->session->userdata('NRP')) {
            show_error('Forbidden', 403);
            return;
        }

        $this->session->set_userdata('host_quiz_id', (int)$quiz_id);
        $data['quiz']    = $quiz;
        $data['content'] = 'quiz_admin/builder_content';
        $this->load->view('templates/header_footer', $data);
    }

    public function leaderboard($id_or_hash)
    {
        if (ctype_digit((string)$id_or_hash)) {
            $quiz = $this->quiz->get_quiz((int)$id_or_hash);
        } elseif (ctype_xdigit((string)$id_or_hash) && strlen($id_or_hash) === 32) {
            $quiz = $this->quiz->get_quiz_by_md5($id_or_hash);
        } else {
            show_404();
            return;
        }

        if (!$quiz) {
            show_404();
            return;
        }

        $data['quiz']    = $quiz;
        $data['leaders'] = $this->quiz->leaderboard((int)$quiz['id'], 50);
        $data['content'] = 'quiz/leaderboard_content';
        $this->load->view('templates/header_footer', $data);
    }

    /* ===== JSON helper ===== */
    private function json($data, $code = 200)
    {
        $this->output->set_status_header($code)
            ->set_content_type('application/json')
            ->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0')
            ->set_output(json_encode($data));
    }

    /* ===== ADMIN APIS ===== */
    public function api_quiz_create()
    {
        if ($this->input->method(TRUE) !== 'POST') return $this->json(['ok' => false, 'msg' => 'Method not allowed'], 405);

        $host_nrp = $this->session->userdata('NRP');
        if (!$host_nrp) return $this->json(['ok' => false, 'msg' => 'Host not authenticated'], 403);

        $title = trim((string)$this->input->post('title'));
        if ($title === '') return $this->json(['ok' => false, 'msg' => 'Judul wajib diisi'], 400);
        if (mb_strlen($title) > 200) $title = mb_substr($title, 0, 200);

        $quiz_id = $this->quiz->create_quiz($host_nrp, null, $title); // pin NULL, is_active=0
        $this->session->set_userdata('host_quiz_id', $quiz_id);
        return $this->json(['ok' => true, 'quiz_id' => (int)$quiz_id]);
    }

    public function api_generate_pin()
    {
        if ($this->input->method(TRUE) !== 'POST') {
            return $this->json(['ok' => false, 'msg' => 'Method not allowed'], 405);
        }

        $quiz_id = (int)$this->session->userdata('host_quiz_id');
        if (!$quiz_id) return $this->json(['ok' => false, 'msg' => 'No active host quiz'], 400);

        // pastikan quiz milik host
        $quiz = $this->quiz->get_quiz($quiz_id);
        if (!$quiz || $quiz['host_nrp'] !== $this->session->userdata('NRP')) {
            return $this->json(['ok' => false, 'msg' => 'Forbidden'], 403);
        }

        // generate hingga unik (dengan UNIQUE index pada pin)
        $maxTry = 10;
        $pin = null;
        for ($i = 0; $i < $maxTry; $i++) {
            $try = str_pad((string)mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);

            // cek cepat
            $exists = $this->db->select('id')->get_where('quiz', ['pin' => $try])->row_array();
            if ($exists) continue;

            // commit ke DB (tangkap kemungkinan race)
            $this->db->trans_start();
            $this->db->where('id', $quiz_id)->update('quiz', [
                'pin'       => $try,
                'is_active' => 1,
                'ended_at'  => NULL
            ]);
            $err = $this->db->error(); // ['code'=>..., 'message'=>...]
            $this->db->trans_complete();

            if ($this->db->trans_status() && empty($err['code'])) {
                $pin = $try;
                break;
            }
            // kalau DUPLICATE KEY karena race, loop lanjut
        }

        if (!$pin) return $this->json(['ok' => false, 'msg' => 'Gagal generate PIN'], 500);

        return $this->json(['ok' => true, 'pin' => $pin]);
    }

    public function api_leaderboard($quiz_id)
    {
        return $this->json(['ok' => true, 'rows' => $this->quiz->leaderboard((int)$quiz_id, 100)]);
    }

    public function api_start()
    {
        if ($this->input->method(TRUE) !== 'POST') return $this->json(['ok' => false, 'msg' => 'Method not allowed'], 405);
        $quiz_id = (int)$this->session->userdata('host_quiz_id');
        if (!$quiz_id) return $this->json(['ok' => false, 'msg' => 'No active host quiz'], 400);

        $first = $this->quiz->get_first_question_id_by_quiz($quiz_id);
        if (!$first) return $this->json(['ok' => false, 'msg' => 'Belum ada soal'], 400);

        $this->quiz->set_status($quiz_id, [
            'is_active' => 1,
            'current_question' => $first,
            'question_started_at' => date('Y-m-d H:i:s')
        ]);
        return $this->json(['ok' => true, 'quiz_id' => $quiz_id, 'current_question' => $first]);
    }

    // controllers/Quiz_admin.php
    public function api_next()
    {
        if ($this->input->method(TRUE) !== 'POST') return $this->json(['ok' => false, 'msg' => 'Method not allowed'], 405);
        $quiz_id = (int)$this->session->userdata('host_quiz_id');
        if (!$quiz_id) return $this->json(['ok' => false, 'msg' => 'No active host quiz'], 400);

        $quiz = $this->quiz->get_quiz($quiz_id);
        if (!$quiz || !$quiz['is_active']) return $this->json(['ok' => false, 'msg' => 'Quiz not active'], 409);

        $next = $this->quiz->get_next_question_id_by_quiz($quiz_id, $quiz['current_question']);
        if (!$next) {
            $this->quiz->set_status($quiz_id, [
                'is_active'            => 0,
                'current_question'     => NULL,
                'question_started_at'  => NULL,
                'ended_at'             => date('Y-m-d H:i:s'),
                'pin'                  => NULL,   // <- clear PIN saat habis soal
            ]);
            return $this->json(['ok' => true, 'ended' => true]);
        }

        $this->quiz->set_status($quiz_id, [
            'current_question'     => $next,
            'question_started_at'  => date('Y-m-d H:i:s')
        ]);
        return $this->json(['ok' => true, 'current_question' => $next]);
    }

    public function api_end()
    {
        if ($this->input->method(TRUE) !== 'POST') return $this->json(['ok' => false, 'msg' => 'Method not allowed'], 405);
        $quiz_id = (int)$this->session->userdata('host_quiz_id');
        if (!$quiz_id) return $this->json(['ok' => false, 'msg' => 'No active host quiz'], 400);
        $this->quiz->set_status($quiz_id, [
            'is_active' => 0,
            'current_question'    => NULL,
            'question_started_at' => NULL,
            'ended_at'            => date('Y-m-d H:i:s'),
            'pin'                 => NULL,   // <-- clear PIN saat selesai
        ]);
        return $this->json(['ok' => true]);
    }

    public function api_reset()
    {
        if ($this->input->method(TRUE) !== 'POST') return $this->json(['ok' => false, 'msg' => 'Method not allowed'], 405);
        $quiz_id = (int)$this->session->userdata('host_quiz_id');
        if (!$quiz_id) return $this->json(['ok' => false, 'msg' => 'No active host quiz'], 400);

        $this->db->trans_start();
        $this->db->where('quiz_id', $quiz_id)->delete('quiz_answer');
        $this->db->where('quiz_id', $quiz_id)->update('quiz_players', ['score' => 0]);
        $this->quiz->set_status($quiz_id, ['current_question' => NULL, 'question_started_at' => NULL, 'is_active' => 1, 'ended_at' => NULL]);
        $this->db->trans_complete();

        return $this->json(['ok' => $this->db->trans_status()]);
    }

    public function api_questions_list($quiz_id)
    {
        $quiz = $this->quiz->get_quiz((int)$quiz_id);
        if (!$quiz) return $this->json(['ok' => false, 'msg' => 'Quiz not found'], 404);
        if ($quiz['host_nrp'] !== $this->session->userdata('NRP')) return $this->json(['ok' => false, 'msg' => 'Forbidden'], 403);

        $rows = $this->db->order_by('id', 'ASC')->get_where('quiz_question', ['quiz_id' => (int)$quiz_id])->result_array();
        return $this->json(['ok' => true, 'rows' => $rows]);
    }

    public function api_questions_save()
    {
        if ($this->input->method(TRUE) !== 'POST') return $this->json(['ok' => false, 'msg' => 'Method not allowed'], 405);

        $payload = json_decode($this->input->raw_input_stream, true);
        if (!is_array($payload)) return $this->json(['ok' => false, 'msg' => 'Invalid JSON'], 400);

        $quiz_id = (int)($payload['quiz_id'] ?? 0);
        if (!$quiz_id) return $this->json(['ok' => false, 'msg' => 'quiz_id required'], 400);

        $quiz = $this->quiz->get_quiz($quiz_id);
        if (!$quiz) return $this->json(['ok' => false, 'msg' => 'Quiz not found'], 404);
        if ($quiz['host_nrp'] !== $this->session->userdata('NRP')) return $this->json(['ok' => false, 'msg' => 'Forbidden'], 403);

        $whitelist = ['question', 'option_a', 'option_b', 'option_c', 'option_d', 'answer', 'time_limit'];
        $sanitize = function (array $row) use ($whitelist) {
            $out = [];
            foreach ($whitelist as $f) {
                if (array_key_exists($f, $row)) $out[$f] = is_string($row[$f]) ? trim((string)$row[$f]) : $row[$f];
            }
            $out['answer'] = strtoupper(trim((string)($out['answer'] ?? '')));
            $out['time_limit'] = max(1, (int)($out['time_limit'] ?? 15));
            return $out;
        };

        $validIds = array_map(
            'intval',
            array_column(
                $this->db->select('id')->get_where('quiz_question', ['quiz_id' => $quiz_id])->result_array(),
                'id'
            )
        );

        $updates = $payload['updates'] ?? [];
        $deletes = $payload['deletes'] ?? [];
        $creates = $payload['creates'] ?? [];

        if (empty($updates) && empty($deletes) && empty($creates) && !empty($payload['items']) && is_array($payload['items'])) {
            foreach ($payload['items'] as $it) {
                $id = $it['id'] ?? 0;
                $isDeleted = !empty($it['_delete']);
                if ($isDeleted) {
                    if (is_numeric($id)) $deletes[] = (int)$id;
                    continue;
                }
                $row = $sanitize($it);
                if (($row['question'] ?? '') !== '' && in_array(($row['answer'] ?? ''), ['A', 'B', 'C', 'D'], true)) {
                    if (is_numeric($id) && (int)$id > 0) {
                        $row['id'] = (int)$id;
                        $updates[] = $row;
                    } else {
                        $creates[] = $row;
                    }
                }
            }
        }

        $upd = [];
        foreach ($updates as $row) {
            $id = isset($row['id']) ? (int)$row['id'] : 0;
            if ($id <= 0 || !in_array($id, $validIds, true)) continue;
            $clean = $sanitize($row);
            if (($clean['question'] ?? '') === '' || !in_array(($clean['answer'] ?? ''), ['A', 'B', 'C', 'D'], true)) continue;
            $clean['id'] = $id;
            $upd[] = $clean;
        }

        $delIds = array_values(array_intersect(
            array_map('intval', array_filter($deletes, 'is_numeric')),
            $validIds
        ));

        $ins = [];
        foreach ($creates as $row) {
            $clean = $sanitize($row);
            if (($clean['question'] ?? '') === '' || !in_array(($clean['answer'] ?? ''), ['A', 'B', 'C', 'D'], true)) continue;
            $clean['quiz_id'] = $quiz_id;
            $ins[] = $clean;
        }

        $this->db->trans_start();
        $affected = 0;

        if (!empty($upd)) {
            $this->db->update_batch('quiz_question', $upd, 'id');
            $affected += $this->db->affected_rows();
        }
        if (!empty($delIds)) {
            $this->db->where('quiz_id', $quiz_id)->where_in('id', $delIds)->delete('quiz_question');
            $affected += $this->db->affected_rows();
        }
        if (!empty($ins)) {
            $this->db->insert_batch('quiz_question', $ins);
            $affected += $this->db->affected_rows();
        }

        $this->db->trans_complete();

        return $this->json(['ok' => $this->db->trans_status(), 'affected' => $affected]);
    }

    public function api_host_state()
    {
        $quiz_id = (int)$this->session->userdata('host_quiz_id');
        if (!$quiz_id) return $this->json(['ok' => true, 'quiz_id' => null, 'pin' => null]);
        $q = $this->quiz->get_quiz($quiz_id);
        return $this->json(['ok' => true, 'quiz_id' => $quiz_id, 'pin' => $q['pin'] ?? null, 'title' => $q['title'] ?? null]);
    }

    public function api_quiz_update_title()
    {
        if ($this->input->method(TRUE) !== 'POST') return $this->json(['ok' => false, 'msg' => 'Method not allowed'], 405);
        $quiz_id = (int)$this->input->post('quiz_id');
        $title   = trim((string)$this->input->post('title'));
        if (!$quiz_id || $title === '') return $this->json(['ok' => false, 'msg' => 'Invalid payload'], 400);

        $quiz = $this->quiz->get_quiz($quiz_id);
        if (!$quiz) return $this->json(['ok' => false, 'msg' => 'Quiz not found'], 404);
        if ($quiz['host_nrp'] !== $this->session->userdata('NRP')) return $this->json(['ok' => false, 'msg' => 'Forbidden'], 403);

        $title = mb_substr($title, 0, 200);
        $this->db->where('id', $quiz_id)->update('quiz', ['title' => $title]);
        return $this->json(['ok' => true, 'title' => $title]);
    }

    public function api_quiz_delete()
    {
        if ($this->input->method(TRUE) !== 'POST') return $this->json(['ok' => false, 'msg' => 'Method not allowed'], 405);

        $quiz_id = (int)$this->input->post('quiz_id');
        if (!$quiz_id) return $this->json(['ok' => false, 'msg' => 'Invalid quiz_id'], 400);

        $quiz = $this->quiz->get_quiz($quiz_id);
        if (!$quiz) return $this->json(['ok' => false, 'msg' => 'Quiz not found'], 404);
        if ($quiz['host_nrp'] !== $this->session->userdata('NRP')) return $this->json(['ok' => false, 'msg' => 'Forbidden'], 403);

        $this->db->trans_start();
        $this->db->where('quiz_id', $quiz_id)->delete('quiz_question');
        $this->db->where('quiz_id', $quiz_id)->delete('quiz_answer');
        $this->db->where('quiz_id', $quiz_id)->delete('quiz_players');
        $this->db->where('id', $quiz_id)->delete('quiz');
        $this->db->trans_complete();

        return $this->json(['ok' => $this->db->trans_status()]);
    }

    public function api_focus_stats()
    {
        if ($this->input->method(TRUE) !== 'GET') {
            return $this->json(['ok' => false, 'msg' => 'Method not allowed'], 405);
        }

        $quiz_id = (int)$this->session->userdata('host_quiz_id');
        if (!$quiz_id) $quiz_id = (int)$this->input->get('quiz_id');
        if (!$quiz_id) return $this->json(['ok' => true, 'rows' => []]);

        $this->db->from('quiz_players');
        $this->db->where('quiz_id', $quiz_id);

        // kolom dasar (aman di-escape)
        $this->db->select('name, nrp');

        // blur_count
        if ($this->db->field_exists('blur_count', 'quiz_players')) {
            $this->db->select('blur_count');
        } else {
            $this->db->select('0 AS blur_count', FALSE);
        }

        // focus_count
        if ($this->db->field_exists('focus_count', 'quiz_players')) {
            $this->db->select('focus_count');
        } else {
            $this->db->select('0 AS focus_count', FALSE);
        }

        // last_heartbeat (prefer last_seen_at)
        if ($this->db->field_exists('last_seen_at', 'quiz_players')) {
            $this->db->select('last_seen_at AS last_heartbeat', FALSE);
        } elseif ($this->db->field_exists('last_heartbeat', 'quiz_players')) {
            $this->db->select('last_heartbeat');
        } else {
            $this->db->select('NULL AS last_heartbeat', FALSE);
        }

        // suspicious
        if ($this->db->field_exists('suspicious', 'quiz_players')) {
            $this->db->select('suspicious');
        } else {
            $this->db->select('0 AS suspicious', FALSE);
        }

        $rows = $this->db
            ->order_by('blur_count', 'DESC')
            ->order_by('focus_count', 'DESC')
            ->order_by('name', 'ASC')
            ->get()->result_array();

        return $this->json(['ok' => true, 'rows' => $rows]);
    }
}
