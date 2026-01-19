<?php

/**
 * Site-level configuration
 * Compatible with rapidkart's SiteConfig
 *
 * @author Dynamic Graph Creator
 */
class SiteConfig
{
    /**
     * @return String The URL of the directory containing libraries used in themes
     */
    public static function themeLibrariessUrl()
    {
        return SystemConfig::themesUrl() . "libraries/";
    }
}
