<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace MediaCT\Sniffs\NamingConventions;

use PHP_CodeSniffer_File;
use PHP_CodeSniffer_Sniff;

class ValidVariableNameSniff implements PHP_CodeSniffer_Sniff
{
    protected $allowedNames = [
        '_GET',
        '_POST',
        '_COOKIE',
        '_REQUEST',
        '_SERVER',
        '_SESSION'
    ];

    /**
     * Listen to variable name tokens.
     *
     * @return int[]
     */
    public function register()
    {
        return [T_VARIABLE];
    }

    /**
     * Check variable names to make sure no underscores are used.
     *
     * @param PHP_CodeSniffer_File $phpcsFile
     * @param int                  $stackPtr
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens  = $phpcsFile->getTokens();
        $varName = ltrim($tokens[$stackPtr]['content'], '$');

        if (!in_array($varName, $this->allowedNames)
            && preg_match('/^_/', $varName)
        ) {
            $phpcsFile->addFixableWarning(
                'Variable names may not start with an underscore',
                $stackPtr,
                'IllegalVariableNameUnderscore'
            );
        }
    }
}
