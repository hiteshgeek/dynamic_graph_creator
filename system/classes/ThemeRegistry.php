<?php

/**
 * Theme Registry - manages page rendering
 * Compatible with rapidkart's ThemeRegistry
 *
 * @author Dynamic Graph Creator
 */
class ThemeRegistry
{
    private $stylesheets = array();
    private $scripts = array();
    private $content = '';
    public $pagetitle = '';

    /**
     * Add CSS file
     * @param string $url CSS file URL
     * @param int $weight Load order (lower = earlier)
     */
    public function addCss($url, $weight = 10)
    {
        while (isset($this->stylesheets[$weight])) {
            $weight++;
        }
        $this->stylesheets[$weight] = $url;
    }

    /**
     * Add JavaScript file
     * @param string $url JS file URL
     * @param int $weight Load order (lower = earlier)
     */
    public function addScript($url, $weight = 10)
    {
        while (isset($this->scripts[$weight])) {
            $weight++;
        }
        $this->scripts[$weight] = $url;
    }

    /**
     * Set main content
     * @param string $region Region name (for compatibility with rapidkart, ignored here)
     * @param string $content HTML content
     */
    public function setContent($region, $content)
    {
        $this->content = $content;
    }

    /**
     * Set page title
     * @param string $title Page title
     */
    public function setPageTitle($title)
    {
        $this->pagetitle = $title;
    }

    /**
     * Get page title
     * @return string
     */
    public function getPageTitle()
    {
        return $this->pagetitle;
    }

    /**
     * Render the full page
     */
    public function renderPage()
    {
        ksort($this->stylesheets);
        ksort($this->scripts);
        require_once SystemConfig::templatesPath() . 'html.tpl.php';
    }

    /**
     * Get stylesheets array
     * @return array
     */
    public function getStylesheets()
    {
        return $this->stylesheets;
    }

    /**
     * Get scripts array
     * @return array
     */
    public function getScripts()
    {
        return $this->scripts;
    }

    /**
     * Get content
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }
}
