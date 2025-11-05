<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once FCPATH . 'vendor/autoload.php';

use Google\Client as GoogleClient;
use Google\Service\Drive as GoogleDrive;

class Drive extends MY_Controller
{
    private $drive;

    public function __construct()
    {
        parent::__construct();

        $client = new GoogleClient();
        $client->setAuthConfig(APPPATH . 'keys/riungdriveapi-3e9a0d193ca1.json');
        $client->setScopes([GoogleDrive::DRIVE_READONLY]);
        $this->drive = new GoogleDrive($client);
    }

    public function employee_image($nrp)
    {
        $folderId = '1xy4GRrA5Wnc0y8IaUJlUsaLpVsovrS2Q';

        // --- Parse params ---
        $size = strtolower($this->input->get('size') ?: '3x4'); // 2x3, 3x4, 4x6
        $dpi  = intval($this->input->get('dpi') ?: 300);
        $bg   = strtolower($this->input->get('bg') ?: 'white'); // white|blue|red|#RRGGBB
        $q    = max(50, min(95, intval($this->input->get('q') ?: 90))); // JPEG quality

        // cm -> px (cm / 2.54 * dpi)
        $map = [
            '2x3' => [2.0, 3.0],
            '3x4' => [3.0, 4.0],
            '4x6' => [4.0, 6.0],
        ];
        if (!isset($map[$size])) $size = '3x4';
        [$w_cm, $h_cm] = $map[$size];
        $tw = (int) round(($w_cm / 2.54) * $dpi);
        $th = (int) round(($h_cm / 2.54) * $dpi);

        // --- Cari file di Drive ---
        $qDrive = sprintf(
            "'%s' in parents and trashed=false and mimeType contains 'image/' and name contains '%s'",
            $folderId,
            str_replace("'", "\\'", $nrp)
        );
        $res = $this->drive->files->listFiles([
            'q' => $qDrive,
            'fields' => 'files(id,name,mimeType)',
            'pageSize' => 50,
        ]);
        if (count($res->files) === 0) {
            show_error("Foto untuk NRP {$nrp} tidak ditemukan.", 404);
            return;
        }
        // Prioritaskan exact match {nrp}.jpg|png|webp
        $file = null;
        foreach ($res->files as $f) {
            if (preg_match('/^' . preg_quote($nrp, '/') . '\.(jpe?g|png|webp)$/i', $f->name)) {
                $file = $f;
                break;
            }
        }
        if (!$file) $file = $res->files[0];

        // --- Ambil binary ---
        /** @var \Psr\Http\Message\ResponseInterface $stream */
        $stream = $this->drive->files->get($file->id, ['alt' => 'media']);
        $blob   = $stream->getBody()->getContents();

        // --- GD load image ---
        $src = @imagecreatefromstring($blob);
        if (!$src) {
            show_error("Gagal membaca gambar {$file->name}.", 500);
            return;
        }

        // --- Hitung crop center sesuai aspect target ---
        $sw = imagesx($src);
        $sh = imagesy($src);
        $targetRatio = $tw / $th;
        $srcRatio    = $sw / $sh;

        if ($srcRatio > $targetRatio) {
            // potong lebar
            $newW = (int) round($sh * $targetRatio);
            $newH = $sh;
            $sx = (int) floor(($sw - $newW) / 2);
            $sy = 0;
        } else {
            // potong tinggi
            $newW = $sw;
            $newH = (int) round($sw / $targetRatio);
            $sx = 0;
            $sy = (int) floor(($sh - $newH) / 2);
        }

        // --- Canvas + background (buat pasfoto rapi, handle PNG transparan) ---
        [$r, $g, $b] = $this->parseColor($bg);
        $dst = imagecreatetruecolor($tw, $th);
        $bgc = imagecolorallocate($dst, $r, $g, $b);
        imagefill($dst, 0, 0, $bgc);

        // high-quality resample
        imagecopyresampled($dst, $src, 0, 0, $sx, $sy, $tw, $th, $newW, $newH);

        // --- Output JPEG ---
        header('Content-Type: image/jpeg');
        header('Cache-Control: public, max-age=604800'); // 7 hari
        imagejpeg($dst, null, $q);

        imagedestroy($src);
        imagedestroy($dst);
    }

    // Parse warna: white|blue|red|#RRGGBB
    private function parseColor($name)
    {
        $name = trim($name);
        $preset = [
            'white' => [255, 255, 255],
            'blue'  => [0, 70, 160], // biru pasfoto umum Indo
            'red'   => [200,  0,  0], // merah pasfoto umum Indo
        ];
        if (isset($preset[$name])) return $preset[$name];

        if (preg_match('/^#?([a-f0-9]{6})$/i', $name, $m)) {
            $hex = $m[1];
            return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
        }
        return $preset['white'];
    }
}
