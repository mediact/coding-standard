<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */
namespace MediactCommon;

use PHP_CodeSniffer_File;

trait FunctionTrait
{
    /**
     * Get a function name.
     *
     * @param PHP_CodeSniffer_File $file
     * @param int                  $functionIndex
     *
     * @return bool|string
     */
    protected function getFunctionName(PHP_CodeSniffer_File $file, $functionIndex)
    {
        $index = $this->findFunctionNameIndex($file, $functionIndex);
        return $index
            ? $file->getTokens()[$index]['content']
            : false;
    }

    /**
     * Find the function name index.
     *
     * @param PHP_CodeSniffer_File $file
     * @param int                  $functionIndex
     *
     * @return bool|int
     */
    protected function findFunctionNameIndex(PHP_CodeSniffer_File $file, $functionIndex)
    {
        return $file->findNext([T_STRING], $functionIndex);
    }

    /**
     * Find the start of a function body.
     *
     * @param PHP_CodeSniffer_File $file
     * @param int                  $functionIndex
     *
     * @return bool|int
     */
    protected function findFunctionBodyStartIndex(PHP_CodeSniffer_File $file, $functionIndex)
    {
        return $file->findNext([T_SEMICOLON, T_OPEN_CURLY_BRACKET], $functionIndex);
    }
}
