<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */
namespace MediactCommon;

use PHP_CodeSniffer;

trait PhpVersionTrait
{
    /**
     * Gets the PHP version.
     *
     * @return int
     */
    protected function getPhpVersion()
    {
        return PHP_CodeSniffer::getConfigData('php_version')
            ?: PHP_VERSION_ID;
    }
}
