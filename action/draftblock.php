<?php
/**
 * Approve Plus
 * 
 * Requires: Approve-Plugin, Sqlite-Plugin
 * 
 * @license: GPL2
 * @author: Gero Gothe <practical@medizin-lernen.de>
 * 
 */


// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();



/**
 * Class action_plugin_approveplus_draftblock
 */
class action_plugin_approveplus_draftblock extends DokuWiki_Action_Plugin {

    /**
     * Register callbacks
     */
    public function register(Doku_Event_Handler $controller) {

        $list = plugin_list();

        if (in_array('approve',$list) && $this->getConf('block_unapproved')==1) { # Hooks nur eintragen, wenn das Approve-Plugin ebenfalls installiert ist	
            $controller->register_hook('TPL_ACT_RENDER', 'BEFORE', $this, 'handle_viewer');
        }
    }


    /**
     * Based on the original function from the approve-plugin
     * 
     * Block pages, which have not been approved yet
     *
     * @param Doku_Event $event
     */
    public function handle_viewer(Doku_Event $event) {
        global $INFO;

        try {
            /** @var \helper_plugin_approve_db $db_helper */
            $db_helper = plugin_load('helper', 'approve_db');
            $sqlite = $db_helper->getDB();
        } catch (Exception $e) {
            msg($e->getMessage(), -1);
            return;
        }
        /** @var helper_plugin_approve $helper */
        $helper = plugin_load('helper', 'approve');

        if ($event->data != 'show') return;
        //apply only to current page
        if ($INFO['rev'] != 0) return;
        if (!$helper->use_approve_here($sqlite, $INFO['id'], $approver)) return;
        if ($helper->client_can_see_drafts($INFO['id'], $approver)) return;

        $last_approved_rev = $helper->find_last_approved($sqlite, $INFO['id']);
        //no page is approved
        if (!$last_approved_rev) {
            global $auth;
            $a = $auth->getUserData($INFO['editor']);
            echo '<div class="plugin__approveblock_info">' . str_replace("%AUTHOR%",$a['name'],$this->getLang("none_approved")) .'</div>';
            $event->preventDefault();
            return;
        }

        #$last_change_date = @filemtime(wikiFN($INFO['id']));
        //current page is approved
        #if ($last_approved_rev == $last_change_date) return;

        #header("Location: " . wl($INFO['id'], ['rev' => $last_approved_rev], false, '&'));
    }


}

//Setup VIM: ex: et ts=4 enc=utf-8 :
