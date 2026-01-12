<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Image extends CI_Controller
{
    public function show($key)
    {
        $key = urldecode($key);
        $basePath = FCPATH . 'uploads/employee_images/';

        // --- Normalisasi input (support spasi & underscore) ---
        $raw = trim((string)$key);
        if ($raw === '') {
            $this->outputDefault($basePath . 'default.JPG', 90);
            return;
        }

        // Hapus karakter berbahaya untuk path, tapi JANGAN buang spasi/underscore
        $raw = preg_replace('/[\/\\\\:*?"<>|]/', '', $raw);
        $raw = str_replace('..', '', $raw);
        $raw = preg_replace('/\s+/', ' ', $raw); // rapikan spasi ganda

        // Kandidat pencarian (nama bisa pakai spasi atau underscore)
        $k1 = $raw;                          // "AULIANSYAH AFRIANTHONI" atau "10125103"
        $k2 = str_replace(' ', '_', $raw);   // "AULIANSYAH_AFRIANTHONI"
        $k3 = str_replace('_', ' ', $raw);   // kalau input underscore tapi file pakai spasi
        $keys = array_values(array_unique([$k1, $k2, $k3]));

        // --- Parse params (samakan dengan versi Drive) ---
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

        $dpi = max(72, min(1200, $dpi));
        $tw = (int) round(($w_cm / 2.54) * $dpi);
        $th = (int) round(($h_cm / 2.54) * $dpi);

        // --- Cari file lokal (exact dulu, lalu wildcard) ---
        $matches = [];
        foreach ($keys as $k) {
            $patterns = [
                $basePath . $k . '.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}',          // exact
                $basePath . '*' . $k . '*.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}',  // Copy of ...-removebg-preview
            ];

            foreach ($patterns as $p) {
                $found = glob($p, GLOB_BRACE);
                if (!empty($found)) {
                    $matches = $found;
                    break 2;
                }
            }
        }

        if (empty($matches)) {
            $this->outputDefault($basePath . 'default.JPG', $q);
            return;
        }

        // Ambil yang terbaru (kalau ada banyak varian)
        usort($matches, fn($a, $b) => @filemtime($b) <=> @filemtime($a));
        $filePath = $matches[0];

        $blob = @file_get_contents($filePath);
        if ($blob === false) {
            $this->outputDefault($basePath . 'default.JPG', $q);
            return;
        }

        // --- GD load image ---
        $src = @imagecreatefromstring($blob);
        if (!$src) {
            $this->outputDefault($basePath . 'default.JPG', $q);
            return;
        }

        // --- Crop center sesuai aspect target ---
        $sw = imagesx($src);
        $sh = imagesy($src);
        $targetRatio = $tw / $th;
        $srcRatio    = $sw / $sh;

        if ($srcRatio > $targetRatio) {
            $newW = (int) round($sh * $targetRatio);
            $newH = $sh;
            $sx = (int) floor(($sw - $newW) / 2);
            $sy = 0;
        } else {
            $newW = $sw;
            $newH = (int) round($sw / $targetRatio);
            $sx = 0;
            $sy = (int) floor(($sh - $newH) / 2);
        }

        // --- Canvas + background (handle PNG transparan) ---
        [$r, $g, $b] = $this->parseColor($bg);
        $dst = imagecreatetruecolor($tw, $th);
        $bgc = imagecolorallocate($dst, $r, $g, $b);
        imagefill($dst, 0, 0, $bgc);

        imagecopyresampled($dst, $src, 0, 0, $sx, $sy, $tw, $th, $newW, $newH);

        // --- Output JPEG ---
        header('Content-Type: image/jpeg');
        header('Cache-Control: public, max-age=604800');
        imagejpeg($dst, null, $q);

        imagedestroy($src);
        imagedestroy($dst);
    }

    private function outputDefault($defaultPath, $q = 90)
    {
        if (!is_file($defaultPath)) {
            show_404();
            return;
        }

        $blob = @file_get_contents($defaultPath);
        $src = @imagecreatefromstring($blob);
        if (!$src) {
            show_404();
            return;
        }

        header('Content-Type: image/jpeg');
        header('Cache-Control: public, max-age=604800');
        imagejpeg($src, null, max(50, min(95, (int)$q)));
        imagedestroy($src);
    }

    private function parseColor($name)
    {
        $name = trim($name);
        $preset = [
            'white' => [255, 255, 255],
            'blue'  => [0, 70, 160],
            'red'   => [200, 0, 0],
        ];
        if (isset($preset[$name])) return $preset[$name];

        if (preg_match('/^#?([a-f0-9]{6})$/i', $name, $m)) {
            $hex = $m[1];
            return [
                hexdec(substr($hex, 0, 2)),
                hexdec(substr($hex, 2, 2)),
                hexdec(substr($hex, 4, 2)),
            ];
        }
        return $preset['white'];
    }
}
