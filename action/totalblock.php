<?php
/**
 * Approve Plus
 * 
 * Requires: Approve-Plugin, Sqlite-Plugin
 *
 * @author: Gero Gothe <practical@medizin-lernen.de>
 * 
 */


// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

define('SUM_BLOCKED', 'blocked');
define('SUM_UNBLOCKED', 'unblocked');

/**
 * Class action_plugin_approveplus_draftblock
 */
class action_plugin_approveplus_totalblock extends DokuWiki_Action_Plugin {

    /**
     * Register callbacks
     */
    public function register(Doku_Event_Handler $controller) {

        $list = plugin_list();

        if (in_array('approve',$list)) { # Hooks nur eintragen, wenn das Approve-Plugin ebenfalls installiert ist
            $controller->register_hook('TPL_ACT_RENDER', 'BEFORE', $this, 'handle_viewer');
            $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this, 'handle_block');
            $controller->register_hook('MENU_ITEMS_ASSEMBLY', 'AFTER', $this, 'addsvgbutton');
            $controller->register_hook('COMMON_WIKIPAGE_SAVE', 'BEFORE', $this, 'handle_pagesave_before');
        }
    }


    # Ansicht einer Seite blockieren
    function handle_block(Doku_Event $event, $param) {
        global $ID;
        global $INFO;

        if ($event->data == 'show' && isset($_GET['blockpage'])) {
            if (!auth_quickaclcheck($ID) >= AUTH_DELETE) return;
            
            if ($this->blocked($ID)) {
                $summary = SUM_UNBLOCKED;
            } else $summary = SUM_BLOCKED;

            saveWikiText($ID, rawWiki($ID), $summary); # Revision erzeugen            
            header('Location: ?id='.$ID);
            
        }
    }


    # Speichern auch wenn keine inhaltliche Änderung durchgeführt wurde
    public function handle_pagesave_before(Doku_Event $event, $param) {
        global $REV;
        $id = $event->data['id'];
 
        //save page if summary is provided
        if($event->data['summary'] == SUM_BLOCKED || $event->data['summary'] == SUM_UNBLOCKED) {
            $event->data['contentChanged'] = true;
        }
    }


    public function handle_viewer(Doku_Event $event) {
        
        if ($event->data != 'show') return;
        
        $id = $event->data['id'];
        
        if ($this->blocked($id)){
            global $INFO;
            
            # Current user hast editing right -> blocking not applied
            if ($INFO['editable']) {
                echo '<div class="plugin__approveblock_info">' . $this->getLang("msg_blocked2") .'</div>';
                return;
            }
            
            global $auth;
            $a = $auth->getUserData($INFO['editor']);
            echo '<div class="plugin__approveblock_info">' . str_replace("%AUTHOR%",$a['name'],$this->getLang("msg_blocked")) .'</div>';
            $event->preventDefault();
            return;
        }
    }


    # Funktion prüft, ob ein Seite blockiert ist. Modifizierte Funktion aus dem approve-Plugin
    public function blocked($id) {
        global $INFO;
        global $conf;

        if ($INFO['meta']['last_change']['sum'] == SUM_BLOCKED) return true; # current version is blocked
        if ($INFO['meta']['last_change']['sum'] == SUM_UNBLOCKED) return false; # current version is unblocked


        # Search the revisions until a signal is found (block vs unblock)
        $count = 0;

        $changelog = new PageChangeLog($id);
        $first = 0;
        $num = 100;
        while (count($revs = $changelog->getRevisions($first, $num)) > 0) {
            foreach ($revs as $rev) {
                $revInfo = $changelog->getRevisionInfo($rev);
                if ($revInfo['sum'] == SUM_UNBLOCKED) {
                    return false; # a block was removed
                }                
                if ($revInfo['sum'] == SUM_BLOCKED) return true; # blockieren, da vorher keine freigegebene Fassung
                
            }
            $first += $num;
        }
        
        return $false;
    }
    
    public function addsvgbutton(Doku_Event $event) {
        global $INFO;
        
        if (!$INFO['editable']) return;
        
        if($event->data['view'] != 'page') { # || !$this->getConf('showexportbutton')) {
            return;
        }

        if(!$INFO['exists']) {
            return;
        }

        array_splice($event->data['items'], -1, 0, [new \dokuwiki\plugin\approveplus\MenuItem()]);
    }


}

//Setup VIM: ex: et ts=4 enc=utf-8 :
