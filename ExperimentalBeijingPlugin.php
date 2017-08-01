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
        'public_head',
    );

    protected $_filters = array(
        'search_element_texts',
        'search_form_default_query_type',
    );

    protected $_translatedTexts = array(
        'Original Format',
        'Original Material',
        'Role of Creator',
        'Role of Contributor',
    );

    protected $_locales = array(
        'en_US',
        'zh_CN',
    );

    protected $_defaultLang = 'en_US';

    protected $_lang = null;

    protected function _processLanguageSelection()
    {
        $session = new Zend_Session_Namespace;
        if (isset($session->lang) AND in_array($session->lang, $this->_locales)) {
            $this->_lang = $session->lang;
        } else {
            $this->_lang = $this->_defaultLang;
        }
    }

    public function getLang()
    {
        return $this->_lang;
    }

    /**
     * If on a simple page or exhibit adjust the URL according to
     * the convention that pages and exhibits end in '-zh_cn' on Chinese
     * language pages.
     */
    protected function _langRedirect()
    {
        $cur_url = $new_url = current_url();
        if ($cur_url != CURRENT_BASE_URL . '/') {
            if ($this->_lang == $this->_defaultLang) {
                $new_url = preg_replace('/(.*)-zh_cn(.*)/i', '\1\2', $cur_url);
            } else {
                $page = get_current_record('simple_pages_page', false);
                if ($page) {
                    $sp_slug = metadata('simple_pages_page', 'slug');
                    if ($sp_slug && ! preg_match('/-zh_cn$/i', $sp_slug)) {
                        $new_url = $cur_url . '-zh_cn';
                    }
                }

                $exhibit = get_current_record('exhibit', false);
                if ($exhibit && ! preg_match('/-zh_cn/i', $exhibit->slug)) {
                    $new_url = preg_replace('|(exhibits/show/)([^/.]*)|',
                                            '\1\2-zh_cn', $cur_url);
                }
            }
        }

        if ($new_url !== $cur_url) {
            $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
            $redirector->setPrependBase(false)->goToUrl($new_url);
        }
    }

    protected function _retrieveWorks($args)
    {
        $view = get_view();
        $vars = $view->getVars();
        if (! isset($vars['item'])) {
            return;
        }
        $item = $vars['item'];
        $item_type = $item->getProperty('item_type_name');

        if (! in_array($item_type, array('Person', 'Series'))) {
            return;
        }

        $db = get_db();

        // Fetch the item title
        $params = array(
            'record_id' => $item->id,
            'element_type' => 'Title',
        );
        $titleElementText = $db->getTable('ElementText')->findBy($params, 1);

        if (! $titleElementText) {
            return;
        }

        $title = $titleElementText[0]->text;

        // Fetch works related to this item
        $itemTable = $db->getTable('Item');
        $select = $itemTable->getSelect();
        $select->joinInner(array('element_texts' => $db->ElementTexts),
            'element_texts.record_id = items.id', '');
        $select->joinInner(array('elements' => $db->Elements),
            'element_texts.element_id = elements.id', array('elements.name'));
        $select->joinInner(array('item_types' => $db->ItemTypes),
            'items.item_type_id = item_types.id', '');

        if ($item_type == 'Person') {
            $select->where('elements.name IN ("Creator", "Contributor")');
            $select->where('element_texts.text = ?', $title);
        }

        if ($item_type == 'Series') {
            $select->where("elements.name = 'Is Part Of'");
        }

        $select->where('element_texts.text = ?', $title);
        $select->where('item_types.name IN ("Still Image", "Moving Image")');
        $works = $itemTable->fetchObjects($select);
        get_view()->works = $works;
    }

    public function hookPublicHead($args)
    {
        $this->_langRedirect();
        $this->_retrieveWorks($args);
    }

    /**
     * Initialize translations.
     */
    public function hookInitialize()
    {
        $this->_processLanguageSelection();
        add_translation_source(dirname(__FILE__) . '/languages');
        foreach ($this->_translatedTexts as $tt) {
            add_filter(
                array('Display', 'Item', 'Item Type Metadata', $tt), '__');
        }
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
