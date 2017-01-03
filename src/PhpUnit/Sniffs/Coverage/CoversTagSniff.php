<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */
namespace PhpUnit\Sniffs\Coverage;

use Common\FunctionTrait;
use Common\PhpDocCommentTrait;
use PHP_CodeSniffer_File;
use PHP_CodeSniffer_Sniff;

class CoversTagSniff implements PHP_CodeSniffer_Sniff
{
    use FunctionTrait;
    use PhpDocCommentTrait;

    /**
     * This is public so it can be configured in a rule set.
     *
     * @var array
     */
    public $methodPatterns = [
        'test*'
    ];

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
     * @param PHP_CodeSniffer_File $file
     * @param int                  $stackPtr
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $file, $stackPtr)
    {
        if (!$this->matchesPatterns($this->getFunctionName($file, $stackPtr))) {
            return;
        }

        $commentEnd   = $this->findCommentEndIndex($file, $stackPtr);
        $commentStart = $this->findCommentStartIndex($file, $commentEnd);
        if ($commentStart && $commentEnd) {
            $coversTags = $this->getCoversTags($file, $commentStart);

            $this->validateCoversTagExists($file, $commentStart, $coversTags);
            $this->validateCoversTagsNotEmpty($file, $coversTags);
            $this->validateCoversTagsAreRelative($file, $coversTags);
        }
    }

    /**
     * @param PHP_CodeSniffer_File $file
     * @param                      $commentStart
     * @param array                $tags
     *
     * @return void
     */
    protected function validateCoversTagExists(PHP_CodeSniffer_File $file, $commentStart, array $tags)
    {
        if (empty($tags)) {
            $file->addError(
                'Test methods must include a @covers tag',
                $commentStart,
                'CoversTagMissing'
            );
        }
    }

    /**
     * @param PHP_CodeSniffer_File $file
     * @param array                $tags
     *
     * @return void
     */
    protected function validateCoversTagsNotEmpty(PHP_CodeSniffer_File $file, array $tags)
    {
        foreach ($tags as $index => $value) {
            if (empty($value)) {
                $file->addError(
                    'Covers tag must not be empty',
                    $index,
                    'CoversTagEmpty'
                );
            }
        }
    }

    /**
     * @param PHP_CodeSniffer_File $file
     * @param array                $tags
     *
     * @return void
     */
    protected function validateCoversTagsAreRelative(PHP_CodeSniffer_File $file, array $tags)
    {
        foreach ($tags as $index => $value) {
            if (!empty($value) && substr($value, 0, 2) !== '::') {
                $file->addWarning(
                    'Covers tag should start with ::',
                    $index,
                    'CoversTagAbsolute',
                    [],
                    3
                );
            }
        }
    }

    /**
     * @param PHP_CodeSniffer_File $file
     * @param int                  $commentStart
     *
     * @return string[]
     */
    protected function getCoversTags(PHP_CodeSniffer_File $file, $commentStart)
    {
        $tags = [];

        $tokens  = $file->getTokens();
        $indexes = $this->findCommentTagIndexes($file, $commentStart, '@covers');
        foreach ($indexes as $index) {
            $tags[$index] = isset($tokens[$index + 2]) && $tokens[$index + 2]['code'] == T_DOC_COMMENT_STRING
                ? $tokens[$index + 2]['content']
                : null;
        }

        return $tags;
    }

    /**
     * @param string $functionName
     *
     * @return bool
     */
    protected function matchesPatterns($functionName)
    {
        $matches = array_filter(
            array_map(
                function ($pattern) use ($functionName) {
                    return fnmatch($pattern, $functionName);
                },
                $this->methodPatterns
            )
        );
        return !empty($matches);
    }
}
