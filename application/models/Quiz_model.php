<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Quiz_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    /* ========== PLAYERS ========== */
    public function create_player($name)
    {
        $this->db->insert('players', ['name' => $name, 'score' => 0]);
        return $this->db->insert_id();
    }

    public function add_score($player_id, $points)
    {
        $this->db->set('score', 'score + ' . (int)$points, FALSE)
            ->where('id', $player_id)
            ->update('players');
    }

    public function leaderboard($limit = 50)
    {
        return $this->db->order_by('score', 'DESC')
            ->limit($limit)
            ->get('players')->result_array();
    }

    /* ========== QUESTIONS ========== */
    public function get_question($question_id)
    {
        return $this->db->get_where('questions', ['id' => $question_id])->row_array();
    }

    public function get_first_question_id()
    {
        $row = $this->db->select('id')->order_by('id', 'ASC')->limit(1)->get('questions')->row_array();
        return $row ? (int)$row['id'] : null;
    }

    public function get_next_question_id($current_id)
    {
        $row = $this->db->select('id')->where('id >', (int)$current_id)
            ->order_by('id', 'ASC')->limit(1)->get('questions')->row_array();
        return $row ? (int)$row['id'] : null;
    }

    /* ========== STATUS ========== */
    public function get_status()
    {
        return $this->db->get_where('quiz_status', ['id' => 1])->row_array();
    }

    public function set_status($data)
    {
        $this->db->where('id', 1)->update('quiz_status', $data);
    }

    public function reset_quiz()
    {
        $this->db->trans_start();
        $this->db->update('quiz_status', [
            'is_active' => 0,
            'current_question' => NULL,
            'question_started_at' => NULL
        ], ['id' => 1]);
        $this->db->update('players', ['score' => 0, 'last_answer_at' => NULL]);
        $this->db->truncate('answers');
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    /* ========== ANSWERS ========== */
    public function has_answered($player_id, $question_id)
    {
        return $this->db->where([
            'player_id' => $player_id,
            'question_id' => $question_id
        ])->from('answers')->count_all_results() > 0;
    }

    public function get_player_score($player_id)
    {
        $row = $this->db->select('score')->get_where('players', ['id' => $player_id])->row_array();
        return $row ? (int)$row['score'] : 0;
    }

    public function submit_answer($player_id, $question_id, $chosen)
    {
        $q = $this->get_question($question_id);
        if (!$q) return ['ok' => false, 'msg' => 'Question not found'];

        $is_correct = strtoupper($chosen) === strtoupper($q['answer']) ? 1 : 0;

        // Cegah dobel submit (protected by UNIQUE KEY)
        $this->db->insert('answers', [
            'player_id'   => $player_id,
            'question_id' => $question_id,
            'chosen'      => strtoupper($chosen),
            'is_correct'  => $is_correct
        ]);
        if ($this->db->affected_rows() <= 0) {
            return ['ok' => false, 'msg' => 'Already answered'];
        }

        $added = 0;
        if ($is_correct) {
            $status = $this->db->get_where('quiz_status', ['id' => 1])->row_array();
            $time_limit = (int)$q['time_limit'];
            $added = 100;
            if ($time_limit > 0 && !empty($status['question_started_at'])) {
                $started   = strtotime($status['question_started_at']);
                $elapsed   = max(0, time() - $started);
                $remaining = max(0, $time_limit - $elapsed);
                $added = round(40 + 60 * ($remaining / $time_limit));
                $added = max(40, min(100, $added));
            }
            $this->add_score($player_id, $added);
        }

        // Update last_answer_at & ambil skor terbaru
        $this->db->where('id', $player_id)->update('players', ['last_answer_at' => date('Y-m-d H:i:s')]);
        $score_now = $this->get_player_score($player_id);

        return [
            'ok'      => true,
            'correct' => (bool)$is_correct,
            'added'   => $added,       // poin yang baru ditambahkan
            'score'   => $score_now    // total skor terbaru
        ];
    }

    public function bind_fingerprint($player_id, $session_token, $ua, $ip)
    {
        $this->db->where('id', $player_id)->update('players', [
            'session_token' => $session_token,
            'user_agent'    => substr($ua, 0, 255),
            'last_ip'       => $ip
        ]);
    }

    public function validate_session($player_id, $session_token)
    {
        $row = $this->db->select('session_token')->get_where('players', ['id' => $player_id])->row_array();
        return $row && hash_equals((string)$row['session_token'], (string)$session_token);
    }

    public function inc_blur($player_id)
    {
        $this->db->set('blur_count', 'blur_count + 1', FALSE)
            ->where('id', $player_id)->update('players');
    }

    public function heartbeat($player_id)
    {
        $this->db->where('id', $player_id)->update('players', ['last_heartbeat' => date('Y-m-d H:i:s')]);
    }

    public function mark_suspicious($player_id)
    {
        $this->db->where('id', $player_id)->update('players', ['suspicious' => 1]);
    }

    public function get_focus_stats()
    {
        return $this->db->select('id,name,score,blur_count,suspicious,last_heartbeat')
            ->order_by('score', 'DESC')->get('players')->result_array();
    }

    /* ===== Rate limit sederhana per 10 detik ===== */
    public function allow_action($player_id, $action, $limit, $window_seconds = 10)
    {
        $window_start = date('Y-m-d H:i:s', (int)(time() / $window_seconds) * $window_seconds);
        $row = $this->db->get_where('rate_limit', [
            'player_id' => $player_id,
            'action' => $action,
            'window_start' => $window_start
        ])->row_array();

        if (!$row) {
            $this->db->insert('rate_limit', [
                'player_id' => $player_id,
                'action' => $action,
                'window_start' => $window_start,
                'count' => 1
            ]);
            return true;
        }
        if ((int)$row['count'] >= $limit) return false;
        $this->db->set('count', 'count + 1', FALSE)
            ->where('id', $row['id'])->update('rate_limit');
        return true;
    }
}
