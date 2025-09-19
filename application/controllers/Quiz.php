<?php
class Quiz extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Quiz_multi_model', 'quiz');
        $this->load->helper(['url', 'form']);
        $this->load->library('session');
    }

    /* ========== PAGES ========== */
    public function index()
    {
        $host_nrp = $this->session->userdata('NRP');
        $data['my_quizzes'] = $host_nrp ? $this->quiz->list_quiz_by_host($host_nrp, 50) : [];
        $data['content'] = 'quiz/index_content'; // NEW view berisi 2 card
        $this->load->view('templates/header_footer', $data);
    }

    // Host membuka panel untuk quiz tertentu (set session host_quiz_id)
    public function host($quiz_id = null)
    {
        if ($quiz_id) {
            $q = $this->quiz->get_quiz((int)$quiz_id);
            // security: hanya host pemilik quiz yang boleh set
            if ($q && $q['host_nrp'] === $this->session->userdata('NRP')) {
                $this->session->set_userdata('host_quiz_id', (int)$quiz_id);
            } else {
                // forbidden: tetap tampilkan host page tapi tanpa quiz aktif
                $this->session->unset_userdata('host_quiz_id');
            }
        }
        $data['content'] = 'quiz/host_content';
        $this->load->view('templates/header_footer', $data);
    }

    public function play()
    {
        if (!$this->session->userdata('quiz_id')) redirect('quiz');
        $data['content'] = 'quiz/play_content';
        $this->load->view('templates/header_footer', $data);
    }

    public function leaderboard($quiz_id)
    {
        $data['leaders'] = $this->quiz->leaderboard((int)$quiz_id, 50);
        $data['content'] = 'quiz/leaderboard_content';
        $this->load->view('templates/header_footer', $data);
    }

    /* ========== JSON helper ========== */
    private function json($data, $code = 200)
    {
        $this->output->set_status_header($code)
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }

    /* ========== HOST APIS ========== */

    // Buat quiz baru → generate PIN (valid sampai end)
    public function api_quiz_create()
    {
        $host_nrp = $this->session->userdata('NRP');
        if (!$host_nrp) return $this->json(['ok' => false, 'msg' => 'Host not authenticated'], 403);

        // PIN 6 digit unik
        do {
            $pin = str_pad((string)mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $exists = $this->quiz->get_quiz_by_pin($pin);
        } while ($exists);

        $quiz_id = $this->quiz->create_quiz($host_nrp, $pin);
        // simpan quiz host aktif ke session (opsional)
        $this->session->set_userdata('host_quiz_id', $quiz_id);
        return $this->json(['ok' => true, 'quiz_id' => $quiz_id, 'pin' => $pin]);
    }

    // Start soal pertama utk quiz host aktif
    public function api_start()
    {
        $quiz_id = (int)$this->session->userdata('host_quiz_id');
        if (!$quiz_id) return $this->json(['ok' => false, 'msg' => 'No active host quiz'], 400);

        $first = $this->quiz->get_first_question_id();
        if (!$first) return $this->json(['ok' => false, 'msg' => 'Belum ada soal'], 400);

        $this->quiz->set_status($quiz_id, [
            'is_active' => 1,
            'current_question' => $first,
            'question_started_at' => date('Y-m-d H:i:s')
        ]);
        return $this->json(['ok' => true, 'quiz_id' => $quiz_id, 'current_question' => $first]);
    }

    public function api_next()
    {
        $quiz_id = (int)$this->session->userdata('host_quiz_id');
        if (!$quiz_id) return $this->json(['ok' => false, 'msg' => 'No active host quiz'], 400);

        $quiz = $this->quiz->get_quiz($quiz_id);
        if (!$quiz || !$quiz['is_active']) return $this->json(['ok' => false, 'msg' => 'Quiz not active'], 409);

        $next = $this->quiz->get_next_question_id($quiz['current_question']);
        if (!$next) {
            $this->quiz->set_status($quiz_id, ['is_active' => 0, 'current_question' => NULL, 'question_started_at' => NULL, 'ended_at' => date('Y-m-d H:i:s')]);
            return $this->json(['ok' => true, 'ended' => true]);
        }
        $this->quiz->set_status($quiz_id, ['current_question' => $next, 'question_started_at' => date('Y-m-d H:i:s')]);
        return $this->json(['ok' => true, 'current_question' => $next]);
    }

    public function api_end()
    {
        $quiz_id = (int)$this->session->userdata('host_quiz_id');
        if (!$quiz_id) return $this->json(['ok' => false, 'msg' => 'No active host quiz'], 400);
        $this->quiz->set_status($quiz_id, ['is_active' => 0, 'current_question' => NULL, 'question_started_at' => NULL, 'ended_at' => date('Y-m-d H:i:s')]);
        return $this->json(['ok' => true]);
    }

    public function api_reset()
    {
        // optional: hapus pemain & jawaban quiz host aktif
        $quiz_id = (int)$this->session->userdata('host_quiz_id');
        if (!$quiz_id) return $this->json(['ok' => false, 'msg' => 'No active host quiz'], 400);
        $this->db->trans_start();
        $this->db->where('quiz_id', $quiz_id)->delete('quiz_answers');
        $this->db->where('quiz_id', $quiz_id)->update('quiz_players', ['score' => 0]);
        $this->quiz->set_status($quiz_id, ['current_question' => NULL, 'question_started_at' => NULL, 'is_active' => 1, 'ended_at' => NULL]);
        $this->db->trans_complete();
        return $this->json(['ok' => $this->db->trans_status()]);
    }

    /* ========== PLAYER APIS ========== */

    // Join by PIN → set quiz_id in session, ensure player exists
    public function api_join_by_pin()
    {
        $pin = trim($this->input->post('pin'));
        if ($pin === '') return $this->json(['ok' => false, 'msg' => 'PIN wajib diisi'], 400);

        $quiz = $this->quiz->get_quiz_by_pin($pin);
        if (!$quiz) return $this->json(['ok' => false, 'msg' => 'PIN tidak valid / quiz sudah berakhir'], 404);

        $nrp  = $this->session->userdata('NRP');
        $name = $this->session->userdata('full_name') ?: $nrp;
        if (!$nrp) return $this->json(['ok' => false, 'msg' => 'User tidak terautentikasi'], 403);

        $this->quiz->ensure_player_in_quiz($quiz['id'], $nrp, $name);
        $this->session->set_userdata('quiz_id', (int)$quiz['id']);
        return $this->json(['ok' => true, 'quiz_id' => (int)$quiz['id']]);
    }

    // Current question for the player quiz
    public function api_current()
    {
        // 1) Cek override quiz_id untuk host (GET)
        $override_qid = (int)$this->input->get('quiz_id');
        if ($override_qid > 0) {
            // Validasi: hanya host dari quiz tsb yang boleh override
            $host_nrp = $this->session->userdata('NRP');
            $q = $this->quiz->get_quiz($override_qid);
            if ($q && $q['host_nrp'] === $host_nrp) {
                $quiz_id = $override_qid;
            } else {
                return $this->json(['ok' => false, 'msg' => 'Forbidden'], 403);
            }
        } else {
            // Mode player biasa: ambil dari session join
            $quiz_id = (int)$this->session->userdata('quiz_id');
            if (!$quiz_id) return $this->json(['ok' => true, 'active' => false]); // belum join
        }

        // 2) Ambil quiz & skor saya (jika player)
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
        // simpan answer_ms (optional analitik)
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

    public function api_host_state()
    {
        $quiz_id = (int)$this->session->userdata('host_quiz_id');
        if (!$quiz_id) return $this->json(['ok' => true, 'quiz_id' => null, 'pin' => null]);
        $q = $this->quiz->get_quiz($quiz_id);
        return $this->json(['ok' => true, 'quiz_id' => $quiz_id, 'pin' => $q['pin'] ?? null]);
    }
}
