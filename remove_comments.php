<?php

$directory = __DIR__;

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($directory)
);

foreach ($iterator as $file) {
    if ($file->isDir()) {
        continue;
    }

    if ($file->getExtension() !== 'php') {
        continue;
    }

    $path = $file->getPathname();
    $code = file_get_contents($path);

    $tokens = token_get_all($code);
    $output = '';

    foreach ($tokens as $token) {
        if (is_array($token)) {
            if (in_array($token[0], [T_COMMENT, T_DOC_COMMENT])) {
                continue;
            }
            $output .= $token[1];
        } else {
            $output .= $token;
        }
    }

    file_put_contents($path, $output);
    echo "Cleaned: $path\n";
}