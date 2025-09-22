<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Quiz_multi_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    /* ===== QUIZ ROOM ===== */
    public function create_quiz($host_nrp, $pin = null, $title = null)
    {
        $this->db->insert('quiz', [
            'host_nrp'  => $host_nrp,
            'title'     => $title,
            'pin'       => null,   // <-- NULL dulu, belum generate
            'is_active' => 0       // <-- belum aktif
        ]);
        return $this->db->insert_id();
    }

    public function generate_unique_pin()
    {
        for ($i = 0; $i < 10; $i++) {
            $pin = str_pad((string)mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $exists = $this->db->where('pin', $pin)
                ->where('is_active', 1)
                ->count_all_results('quiz') > 0;
            if (!$exists) return $pin;
        }
        return false; // gagal setelah 10x
    }

    public function set_pin($quiz_id, $pin)
    {
        $this->db->where('id', (int)$quiz_id)->update('quiz', ['pin' => $pin]);
        return $this->db->affected_rows() > 0;
    }

    public function get_quiz_by_pin($pin)
    {
        return $this->db->get_where('quiz', ['pin' => $pin, 'is_active' => 1])->row_array();
    }

    public function get_quiz($quiz_id)
    {
        return $this->db->get_where('quiz', ['id' => $quiz_id])->row_array();
    }

    public function set_status($quiz_id, $data)
    {
        $this->db->where('id', $quiz_id)->update('quiz', $data);
    }

    public function list_quiz_by_host($host_nrp, $limit = 50)
    {
        // kalau tabel punya kolom created_at, pakai itu; kalau tidak, pakai id DESC
        if ($this->db->field_exists('created_at', 'quiz')) {
            $this->db->order_by('created_at', 'DESC');
            // tie-breaker tetap by id
            $this->db->order_by('id', 'DESC');
        } else {
            $this->db->order_by('id', 'DESC');
        }

        return $this->db->limit($limit)
            ->get_where('quiz', ['host_nrp' => $host_nrp])
            ->result_array();
    }

    /* ===== QUESTIONS ===== */
    public function get_question($question_id)
    {
        return $this->db->get_where('quiz_question', ['id' => $question_id])->row_array();
    }
    public function get_first_question_id()
    {
        $r = $this->db->select('id')->order_by('id', 'ASC')->limit(1)->get('quiz_question')->row_array();
        return $r ? (int)$r['id'] : null;
    }
    public function get_next_question_id($current_id)
    {
        $r = $this->db->select('id')->where('id >', (int)$current_id)
            ->order_by('id', 'ASC')->limit(1)->get('quiz_question')->row_array();
        return $r ? (int)$r['id'] : null;
    }

    /* ===== PLAYERS ===== */
    public function ensure_player_in_quiz($quiz_id, $nrp, $name)
    {
        $exists = $this->db->get_where('quiz_players', ['quiz_id' => $quiz_id, 'nrp' => $nrp])->row_array();
        if (!$exists) {
            $this->db->insert('quiz_players', ['quiz_id' => $quiz_id, 'nrp' => $nrp, 'name' => $name, 'score' => 0]);
        }
    }
    public function add_score($quiz_id, $nrp, $points)
    {
        $this->db->set('score', 'score + ' . (int)$points, FALSE)
            ->where(['quiz_id' => $quiz_id, 'nrp' => $nrp])
            ->update('quiz_players');
    }
    public function leaderboard($quiz_id, $limit = 100)
    {
        return $this->db->order_by('score', 'DESC')
            ->limit($limit)
            ->get_where('quiz_players', ['quiz_id' => $quiz_id])
            ->result_array();
    }

    /* ===== ANSWERS ===== */
    public function has_answered($quiz_id, $nrp, $question_id)
    {
        return $this->db->where(['quiz_id' => $quiz_id, 'nrp' => $nrp, 'question_id' => $question_id])
            ->from('quiz_answer')->count_all_results() > 0;
    }

    public function submit_answer($quiz_id, $nrp, $question_id, $chosen)
    {
        $q = $this->get_question($question_id);
        if (!$q) return ['ok' => false, 'msg' => 'Question not found'];

        $is_correct = strtoupper($chosen) === strtoupper($q['answer']) ? 1 : 0;

        $this->db->insert('quiz_answer', [
            'quiz_id' => $quiz_id,
            'question_id' => $question_id,
            'nrp' => $nrp,
            'chosen' => strtoupper($chosen),
            'is_correct' => $is_correct
        ]);
        if ($this->db->affected_rows() <= 0) return ['ok' => false, 'msg' => 'Already answered'];

        $added = 0;
        if ($is_correct) {
            $quiz = $this->get_quiz($quiz_id);
            $time_limit = (int)$q['time_limit'];
            $added = 100;
            if ($time_limit > 0 && !empty($quiz['question_started_at'])) {
                $started   = strtotime($quiz['question_started_at']);
                $elapsed   = max(0, time() - $started);
                $remaining = max(0, $time_limit - $elapsed);
                $added = round(40 + 60 * ($remaining / $time_limit));
                $added = max(40, min(100, $added));
            }
            $this->add_score($quiz_id, $nrp, $added);
        }
        $row = $this->db->select('score')->get_where('quiz_players', ['quiz_id' => $quiz_id, 'nrp' => $nrp])->row_array();
        return ['ok' => true, 'correct' => (bool)$is_correct, 'added' => $added, 'score' => (int)($row['score'] ?? 0)];
    }

    public function questions_by_quiz($quiz_id)
    {
        return $this->db->order_by('id', 'ASC')
            ->get_where('quiz_question', ['quiz_id' => $quiz_id])->result_array();
    }

    public function get_first_question_id_by_quiz($quiz_id)
    {
        $r = $this->db->select('id')
            ->where('quiz_id', (int)$quiz_id)
            ->order_by('id', 'ASC')->limit(1)
            ->get('quiz_question')->row_array();
        return $r ? (int)$r['id'] : null;
    }

    public function get_next_question_id_by_quiz($quiz_id, $current_id)
    {
        $r = $this->db->select('id')
            ->where('quiz_id', (int)$quiz_id)
            ->where('id >', (int)$current_id)
            ->order_by('id', 'ASC')->limit(1)
            ->get('quiz_question')->row_array();
        return $r ? (int)$r['id'] : null;
    }

    public function get_quiz_by_pin_any($pin)
    {
        // Ambil quiz dengan PIN tsb; utamakan yang aktif, kalau tidak ada, ambil yang selesai terbaru
        return $this->db->order_by('is_active', 'DESC')
            ->order_by('ended_at', 'DESC')
            ->order_by('id', 'DESC')
            ->get_where('quiz', ['pin' => $pin])
            ->row_array();
    }

    public function get_quiz_by_md5($hash)
    {
        // menghasilkan: SELECT * FROM quiz WHERE MD5(id) = '{hash}' LIMIT 1
        return $this->db->where('MD5(id)', $hash)
            ->get('quiz')->row_array();
    }
}
