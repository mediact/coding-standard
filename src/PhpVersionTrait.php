<?php

/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\CodingStandard;

use PHP_CodeSniffer\Config;

trait PhpVersionTrait
{
    /**
     * Gets the PHP version.
     *
     * @return int
     */
    protected function getPhpVersion()
    {
        return Config::getConfigData('php_version')
            ?: PHP_VERSION_ID;
    }
}
