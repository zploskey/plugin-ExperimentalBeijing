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
        'public_collections_show',
        'public_head',
        'public_items_show',
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

    public function hookPublicItemsShow($args)
    {
        $view = $args['view'];

        if (! isset($view->item)) {
            return;
        }

        $item = $view->item;
        $item_type = $item->getProperty('item_type_name');

        if (! in_array($item_type, array('Person', 'Series'))) {
            return;
        }

        // Fetch the item title
        $db = get_db();

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

        $its = $itemTable->getSelect();
        $its->joinInner(array('element_texts' => $db->ElementTexts),
            'element_texts.record_id = items.id', '');
        $its->joinInner(array('elements' => $db->Elements),
            'element_texts.element_id = elements.id', '');

        if ($item_type == 'Person') {
            $its->where('elements.name IN ("Creator", "Contributor")');
        }

        if ($item_type == 'Series') {
            $its->where('elements.name = "Is Part Of"');
        }

        $its->where('element_texts.text = ?', $title);

        $dates = $db->select();
        $dates->from(array('items' => $db->Items),
            array('id', 'dates_created' => 'element_texts.text'));
        $dates->joinInner(array('element_texts' => $db->ElementTexts),
            'element_texts.record_id = items.id', '');
        $dates->joinInner(array('elements' => $db->Elements),
            'element_texts.element_id = elements.id', '');
        $dates->where('elements.name = "Date Created"');

        $its->joinLeft(array('dates' => $dates), 'dates.id = items.id', '');
        $its->group('items.id');
        $its->order('dates.dates_created ASC');

        $works = $itemTable->fetchObjects($its);
        $view->works = $works;
    }

    public function hookPublicHead($args)
    {
        $this->_langRedirect();
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
     * Add count of related works for each Person to the view.
     */
    public function hookPublicCollectionsShow($args)
    {
        $itemIds = array();
        foreach (loop('items') as $item) {
            $itemIds[] = $item->id;
        }

        // TODO: return if not People page
        if (!$itemIds) {
            return;
        }

        $db = get_db();
        $person = $db->select()->from(array('items' => $db->Item),
            array('person_id' => 'items.id', 'person_name' => 'element_texts.text'));
        $person->joinInner(array('element_texts' => $db->ElementTexts),
            'element_texts.record_id = items.id', '');
        $person->joinInner(array('elements' => $db->Elements),
            'element_texts.element_id = elements.id', '');
        $person->where('items.id IN (?)', $itemIds);
        $person->where('elements.name = "Title"');
        $person->where('items.public = 1');

        $select = $db->select();
        $select->from(array('items' => $db->Item), '');
        $select->from(array('person' => $person),
            array('id' => 'person.person_id', 'works_count' => 'COUNT(*)'));
        $select->joinInner(array('element_texts' => $db->ElementTexts),
            'element_texts.record_id = items.id', '');
        $select->joinInner(array('elements' => $db->Elements),
            'element_texts.element_id = elements.id', '');
        $select->where('elements.name IN ("Creator", "Contributor")');
        $select->where('element_texts.text = person.person_name');
        $select->where('items.public = 1');
        $select->group('person.person_id');
        $rows = $db->fetchAll($select);

        $counts = array();
        foreach ($rows as $row) {
            $counts[$row['id']] = $row['works_count'];
        }

        $args['view']->counts = $counts;
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
