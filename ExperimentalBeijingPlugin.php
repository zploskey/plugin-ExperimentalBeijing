<?php

class ExperimentalBeijing extends Omeka_Plugin_AbstractPlugin
{
    protected $plugin_prefix = 'ebj_';
    protected $prefix;

    protected $_hooks = array(
        'install',
        'uninstall',
        'initialize',
    );

    public function __construct()
    {
        parent::__construct();
        $this->prefix = $db->prefix . $this->plugin_prefix;
    }

    public function hookInstall()
    {
        $db = $this->_db;

//        $db->query("
//            CREATE TABLE IF NOT EXISTS `{$this->prefix}work`"
//        );
    }

    public function hookUninstall()
    {
        $db = $this->_db;
//        $db->query("DROP TABLE IF EXISTS `{$this->prefix}work;");

    }

    /*
     * Insert our custom item types.
     */
    public function hookInitialize()
    {
        $db = $this->_db;
//        $db->query("INSERT INTO `{$this->prefix}work");
    }
}
