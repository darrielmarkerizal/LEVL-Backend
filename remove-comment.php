<?php

$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__));

foreach ($files as $file) {
    if ($file->getExtension() !== 'php') continue;

    $code = file_get_contents($file->getPathname());
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

    file_put_contents($file->getPathname(), $output);
}