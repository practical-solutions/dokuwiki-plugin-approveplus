<?php
/**
 * XLS-Converter
 *
 * @author     Gero Gothe <practical@medizin-lernen.de>
 */
 

class admin_plugin_approveplus extends DokuWiki_Admin_Plugin {
     
    function getMenuText(){
        return "Approve: Migrate from Revision-line";
    }

    function forAdminOnly() {
        return true;
    }
     
    function html() {
        
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
        
        
        
        echo "<h1>Migrate</h1>
              The old version of the approve-plugin and the publish-plugin used the revision-line to mark pages as approved or not. The newer approve-plugin uses a sqlite-database. This tool converts approved pages in the old system to an approved-marked page in the sqlite database using the <u>recent-changes-list</u> (in the amount of days is configured to low, this tool will not work).<br><br><hr>";

        // Get global changelog
        global $conf;
        
        # "/var/www/html/dokuwiki/data/meta/_dokuwiki.changes"; 
        $cl = $conf['changelog'];
        
        if(file_exists($cl) && is_readable($cl)) {
            $lines = @file($cl);
        }
        
        echo "Changelog:<br><pre>".($conf['changelog'])."</pre><br><hr>";
        
        $pages = Array();
        
        echo "<pre>";
        foreach ($lines as $l) {
            $t = explode("\t",$l);
            if ($t[5]=="Approved") {
                print_r($l);
                $pages[$t[3]] = intval($t[0]);
            }
        }
        
        print_r($pages);
        echo "</pre>";
    }

}

