<?php
// includes/functions.php

function uid($prefix = 'ID') {
    return $prefix . '-' . strtoupper(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 5)) . 
           strtoupper(substr(time(), -4));
}

function now() {
    return date('Y-m-d H:i:s');
}

function money($n) {
    return number_format((float)$n, 0, ',', '.') . 'đ';
}

function esc($s) {
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

function nl2br_esc($s) {
    return nl2br(esc($s));
}

function priceRange($packages) {
    if (empty($packages)) return money(0);
    $prices = array_column($packages, 'price');
    $min = min($prices);
    $max = max($prices);
    return $min === $max ? money($min) : money($min) . ' ~ ' . money($max);
}

function firstPkg($packages) {
    return !empty($packages) ? $packages[0] : ['name' => 'Gói mặc định', 'price' => 0, 'deliver' => ''];
}

function logoSrc($settings) {
    return $settings['logo'] ?? 'assets/products/logo-cheating-game-vn.png';
}

function generateRandomCode($length = 10) {
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $code;
}

function isValidUid($uid) {
    return preg_match('/^UID-[A-Z0-9]+$/', $uid);
}

function vietQrUrl($amount, $content) {
    $bank = 'MB';
    $acc = '0792822868';
    $name = urlencode('CHEATING GAME VN');
    $info = urlencode($content);
    return "https://img.vietqr.io/image/{$bank}-{$acc}-compact2.png?amount=" . intval($amount) . "&addInfo={$info}&accountName={$name}";
}