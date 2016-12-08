<?php

/**
 * Extensions for the Experimental Beijing project.
 */

/**
 * Experimental Beijing plugin main class.
 */
class ExperimentalBeijingPlugin extends Omeka_Plugin_AbstractPlugin
{

    protected $_hooks = array(
        'initialize',
    );

    protected $_filters = array(
        'public_navigation_main',
        'search_form_default_query_type',
    );

    protected $_translatedTexts = array(
        'Original Format',
        'Original Material',
        'Role of Creator',
        'Role of Contributor',
    );

    /**
     * Initialize translations.
     */
    public function hookInitialize()
    {
        add_translation_source(dirname(__FILE__) . '/languages');
        foreach ($this->_translatedTexts as $tt) {
            add_filter(
                array('Display', 'Item', 'Item Type Metadata', $tt), '__');
        }
    }

    public function filterPublicNavigationMain($nav)
    {
        $nav[] = array(
            'label' => 'Language',
            'uri' => url('/'),
            'pages' => array(
                array('label' => '中文',
                      'uri' => current_url(array('lang' => 'zh_CN'))),
                array('label' => 'English',
                      'uri' => current_url(array('lang' => 'en_US'))),
            ),
        );
        return $nav;
    }

    /**
     * Set the default query type to exact match.
     */
    public function filterSearchFormDefaultQueryType($type)
    {
        return 'exact_match';
    }

}
