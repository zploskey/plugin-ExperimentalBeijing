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

    /**
     * Initialize translations.
     */
    public function hookInitialize()
    {
        add_translation_source(dirname(__FILE__) . '/languages');
    }

}
