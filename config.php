<?php
if (!defined('TARGET_DATE')) {
    define('TARGET_DATE', '2019-10-01 00:00:00');
}

if (!defined('TIME_ZONE')) {
    define('TIME_ZONE', 'Asia/Tokyo');
}

if (!defined('CURRENT_TAX_RATE')) {
    define('CURRENT_TAX_RATE', 0.08);
}

if (!defined('NEW_TAX_RATE')) {
    define('NEW_TAX_RATE', 0.1);
}

return [
    'tax' => [
        'rate' => function() {
            $timeZone = new DateTimeZone(TIME_ZONE);
            $now = new DateTime(null, $timeZone);
            $target = new DateTime(TARGET_DATE, $timeZone);
        
            return ($now < $target) ? CURRENT_TAX_RATE : NEW_TAX_RATE;
        },
    ],
];
?>
