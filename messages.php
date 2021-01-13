<?php 
$messages = [
    'success' => 'check-circle' ,
    'info' => 'info-circle',
    'warning' => 'exclamation-circle',
    'error' => 'exclamation-triangle'
];
foreach ($messages as $type => $icon) {
    if ($msg = $app->getMessage($type)) {
        echo <<<HTML
        <p class="message {$type}"><i class="fas fa-{$icon}"></i> {$msg}</p>
        HTML;
    }
}
