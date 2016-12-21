<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */
namespace MediaCT\Sniffs\Php7;

use PHP_CodeSniffer;
use PHP_CodeSniffer_File;
use PHP_CodeSniffer_Sniff;
use PHP_CodeSniffer_Tokens;

class ReturnTypeSniff implements PHP_CodeSniffer_Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     */
    public function register()
    {
        return [T_FUNCTION];
    }

    /**
     * Called when one of the token types that this sniff is listening for
     * is found.
     *
     * @param PHP_CodeSniffer_File $phpcsFile
     * @param int                  $stackPtr
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        if ($this->getPhpVersion() < 70000) {
            return;
        }

        $functionStart     = $stackPtr;
        $functionBodyStart = $this->findFunctionBodyStartIndex($phpcsFile, $functionStart);
        $commentEnd        = $this->findCommentEndIndex($phpcsFile, $stackPtr);
        $commentStart      = $this->findCommentStartIndex($phpcsFile, $commentEnd);

        if ($commentStart && $functionBodyStart) {
            $suggestedReturnTypes = $this->findSuggestedReturnTypes($phpcsFile, $commentStart);
            $filteredReturnTypes  = array_filter($suggestedReturnTypes);
            $returnType           = $this->findActualReturnType($phpcsFile, $functionStart, $functionBodyStart);

            if (empty($returnType)
                && count($suggestedReturnTypes) > 1
            ) {
                $phpcsFile->addError(
                    'Multiple return types are discouraged',
                    $functionStart,
                    'MultipleReturnTypes'
                );
            } elseif (empty($returnType)
                && count($suggestedReturnTypes) == 1
                && count($filteredReturnTypes) == 1
            ) {
                $phpcsFile->addWarning(
                    sprintf(
                        'Method should have a return type in PHP7, use %s',
                        reset($filteredReturnTypes)
                    ),
                    $functionStart,
                    'MissingReturnType'
                );
            } elseif (!empty($returnType)
                && count($filteredReturnTypes)
                && !in_array($returnType, $filteredReturnTypes)
            ) {
                $phpcsFile->addWarning(
                    sprintf(
                        'Method return type should be one of (%s)',
                        implode('|', $suggestedReturnTypes)
                    ),
                    $functionStart,
                    'WrongReturnType'
                );
            }
        }
    }

    /**
     * @param PHP_CodeSniffer_File $phpcsFile
     * @param int                  $functionIndex
     *
     * @return bool|int
     */
    protected function findFunctionNameIndex(PHP_CodeSniffer_File $phpcsFile, $functionIndex)
    {
        return $phpcsFile->findNext([T_STRING], $functionIndex);
    }

    /**
     * @param PHP_CodeSniffer_File $phpcsFile
     * @param int                  $functionIndex
     *
     * @return bool|int
     */
    protected function findFunctionBodyStartIndex(PHP_CodeSniffer_File $phpcsFile, $functionIndex)
    {
        return $phpcsFile->findNext([T_SEMICOLON, T_OPEN_CURLY_BRACKET], $functionIndex);
    }

    /**
     * @param PHP_CodeSniffer_File $phpcsFile
     * @param int                  $functionIndex
     *
     * @return bool|int
     */
    protected function findCommentEndIndex(PHP_CodeSniffer_File $phpcsFile, $functionIndex)
    {
        $searchTypes   = array_merge(PHP_CodeSniffer_Tokens::$methodPrefixes, [T_WHITESPACE]);
        $previousToken = $phpcsFile->findPrevious($searchTypes, $functionIndex - 1, null, true);
        if ($previousToken
            && $phpcsFile->getTokens()[$previousToken]['code'] == T_DOC_COMMENT_CLOSE_TAG
        ) {
            return $previousToken;
        }
        return false;
    }

    /**
     * @param PHP_CodeSniffer_File $phpcsFile
     * @param int                  $commentEnd
     *
     * @return bool|int
     */
    protected function findCommentStartIndex(PHP_CodeSniffer_File $phpcsFile, $commentEnd)
    {
        if (!$commentEnd) {
            return false;
        }
        return $phpcsFile->getTokens()[$commentEnd]['comment_opener'];
    }

    /**
     * @param PHP_CodeSniffer_File $phpcsFile
     * @param int                  $commentStart
     *
     * @return array
     */
    protected function findSuggestedReturnTypes(PHP_CodeSniffer_File $phpcsFile, $commentStart)
    {
        $returnTag = $this->findTagIndex($phpcsFile, $commentStart, '@return');
        return $returnTag
            ? $this->getSuggestedTypes($this->getTypeFromTag($phpcsFile, $returnTag))
            : [];
    }

    /**
     * @param PHP_CodeSniffer_File $phpcsFile
     * @param int                  $commentStart
     * @param string               $tagName
     *
     * @return bool|int
     */
    protected function findTagIndex(PHP_CodeSniffer_File $phpcsFile, $commentStart, $tagName)
    {
        $tokens = $phpcsFile->getTokens();

        $index = false;
        foreach ($tokens[$commentStart]['comment_tags'] as $tag) {
            if ($tokens[$tag]['content'] === $tagName) {
                $index = $tag;
            }
        }
        return $index;
    }

    /**
     * @param PHP_CodeSniffer_File $phpcsFile
     * @param int                  $tagIndex
     *
     * @return string
     */
    protected function getTypeFromTag(PHP_CodeSniffer_File $phpcsFile, $tagIndex)
    {
        $content = $phpcsFile->getTokens()[($tagIndex + 2)]['content'];
        $parts   = explode(' ', $content, 2);
        $type    = $parts[0];
        return $type;
    }

    /**
     * @param string $type
     *
     * @return array
     */
    protected function getSuggestedTypes($type)
    {
        $mapping = [
            'void'    => false,
            'mixed'   => false,
            'null'    => false,
            'int'     => 'int',
            'integer' => 'int',
            'string'  => 'string',
            'float'   => 'float',
            'bool'    => 'bool',
            'boolean' => 'bool',
        ];

        $typeNames = explode('|', $type);
        return array_unique(
            array_map(
                function ($typeName) use ($mapping) {
                    if (isset($mapping[$typeName])) {
                        return $mapping[$typeName];
                    }
                    if ($this->isTypeArray($typeName)) {
                        return 'array';
                    }
                    if ($this->isTypeCallable($typeName)) {
                        return 'callable';
                    }
                    if ($this->isTypeObject($typeName)) {
                        return $typeName;
                    }
                    return false;
                },
                $typeNames
            )
        );
    }

    /**
     * @param string $typeName
     *
     * @return bool
     */
    protected function isTypeArray($typeName)
    {
        return strpos($typeName, 'array') !== false
            || substr($typeName, -2) === '[]';
    }

    /**
     * @param string $typeName
     *
     * @return bool
     */
    protected function isTypeCallable($typeName)
    {
        return strpos($typeName, 'callable') !== false
            || strpos($typeName, 'callback') !== false;
    }

    /**
     * @param string $typeName
     *
     * @return bool
     */
    protected function isTypeObject($typeName)
    {
        return in_array($typeName, PHP_CodeSniffer::$allowedTypes) === false;
    }


    /**
     * @param PHP_CodeSniffer_File $phpcsFile
     * @param string               $functionStart
     * @param string               $functionBodyStart
     *
     * @return string
     */
    protected function findActualReturnType(PHP_CodeSniffer_File $phpcsFile, $functionStart, $functionBodyStart)
    {
        $returnTypeIndex = $phpcsFile->findNext(
            T_RETURN_TYPE,
            $functionStart,
            $functionBodyStart
        );

        if (!$returnTypeIndex) {
            // Sometimes the return tag has been parsed wrong by PHPCS
            $returnTypeIndex = $this->findFunctionArrayReturnIndex(
                $phpcsFile,
                $functionStart,
                $functionBodyStart
            );
        }

        return $returnTypeIndex
            ? $phpcsFile->getTokens()[$returnTypeIndex]['content']
            : '';
    }

    /**
     * @param PHP_CodeSniffer_File $phpcsFile
     * @param int                  $functionStart
     * @param int                  $functionBodyStart
     *
     * @return int|bool
     */
    protected function findFunctionArrayReturnIndex(
        PHP_CodeSniffer_File $phpcsFile,
        $functionStart,
        $functionBodyStart
    ) {
        $closingIndex = $phpcsFile->findNext(
            T_CLOSE_PARENTHESIS,
            $functionStart,
            $functionBodyStart
        );

        return $phpcsFile->findNext(
            [T_ARRAY_HINT],
            $closingIndex,
            $functionBodyStart
        );
    }

    /**
     * @return int
     */
    protected function getPhpVersion()
    {
        return PHP_CodeSniffer::getConfigData('php_version')
            ?: PHP_VERSION_ID;
    }
}
