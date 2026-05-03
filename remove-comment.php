<?php

$selfPath = realpath(__FILE__);

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(__DIR__, RecursiveDirectoryIterator::SKIP_DOTS)
);

$processed = 0;
$skipped = 0;

foreach ($iterator as $file) {
    if ($file->getExtension() !== 'php') {
        continue;
    }

    $path = $file->getRealPath();

    // Skip this script itself to avoid self-corruption
    if ($path === $selfPath) {
        continue;
    }

    // Skip vendor directory
    if (str_contains($path, DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR)) {
        continue;
    }

    $code = file_get_contents($path);

    if ($code === false) {
        echo "SKIP (unreadable): $path\n";
        $skipped++;
        continue;
    }

    $tokens = token_get_all($code);
    $output = '';

    foreach ($tokens as $token) {
        if (is_array($token)) {
            // Remove single-line comments (// and #) and block/doc comments (/* */ and /** */)
            if ($token[0] === T_COMMENT || $token[0] === T_DOC_COMMENT) {
                // Preserve the newline at the end of single-line comments
                // so line numbers don't shift and blank lines remain clean
                if (str_ends_with($token[1], "\n")) {
                    $output .= "\n";
                }
                continue;
            }
            $output .= $token[1];
        } else {
            $output .= $token;
        }
    }

    if ($output !== $code) {
        file_put_contents($path, $output);
        echo "CLEANED: $path\n";
        $processed++;
    }
}

echo "\nDone. Cleaned: $processed file(s), Skipped: $skipped file(s).\n";
