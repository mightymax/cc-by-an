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

$debug = $app->config('debug');
if (true === (bool)$debug) {
    if ($msg = $app->getMessage('debug')) {
        echo <<<HTML
        <blockquote>
            <p><code>*** DEBUG ***</code></p>
            <pre>{$msg}</pre>
        </blockquote>
        HTML;
    }
}