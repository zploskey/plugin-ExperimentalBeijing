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
        'search_element_texts',
        'search_form_default_query_type',
        'search_form_default_action',
        'search_form',
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

    /**
     * Add language English/Chinese language switching to navbar.
     * Gets current language from session and sets it as selected.
     * Defaults to 'en_US' locale.
     * @param Omeka_Navigation_Page_Mvc $nav
     * @return Omeka_Navigation_Page_Mvc $nav
     */
    public function filterPublicNavigationMain($nav)
    {
        $additionalNav = array(
            'zh_CN' => array(
                'label' => '中文',
                'uri'   => current_url(array('lang' => 'zh_CN')),
                'class' => 'lang-select',
            ),
            'en_US' => array(
                'label' => 'ENG',
                'uri'   => current_url(array('lang' => 'en_US')),
                'class' => 'lang-select',
            )
        );
        $langCodes = array_keys($additionalNav);
        $session = new Zend_Session_Namespace;
        if (isset($session->lang) AND in_array($session->lang, $langCodes)) {
            $selectedLang = $session->lang;
        } else {
            $selectedLang = 'en_US';
        }
        $additionalNav[$selectedLang]['class'] .= ' selected';
        foreach ($additionalNav as $addNav) {
            $nav[] = $addNav;
        }
        return $nav;
    }

    /**
     * Add translated text to search text used in site searches.
     *
     * @param ElementText[] $elementTexts argument array
     * @return array
     */
    public function filterSearchElementTexts($elementTexts)
    {
        $db = get_db();
        $translations = $db->getTable('MultilanguageTranslation');
        $sql = "SELECT locale_code FROM omeka_multilanguage_translations
                GROUP BY locale_code";
        $locales = $db->query($sql)->fetchAll();
        //die(print_r($locales));
        foreach ($elementTexts as $et) {
            foreach ($locales as $localeRow) {
                $locale = $localeRow['locale_code'];
                $text = $et->getText();
                $translated = __($text);
                if ($text == $translated) {
                    // no translation available, look up in Multilanguage table
                    $translated = $translations->getTranslation($et->record_id,
                        $et->record_type, $et->element_id, $locale, $text);
                    $translated = $translated->translation;
                }
                $et->setText($text . " $translated");
            }
        }
        return $elementTexts;
    }

    /**
     * Set the default query type to exact match.
     */
    public function filterSearchFormDefaultQueryType($type)
    {
        return 'exact_match';
    }

    /**
     * Set the default search form action to browse items using the query. The
     * normal search results page only receives English titles and so is not
     * easily translatable.
     */
    public function filterSearchFormDefaultAction($action)
    {
        return url('items/browse');
    }

    /**
     * Change the name of the query input to search so that items/browse
     * will function as a search page.
     */
    public function filterSearchForm($form, $args)
    {
        return preg_replace('/query/', 'search', $form, 1);
    }

}
