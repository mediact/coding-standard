<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace MediaCT\Sniffs\Arrays;

use PHP_CodeSniffer_File;
use PHP_CodeSniffer_Sniff;

class ArrayNotationSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * Register tokens.
     *
     * @return array
     */
    public function register()
    {
        return [T_ARRAY];
    }

    /**
     * Add fixable error message to long notated arrays.
     *
     * @param PHP_CodeSniffer_File $phpcsFile
     * @param int $stackPtr
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $phpcsFile->addFixableError(
            'Only short notation should be used for arrays, e.g. []',
            $stackPtr
        );
    }
}
