<?php

namespace dokuwiki\plugin\approveplus;

use dokuwiki\Menu\Item\AbstractItem;

/**
 * Class MenuItem
 *
 * Implements the block button for DokuWiki's menu system
 *
 */
class MenuItem extends AbstractItem {
    /** @var string do action for this plugin */
    protected $type = '';

    /** @var string icon file */
    protected $svg = __DIR__ . '/img/blockpage.svg';
    
    public function __construct() {
        parent::__construct();
        global $INFO;
        
        unset($this->params['do']);
        $this->params['blockpage']="blockpage";
        
        $hlp = plugin_load('action','approveplus_totalblock');
        if ($hlp->blocked($INFO['id'])) $this->svg = __DIR__ . '/img/unblock.svg';
    }

    
    /**
     * Get label from plugin language file
     *
     * @return string
     */
    public function getLabel() {
        global $INFO;
        
        $hlp = plugin_load('action','approveplus_totalblock');
        
        if ($hlp->blocked($INFO['id'])) return $hlp->getLang('unblock_button');
        return $hlp->getLang('block_button');
    }
}
