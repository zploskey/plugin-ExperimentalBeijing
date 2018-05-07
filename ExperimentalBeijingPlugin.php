<?php

/**
 * Extensions for the Experimental Beijing project.
 */
define('EXPERIMENTAL_BEIJING_DIR', dirname(__FILE__));

require_once EXPERIMENTAL_BEIJING_DIR . '/helpers/tags.php';

/**
 * Experimental Beijing plugin main class.
 */
class ExperimentalBeijingPlugin extends Omeka_Plugin_AbstractPlugin
{
    protected $_hooks = array(
        'initialize',
        'define_routes',
        'items_browse_sql',
        'public_collections_show',
        'public_head',
        'public_home',
        'public_items_show',
    );

    protected $_filters = array(
        'exhibit_attachment_markup',
        'items_browse_default_sort',
        'items_browse_params',
        'public_navigation_items',
        'public_navigation_main_all',
        'search_element_texts',
        'search_form_default_query_type',
        'search_form_default_action',
        'search_form',
    );

    protected $_translatedTexts = array(
        'Gender',
        'Language',
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
                array('Display', 'Item', 'Item Type Metadata', $tt),
                '__'
            );
        }
    }

    public function hookDefineRoutes($args)
    {
        $router = $args['router'];
        $router->addRoute(
            'ebj_chapters',
            new Zend_Controller_Router_Route(
                'chapters',
                array(
                    'module' => 'experimental-beijing',
                    'controller' => 'chapter',
                    'action' => 'index',
                )
            )
        );
    }

    /**
     * If on a simple page or exhibit adjust the URL according to
     * the convention that pages and exhibits end in '-zh_cn' on Chinese
     * language pages.
     */
    protected function _langRedirect()
    {
        $session = new Zend_Session_Namespace;
        $lang = $session->lang;
        $cur_url = $new_url = current_url();
        if ($cur_url != CURRENT_BASE_URL . '/') {
            if ($lang !== 'zh_CN') {
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
                    $new_url = preg_replace(
                        '|(exhibits/show/)([^/.]*)|',
                        '\1\2-zh_cn',
                        $cur_url
                    );
                }
            }
        }

        if ($new_url !== $cur_url) {
            $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
            $redirector->setPrependBase(false)->goToUrl($new_url);
        }
    }

    public function hookPublicHome($args)
    {
        $view = $args['view'];
        $config = array(
            'record_type' => 'AdminImage',
            'showtitles' => 'true',
            'ids' => '6,7,15,8,14,17,9,10,11,12,13,16',
            'num' => 20,
        );
        echo '</article></div>';
        $carousel = ShortcodeCarouselPlugin::carousel($config, $view);
        echo $carousel;
        echo '<div class="wrap"><article class="content" role="main" tabindex="-1">';
        echo $view->partial('index/home_about.php');
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
        $db = $this->_db;

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
        $its->joinLeft(
            array('element_texts' => $db->ElementTexts),
            'element_texts.record_id = items.id',
            ''
        );
        $its->joinLeft(
            array('elements' => $db->Elements),
            'element_texts.element_id = elements.id',
            ''
        );

        if ($item_type == 'Person') {
            $its->where('elements.name IN ("Creator", "Contributor")');
        }

        if ($item_type == 'Series') {
            $its->where('elements.name = "Is Part Of"');
        }

        $its->where('element_texts.text = ?', $title);

        $dates = $db->select();
        $dates->from(
            array('items' => $db->Items),
            array('id', 'dates_created' => 'element_texts.text')
        );
        $dates->joinLeft(
            array('element_texts' => $db->ElementTexts),
            'element_texts.record_id = items.id',
            ''
        );
        $dates->joinLeft(
            array('elements' => $db->Elements),
            'element_texts.element_id = elements.id',
            ''
        );
        $dates->where('elements.name = "Date Created"');

        $its->joinLeft(array('dates' => $dates), 'dates.id = items.id', '');
        $its->group('items.id');
        $its->order('dates.dates_created ASC');

        $allWorks = $itemTable->fetchObjects($its);

        if ($item_type === 'Person') {
            $works = array();
            $series = array();
            foreach ($allWorks as $work) {
                $part_of = metadata($work, array('Dublin Core', 'Is Part Of'));
                if ($part_of) {
                    if (isset($series[$part_of])) {
                        $series[$part_of][] = $work;
                    } else {
                        $series[$part_of] = array($work);
                    }
                } else {
                    $works[] = $work;
                }
            }
            $view->series = $series;
        } else {
            $works = $allWorks;
        }
        $view->works = $works;
    }

    public function hookPublicHead($args)
    {
        $this->_langRedirect();
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
        if (! $itemIds) {
            return;
        }

        $db = $this->_db;
        $nameEls = array('Creator', 'Contributor');
        $ccIds = array();
        foreach ($nameEls as $element) {
            $ccIds[$element] = $db->getTable('Element')
                ->findByElementSetNameAndElementName('Dublin Core', $element)
                ->id;
        }

        $itemTable = $db->getTable('Item');
        $person = $itemTable->getSelect();
        $person->reset('columns');
        $person->columns('id', 'items');
        $person->joinLeft(
            array('name' => $db->ElementTexts),
            'items.id = name.record_id',
            ''
        );
        $person->joinInner(
            array('et_cc' => $db->ElementText),
            "et_cc.element_id IN ({$ccIds["Creator"]}, {$ccIds["Contributor"]})
             AND et_cc.record_type = 'Item' AND et_cc.text = name.text",
            ''
        );
        $person->where('items.id IN (?)', $itemIds);
        $person->group(array('items.id', 'et_cc.record_id'));

        $select = $db->select()
            ->from(array('items' => $db->Item), '');
        $select->joinInner(
            array('p' => $person),
            'p.id = items.id',
            array('p.id', 'works_count' => 'COUNT(*)')
        );
        $select->group('id');
        $rows = $db->fetchAll($select);

        $counts = array();
        foreach ($rows as $row) {
            $counts[$row['id']] = $row['works_count'];
        }

        $args['view']->counts = $counts;
    }

    /**
     * Add markup to exhibit items to show book locations.
     */
    public function filterExhibitAttachmentMarkup($html, $args)
    {
        $item = $args["attachment"]->Item;
        $title = metadata($item, array('Dublin Core', 'Title'));
        $date = metadata($item, array('Dublin Core', 'Date Created'));
        $page = metadata($item, array('Item Type Metadata', 'Page'));
        $figure = metadata($item, array('Item Type Metadata', 'Figure'));
        $plate = metadata($item, array('Item Type Metadata', 'Plate'));
        $embed = metadata($item, array('Item Type Metadata', 'Embed'));

        $has_book_image = $figure || $plate;
        $repl = $has_book_image ? '<div class="marker"></div>' : '';
        $repl .= $embed ? '<div class="video marker"></div>' : '';
        $repl .= '<div class="overlay"><div class="text">';
        $repl .= "<p>$title" . ($date ? " ($date)" : '') . '</p>';
        $repl .= $page ? '<p>'.__('Page')." $page</p>" : '';
        $repl .= "</div></div></a>";
        $html = preg_replace('|</a>$|', $repl, $html, 1);
        return $html;
    }

    /**
     * Change collections/show browse query to sort by Last Name.
     * Make items/browse only show Still and Moving Images.
     *
     * @param Array $params
     * @return Array
     */
    public function filterItemsBrowseParams($params)
    {
        if (isset($params['collection'])) {
            $params['sort_field'] = 'Item Type Metadata,Last Name';
        } elseif (empty($params['query'])) {
            $params['type'] = array('Still Image', 'Moving Image');
        }
        return $params;
    }

    /**
     * Remove items/search link from items/browse.
     *
     * @param Array $navArray
     * @return Array
     */
    public function filterPublicNavigationItems($navArray)
    {
        for ($i = 0; $i < count($navArray); $i++) {
            if (preg_match('|items/search$|', $navArray[$i]['uri'])) {
                unset($navArray[$i]);
            }
        }
        return $navArray;
    }

     /**
     * Remove About links for the other language from the navigation.
     *
     * @param Array $navPages
     * @return Array $navPages
     *
     */
    public function filterPublicNavigationMainAll($navPages)
    {
        $locale = Zend_Registry::get('Zend_Locale');
        if ($locale == 'zh_CN') {
            foreach ($navPages as $key => &$page) {
                if (isset($page->label)) {
                    $navPages[$key]->label = __($page->label);
                } elseif (isset($page['label'])) {
                    $navPages[$key]['label'] = __($page['label']);
                }
            }
        }
        return $navPages;
    }

    /**
     * Add translated text to search text used in site searches.
     * This is done by temporarily adding the translations to the end of the
     * element text before the afterSave hook calls addSearchText on them.
     *
     * @param ElementText[] $elementTexts argument array
     * @return array
     */
    public function filterSearchElementTexts($elementTexts)
    {
        $db = $this->_db;
        // todo just get from ContentLanguages instead
        $translations = $db->getTable('MultilanguageTranslation');
        $sql = "SELECT locale_code FROM $db->MultilanguageTranslations
                GROUP BY locale_code";
        $locales = $db->fetchCol($sql);
        foreach ($elementTexts as $et) {
            foreach ($locales as $locale) {
                $text = $et->getText();
                $translationRecord = $translations->getTranslation(
                    $et->record_id,
                    $et->record_type,
                    $et->element_id,
                    $locale,
                    $text
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
     * Sort Item browsing results by Title by default.
     *
     * @param Array $sortArray
     * @return Array
     */
    public function filterItemsBrowseDefaultSort($sortArray)
    {
        return array('Dublin Core,Title', 'ASC');
    }

    /**
     * Order by Titles with certain html tags and characters removed.
     *
     * @param Array $args
     */
    public function hookItemsBrowseSql($args)
    {
        $select = $args['select'];
        $params = $args['params'];

        if (isset($params['collection'])) {
            return;
        }

        if (isset($params['sort_field']) && $params['sort_field'] === 'Dublin Core,Title') {
            $orders = $select->getPart('order');
            $orders[1][0] = new Zend_Db_Expr(
                "REPLACE(REPLACE(REPLACE(et_sort.text,
                    '<i>', ''),
                    '<em>', ''),
                    '“', '')"
            );
            $select->reset('order');
            foreach ($orders as $order) {
                $select->order($order[0].' '.$order[1]);
            }
        }
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
        if (! is_admin_theme()) {
            $action = url('items/browse');
        }
        return $action;
    }

    /**
     * Change the name of the query input to search so that items/browse
     * will function as a search page.
     */
    public function filterSearchForm($form, $args)
    {
        if (! is_admin_theme()) {
            $form = preg_replace('/query/', 'search', $form, 1);
        }
        return $form;
    }

}
