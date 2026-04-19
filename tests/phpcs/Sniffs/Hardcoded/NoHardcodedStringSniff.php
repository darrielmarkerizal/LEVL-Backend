<?php

namespace Tests\PHPCS\Sniffs\Hardcoded;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class NoHardcodedStringSniff implements Sniff
{
    public function register()
    {
        return [T_CONSTANT_ENCAPSED_STRING];
    }

    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $token = $tokens[$stackPtr];
        $content = $token['content'];

        
        if (strlen($content) < 5) {
            return;
        }

        
        $prevTokenIndex = $phpcsFile->findPrevious(T_WHITESPACE, $stackPtr - 1, null, true);
        if ($prevTokenIndex !== false) {
            $prevToken = $tokens[$prevTokenIndex];

            
            
            
            
            if ($prevToken['code'] === T_OPEN_PARENTHESIS) {
                $funcNameIndex = $phpcsFile->findPrevious(T_WHITESPACE, $prevTokenIndex - 1, null, true);
                if ($funcNameIndex !== false) {
                    $funcNameToken = $tokens[$funcNameIndex];
                    if ($funcNameToken['code'] === T_STRING) {
                        $funcName = $funcNameToken['content'];
                        if (in_array($funcName, ['__', 'trans', 'lang', 'config', 'env', 'view', 'route'])) {
                            return; 
                        }
                    }
                }
            }

            
            
            $nextTokenIndex = $phpcsFile->findNext(T_WHITESPACE, $stackPtr + 1, null, true);
            if ($nextTokenIndex !== false && $tokens[$nextTokenIndex]['code'] === T_DOUBLE_ARROW) {
                return;
            }

            
            
            
        }

        $phpcsFile->addWarning(
            'Potential hardcoded string detected: %s. Consider using translation: __(\'key\')',
            $stackPtr,
            'Found',
            [$content]
        );
    }
}
