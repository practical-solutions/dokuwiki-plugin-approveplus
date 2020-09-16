<?php
/**
 * Approve Plus
 *
 * @license    MIT
 * @author     Gero Gothe <practical@medizin-lernen.de>
 */


# must be run within Dokuwiki
if(!defined('DOKU_INC')) die();


class action_plugin_approveplus_replacement extends DokuWiki_Action_Plugin {

    /**
     * Register callbacks
     */
    public function register(Doku_Event_Handler $controller) {
		
        $plist = plugin_list();
        
        if (in_array('dw2pdf',$plist) && in_array('approve',$plist)) # Both must be installed
            $controller->register_hook('PLUGIN_DW2PDF_REPLACE', 'BEFORE', $this, 'replacement_before');
				
    }
    
    
    
    function replacement_before(Doku_Event $event, $param) {
		global $INFO;
		
        try {
            /** @var \helper_plugin_approve_db $db_helper */
            $db_helper = plugin_load('helper', 'approve_db');
            $sqlite = $db_helper->getDB();
        } catch (Exception $e) {
            return;
        }
        $last_change_date = @filemtime(wikiFN($INFO['id']));
        $rev = !$INFO['rev'] ? $last_change_date : $INFO['rev'];


        $res = $sqlite->query('SELECT ready_for_approval, ready_for_approval_by,
                                        approved, approved_by, version
                                FROM revision
                                WHERE page=? AND rev=?', $INFO['id'], $rev);

        $approve = $sqlite->res_fetch_assoc($res);
        
        if ($approve['approved']) {
            global $auth;
            $data = $auth->getUserData($approve['approved_by']);
            $event->data['replace']['@APPROVER@'] = $this->getLang('approve_text') . $data['name'];
        } else {
            $event->data['replace']['@APPROVER@'] = $this->getLang('not_approve_text');
        }
	}

}

//Setup VIM: ex: et ts=4 enc=utf-8 :