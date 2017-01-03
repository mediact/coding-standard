<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */
namespace MediactCommon;

use PHP_CodeSniffer_File;
use PHP_CodeSniffer_Tokens;

trait PhpDocCommentTrait
{
    /**
     * Gets the end of a PHPDoc comment that is directly above an element.
     *
     * @param PHP_CodeSniffer_File $file
     * @param int                  $elementIndex
     *
     * @return bool|int
     */
    protected function findCommentEndIndex(PHP_CodeSniffer_File $file, $elementIndex) // @codingStandardsIgnoreLine
    {
        $searchTypes   = array_merge(PHP_CodeSniffer_Tokens::$methodPrefixes, [T_WHITESPACE]);
        $previousToken = $file->findPrevious($searchTypes, $elementIndex - 1, null, true);
        if ($previousToken
            && $file->getTokens()[$previousToken]['code'] == T_DOC_COMMENT_CLOSE_TAG
        ) {
            return $previousToken;
        }
        return false;
    }

    /**
     * Gets the start of a PHPDoc comment based on the end index.
     *
     * @param PHP_CodeSniffer_File $file
     * @param int                  $commentEnd
     *
     * @return bool|int
     */
    protected function findCommentStartIndex(PHP_CodeSniffer_File $file, $commentEnd) // @codingStandardsIgnoreLine
    {
        if (!$commentEnd) {
            return false;
        }
        return $file->getTokens()[$commentEnd]['comment_opener'];
    }


    /**
     * Gets the index of a PHPDoc tag.
     *
     * @param PHP_CodeSniffer_File $file
     * @param int                  $commentStart
     * @param string               $tagName
     *
     * @return bool|int
     */
    protected function findSingleCommentTagIndex(PHP_CodeSniffer_File $file, $commentStart, $tagName)
    {
        $indexes = $this->findCommentTagIndexes($file, $commentStart, $tagName);
        return count($indexes)
            ? array_shift($indexes)
            : false;
    }

    /**
     * Gets the indexes of a PHPDoc tag.
     *
     * @param PHP_CodeSniffer_File $file
     * @param                      $commentStart
     * @param                      $tagName
     *
     * @return array
     */
    protected function findCommentTagIndexes(PHP_CodeSniffer_File $file, $commentStart, $tagName)
    {
        $indexes = [];
        $tokens  = $file->getTokens();

        foreach ($tokens[$commentStart]['comment_tags'] as $index) {
            if ($tokens[$index]['content'] === $tagName) {
                $indexes[] = $index;
            }
        }
        return $indexes;
    }
}
