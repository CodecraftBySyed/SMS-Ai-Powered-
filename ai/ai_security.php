<?php
function sanitize_user_input(string $text): string {
    $t = trim($text);
    $patterns = [
        '/ignore\s+previous\s+instructions/i',
        '/disregard\s+previous\s+instructions/i',
        '/override\s+system\s+prompt/i',
        '/reveal\s+system\s+prompt/i',
        '/show\s+system\s+prompt/i',
        '/show\s+database/i',
        '/dump\s+database/i',
        '/expose\s+keys?/i',
        '/reveal\s+api\s+key/i'
    ];
    foreach ($patterns as $p) {
        $t = preg_replace($p, '', $t);
    }
    $t = preg_replace('/\s{2,}/', ' ', $t);
    return $t;
}
?>
