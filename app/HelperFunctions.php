<?php

if (!function_exists('formatBytes')) {
    function formatBytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        // Uncomment one of the following alternatives
        $bytes /= pow(1024, $pow);
        // $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

if (!function_exists('generateConfigLink')) {
   function generateConfigLink($uuid, $url, $name = 'AmirFalconAC'): string
   {
       return 'vless://' .
       $uuid . '@' . $url . ':443?sni=' .
       config('telegraph.xui.active_domain') .
       '&security=tls&type=ws&path=/chat&host=' .
       config('telegraph.xui.active_domain') .
       '#' . $name;
   }
}
