<?php
/** Merge duplicate class="" attributes left by phase2-inline-styles.php */
$dir = dirname(__DIR__) . '/resources/views/pages';
foreach (glob($dir . '/*.blade.php') as $file) {
    $content = file_get_contents($file);
    $prev = '';
    while ($content !== $prev) {
        $prev = $content;
        $content = preg_replace(
            '/class="([^"]*)"\s+class="([^"]*)"/',
            'class="$1 $2"',
            $content
        );
    }
    file_put_contents($file, $content);
}
echo "Merged duplicate class attributes.\n";
