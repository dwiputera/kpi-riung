<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_talent extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->db->query("SET SESSION sql_mode = REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', '')");
    }

    function calculate_candidate_scores($employees)
    {
        foreach ($employees as &$emp) {
            $emp['score_kompetensi_teknis'] = $this->get_score_kompetensi_teknis($emp['kompetensi_teknis']);
            $emp['score_job_fit_score'] = $this->get_score_job_fit_score($emp['job_fit_score']);
            $emp['score_avg_ipa_score'] = $this->get_score_avg_ipa_score($emp['avg_ipa_score']);
            $emp['score_tour_of_duty'] = $this->get_score_tour_of_duty($emp['tour_of_duty']);
            $emp['score_culture_fit'] = $this->get_score_culture_fit($emp['culture_fit']);
            $emp['score_age'] = $this->get_score_age($emp['age']);
            $emp['score_status_kesehatan'] = $this->get_score_status_kesehatan($emp['status_kesehatan']);
            $emp['score_kategori_hav_mapping'] = $this->get_score_kategori_hav_mapping($emp['status']);
            $emp['score_assess_score'] = $this->get_score_assess_score($emp['assess_score']);

            $emp['score_nxb_kompetensi_teknis'] = $emp['score_kompetensi_teknis'] * 25 / 100;
            $emp['score_nxb_job_fit_score'] = $emp['score_job_fit_score'] * 15 / 100;
            $emp['score_nxb_avg_ipa_score'] = $emp['score_avg_ipa_score'] * 15 / 100;
            $emp['score_nxb_tour_of_duty'] = $emp['score_tour_of_duty'] * 10 / 100;
            $emp['score_nxb_culture_fit'] = $emp['score_culture_fit'] * 5 / 100;
            $emp['score_nxb_age'] = $emp['score_age'] * 5 / 100;
            $emp['score_nxb_status_kesehatan'] = $emp['score_status_kesehatan'] * 5 / 100;
            $emp['score_nxb_kategori_hav_mapping'] = $emp['score_kategori_hav_mapping'] * 10 / 100;
            $emp['score_nxb_assess_score'] = $emp['score_assess_score'] * 10 / 100;

            $score_to_sum = array('kompetensi_teknis', 'job_fit_score', 'avg_ipa_score', 'tour_of_duty', 'culture_fit', 'age', 'status_kesehatan', 'kategori_hav_mapping', 'assess_score');

            $emp['total_score'] = 0;
            foreach ($score_to_sum as $sts) {
                $emp['total_score'] += $emp["score_nxb_$sts"];
            }
        }
        return $employees;
    }

    function get_score_kompetensi_teknis($tod)
    {
        return null;
    }

    function get_score_job_fit_score($job_fit_score)
    {
        if ($job_fit_score > 4.55) return 5;
        if ($job_fit_score > 4) return 4;
        if ($job_fit_score > 3.56) return 3;
        if ($job_fit_score > 3) return 2;
        if ($job_fit_score > 2.5) return 1;
        return null;
    }

    function get_score_avg_ipa_score($avg_ipa_score)
    {
        if ($avg_ipa_score > 4.55) return 5;
        if ($avg_ipa_score > 4) return 4;
        if ($avg_ipa_score > 3.56) return 3;
        if ($avg_ipa_score > 3) return 2;
        if ($avg_ipa_score > 2.5) return 1;
        return null;
    }

    function get_score_tour_of_duty($tod)
    {
        return null;
    }

    function get_score_culture_fit($culture_fit)
    {
        return null;
    }

    function get_score_age($age)
    {
        if ($age >= 55 && $age < 35) return 1;
        if ($age >= 50) return 2;
        if ($age >= 45) return 3;
        if ($age >= 40) return 4;
        if ($age >= 35) return 5;
        return null;
    }

    function get_score_status_kesehatan($status_kesehatan)
    {
        return null;
    }

    function get_score_kategori_hav_mapping($kategori_hav_mapping)
    {
        if ($kategori_hav_mapping == 'Top Talent') return 5;
        if ($kategori_hav_mapping == 'Promotable') return 4;
        if ($kategori_hav_mapping == 'Sleeping Tiger') return 3;
        if ($kategori_hav_mapping == 'Solid Contributor') return 2;
        if ($kategori_hav_mapping == 'Unfit') return 1;
        return null;
    }

    function get_score_assess_score($assess_score)
    {
        if ($assess_score) {
            if ($assess_score >= 80) return 5;
            if ($assess_score >= 60) return 3;
            return 1;
        }
        return null;
    }
}
