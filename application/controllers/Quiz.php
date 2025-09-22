<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Quiz extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Quiz_multi_model', 'quiz');
        $this->load->helper(['url', 'form']);
        $this->load->library('session');
    }

    /* ===== PAGES (PLAYER) ===== */
    public function index()
    {
        // Halaman join saja (tanpa dashboard admin)
        $data['content'] = 'quiz/index_content'; // view join-only
        $this->load->view('templates/header_footer', $data);
    }

    public function play()
    {
        if (!$this->session->userdata('quiz_id')) redirect('quiz');
        $data['content'] = 'quiz/play_content';
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

    /* ===== PLAYER APIS ===== */

    // Join by PIN → set quiz_id in session, ensure player exists
    public function api_join_by_pin()
    {
        if ($this->input->method(TRUE) !== 'POST') {
            return $this->json(['ok' => false, 'msg' => 'Method not allowed'], 405);
        }

        $pin = trim($this->input->post('pin'));
        if ($pin === '') return $this->json(['ok' => false, 'msg' => 'PIN wajib diisi'], 400);

        $quizAny = $this->quiz->get_quiz_by_pin_any($pin);
        if (!$quizAny) return $this->json(['ok' => false, 'msg' => 'PIN tidak ditemukan'], 404);

        if (empty($quizAny['is_active'])) {
            return $this->json([
                'ok'       => true,
                'ended'    => true,
                'quiz_md5' => md5((string)$quizAny['id'])   // <-- pakai ini di front-end
            ]);
        }


        $nrp  = $this->session->userdata('NRP');
        $name = $this->session->userdata('full_name') ?: $nrp;
        if (!$nrp) return $this->json(['ok' => false, 'msg' => 'User tidak terautentikasi'], 403);

        $this->quiz->ensure_player_in_quiz($quizAny['id'], $nrp, $name);
        $this->session->set_userdata('quiz_id', (int)$quizAny['id']);

        return $this->json(['ok' => true, 'quiz_id' => (int)$quizAny['id']]);
    }

    // State/current question untuk player
    public function api_current()
    {
        $override_qid = (int)$this->input->get('quiz_id');
        if ($override_qid > 0) {
            $host_nrp = $this->session->userdata('NRP');
            $q = $this->quiz->get_quiz($override_qid);
            if ($q && $q['host_nrp'] === $host_nrp) {
                $quiz_id = $override_qid;
            } else {
                return $this->json(['ok' => false, 'msg' => 'Forbidden'], 403);
            }
        } else {
            $quiz_id = (int)$this->session->userdata('quiz_id');
            if (!$quiz_id) return $this->json(['ok' => true, 'active' => false]); // belum join
        }

        $quiz = $this->quiz->get_quiz($quiz_id);
        $nrp = $this->session->userdata('NRP');
        $my_score = 0;
        if ($nrp) {
            $row = $this->db->select('score')->get_where('quiz_players', ['quiz_id' => $quiz_id, 'nrp' => $nrp])->row_array();
            $my_score = (int)($row['score'] ?? 0);
        }

        if (!$quiz || !$quiz['is_active'] || !$quiz['current_question']) {
            return $this->json(['ok' => true, 'active' => false, 'quiz_id' => $quiz_id, 'my_score' => $my_score]);
        }

        $q = $this->quiz->get_question($quiz['current_question']);
        if (!$q) return $this->json(['ok' => true, 'active' => false, 'quiz_id' => $quiz_id, 'my_score' => $my_score]);

        $remaining = null;
        if (!empty($q['time_limit']) && !empty($quiz['question_started_at'])) {
            $started = strtotime($quiz['question_started_at']);
            $deadline = $started + (int)$q['time_limit'];
            $remaining = max(0, $deadline - time());
        }

        return $this->json([
            'ok' => true,
            'active' => true,
            'quiz_id' => $quiz_id,
            'question_id' => (int)$q['id'],
            'question' => $q['question'],
            'options' => ['A' => $q['option_a'], 'B' => $q['option_b'], 'C' => $q['option_c'], 'D' => $q['option_d']],
            'time_remaining' => $remaining,
            'my_score' => $my_score
        ]);
    }

    public function api_answer()
    {
        if ($this->input->method(TRUE) !== 'POST') {
            return $this->json(['ok' => false, 'msg' => 'Method not allowed'], 405);
        }

        $quiz_id = (int)$this->session->userdata('quiz_id');
        $nrp     = $this->session->userdata('NRP');
        if (!$quiz_id || !$nrp) return $this->json(['ok' => false, 'msg' => 'Not joined'], 403);

        $question_id = (int)$this->input->post('question_id');
        $chosen      = strtoupper(trim((string)$this->input->post('chosen')));
        if ($question_id <= 0 || !in_array($chosen, ['A', 'B', 'C', 'D'], true)) {
            return $this->json(['ok' => false, 'msg' => 'Invalid input'], 400);
        }

        $quiz = $this->quiz->get_quiz($quiz_id);
        if (!$quiz || !$quiz['is_active'] || (int)$quiz['current_question'] !== $question_id) {
            return $this->json(['ok' => false, 'msg' => 'Question not active'], 409);
        }

        $q = $this->quiz->get_question($question_id);
        if (!$q) return $this->json(['ok' => false, 'msg' => 'Question not found'], 404);

        if (!empty($q['time_limit']) && !empty($quiz['question_started_at'])) {
            $started = strtotime($quiz['question_started_at']);
            if (time() > $started + (int)$q['time_limit']) {
                return $this->json(['ok' => false, 'msg' => 'Time is up'], 409);
            }
        }

        if ($this->quiz->has_answered($quiz_id, $nrp, $question_id)) {
            return $this->json(['ok' => false, 'msg' => 'Already answered'], 409);
        }

        $res = $this->quiz->submit_answer($quiz_id, $nrp, $question_id, $chosen);

        if (!empty($quiz['question_started_at'])) {
            $elapsed_ms = max(0, (time() - strtotime($quiz['question_started_at'])) * 1000);
            $this->db->where(['quiz_id' => $quiz_id, 'question_id' => $question_id, 'nrp' => $nrp])
                ->update('quiz_answer', ['answer_ms' => $elapsed_ms]);
        }
        return $this->json($res, !empty($res['ok']) ? 200 : 400);
    }

    public function api_leaderboard($quiz_id)
    {
        return $this->json(['ok' => true, 'rows' => $this->quiz->leaderboard((int)$quiz_id, 100)]);
    }

    public function api_heartbeat()
    {
        if ($this->input->method(TRUE) !== 'POST') {
            return $this->json(['ok' => false, 'msg' => 'Method not allowed'], 405);
        }
        $quiz_id = (int)$this->session->userdata('quiz_id');
        $nrp     = $this->session->userdata('NRP');
        if ($quiz_id && $nrp && $this->db->field_exists('last_seen_at', 'quiz_players')) {
            $this->db->where(['quiz_id' => $quiz_id, 'nrp' => $nrp])
                ->update('quiz_players', ['last_seen_at' => date('Y-m-d H:i:s')]);
        }
        return $this->json(['ok' => true]);
    }

    public function api_focus()
    {
        if ($this->input->method(TRUE) !== 'POST') {
            return $this->json(['ok' => false, 'msg' => 'Method not allowed'], 405);
        }
        $quiz_id = (int)$this->session->userdata('quiz_id');
        $nrp     = $this->session->userdata('NRP');
        $action  = $this->input->post('action'); // 'blur' | 'focus'
        if ($quiz_id && $nrp && in_array($action, ['blur', 'focus'], true)) {
            $field = $action === 'blur' ? 'blur_count' : 'focus_count';
            if ($this->db->field_exists($field, 'quiz_players')) {
                $this->db->set($field, "$field+1", FALSE)
                    ->where(['quiz_id' => $quiz_id, 'nrp' => $nrp])
                    ->update('quiz_players');
            }
        }
        return $this->json(['ok' => true]);
    }
}
