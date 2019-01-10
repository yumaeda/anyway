<?php
// Constants
define('TARGET_DATE', '2019-10-01 00:00:00');
define('TIME_ZONE', 'Asia/Tokyo');
define('CURRENT_TAX_RATE', 0.08);
define('NEW_TAX_RATE', 0.1);

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
