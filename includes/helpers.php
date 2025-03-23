<?php
function getNotificationColor($type) {
    $colors = [
        'appointment' => 'primary',
        'reminder' => 'warning',
        'status_update' => 'info',
        'feedback' => 'success',
        'default' => 'secondary'
    ];
    return $colors[$type] ?? $colors['default'];
}

function getNotificationIcon($type) {
    $icons = [
        'appointment' => 'calendar-check',
        'reminder' => 'bell',
        'status_update' => 'info-circle',
        'feedback' => 'star',
        'default' => 'bell'
    ];
    return $icons[$type] ?? $icons['default'];
}

function getTimeAgo($timestamp) {
    $time = strtotime($timestamp);
    $now = time();
    $diff = $now - $time;
    
    $intervals = [
        31536000 => 'year',
        2592000 => 'month',
        86400 => 'day',
        3600 => 'hour',
        60 => 'minute',
        1 => 'second'
    ];
    
    foreach ($intervals as $seconds => $label) {
        $interval = floor($diff / $seconds);
        if ($interval >= 1) {
            return $interval . ' ' . $label . ($interval > 1 ? 's' : '') . ' ago';
        }
    }
    
    return 'Just now';
}
?>