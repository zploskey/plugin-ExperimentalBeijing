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
    );

    /**
     * Initialize translations.
     */
    public function hookInitialize()
    {
        add_translation_source(dirname(__FILE__) . '/languages');
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

}
