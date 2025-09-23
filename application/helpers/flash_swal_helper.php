<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

function flash_swal(string $type, string $message): void
{
    $allowed = ['success', 'error', 'warning', 'info', 'question'];
    if (!in_array($type, $allowed, true)) $type = 'info';

    // kalau asalnya dari input user, minimal strip tag
    $message = trim($message);
    // optional keras:
    // $message = strip_tags($message);

    $CI = &get_instance();
    $CI->session->set_flashdata('swal', [
        'type' => $type,
        'message' => $message,
    ]);
}
