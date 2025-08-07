<?php
defined('BASEPATH') or exit('No direct script access allowed');

class MY_Controller extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        // Permission check only, token already validated by hook
        if (!$this->m_permission->has_permission() && $this->uri->segment(1) != 'testing' && $this->uri->segment(1) != 'dummy') {
            // show_error('Unauthorized access', 403);
            redirect('auth/logout');
        }
    }
}
