<?php

/**
 * Extensions for the Experimental Beijing project.
 */

define('EXPERIMENTAL_BEIJING_DIR', dirname(__FILE__));

require_once(EXPERIMENTAL_BEIJING_DIR . '/helpers/tags.php');

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
     * This is done by temporarily adding the translations to the end of the
     * element text before the afterSave hook calls addSearchText on them.
     *
     * @param ElementText[] $elementTexts argument array
     * @return array
     */
    public function filterSearchElementTexts($elementTexts)
    {
        $db = get_db();
        $translations = $db->getTable('MultilanguageTranslation');
        $sql = "SELECT locale_code FROM $db->MultilanguageTranslations
                GROUP BY locale_code";
        $locales = $db->fetchCol($sql);
        foreach ($elementTexts as $et) {
            foreach ($locales as $locale) {
                $text = $et->getText();
                $translationRecord = $translations->getTranslation(
                    $et->record_id, $et->record_type, $et->element_id,
                    $locale, $text
                );
                if ($translationRecord) {
                    $translation = $translationRecord->translation;
                } else {
                    $translation = '';
                }
                if (!$translation) {
                    $translation = __($text);
                }
                if ($text != $translation) {
                    $et->setText("$text $translation");
                }
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
}
