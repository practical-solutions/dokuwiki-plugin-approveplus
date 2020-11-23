<?php
/**
 * Approve Plus
 * 
 * Admin Plugin for renaming all pages in a namespace
 * 
 * Parts of the code from the approve plugin by Szymon Olewniczak
 *
 * @license    GPL2
 * @author     Gero Gothe <practical@medizin-lernen.de>
 */
 
class admin_plugin_approveplus extends DokuWiki_Admin_Plugin {
     
    private $approve_inst = false;

    function __construct() {
        $list = plugin_list();
        if(in_array('approve',$list)) {
            $this->approve_inst = true;
        }
    }
    
    function getMenuText($language){
        return $this->getLang("admin title");
    }
    
    function forAdminOnly(){
        return true;
    }


    function handle() {

        if (!$this->approve_inst) return;
        if (!isset($_REQUEST)) return;
     
        $com = array_keys($_REQUEST);

        $list = Array();
        foreach ($com as $c) {
            if (strpos($c,"CHECK-") === 0) {
                $id = substr($c,6);
                $list[] = $id;
            }
        }
        
        if (count($list)>0) $r = $this->handle_approve($list);
        
        if ($r===false) msg("Fail prerequisits to approve.",-1);

    }
     
    /**
    * output appropriate html
    */
    function html() {
        global $ID;
        global $conf;
        global $auth;

        echo '<form action="'.wl($ID).'" method="post">';
        
        # output hidden values to ensure dokuwiki will return back to this plugin
        echo '<input type="hidden" name="do"   value="admin" />';
        echo '<input type="hidden" name="page" value="'.$this->getPluginName().'" />';

 
        echo '<h1>'.$this->getLang("admin title").'</h1>';
        echo '<hr>';
        

        if (!$this->approve_inst) {
            echo "<a target='_blank' href='https://www.dokuwiki.org/plugin:approve'>Approve plugin</a> ist not installed.";
            return;
        }
        
        echo "<input type='submit' value='".$this->getLang('admin button')."'><br><br>";
        
        $ns = getNS($ID);
        $data = $this->getList($ns);
        
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
        
        echo "<table><tr><th>".$this->getLang('table document')."</th><th>".$this->getLang('table current ver')."</th><th>".$this->getLang('table current date')."</th><th>".$this->getLang('table approve date')."</th><th>".$this->getLang('table approver')."</th><th>".$this->getLang('table diff')."</th><th>".$this->getLang('table approve')." (".$this->getLang('table all').": <input onclick='toggle(this);' id='select-all' type='checkbox'>)</th></tr>";
        
        foreach ($data as $d) {

            $last_approved_rev = $helper->find_last_approved($sqlite, $d['id']);
            if (!$last_approved_rev) $last_approved_rev = -1;
            $last_change_date = @filemtime(wikiFN($d['id']));
            

            if ($last_approved_rev > -1) {
                $res = $sqlite->query('SELECT approved, approved_by
                                       FROM revision
                                       WHERE page=? AND rev=?', $d['id'], $last_approved_rev);

                $approve = $sqlite->res_fetch_assoc($res);
                $approve['approved_by'] = $auth->getUserData($approve['approved_by'])['name'];
            } else {
                $approve = Array('approved' => '-', 'approved_by' => '-');
            }
            
            
            echo '<tr style="background-color:'.($last_change_date>$last_approved_rev? "pink":"linen").'">';
            echo '<td><a href="'.wl($d['id']).'" target="_blank">'.$d['id'].'</a></td>';
            echo '<td>'.($last_change_date>$last_approved_rev? "not approved":"&check;").'</td>';
            
            echo "<td>$last_change_date</td>";
            echo "<td>$last_approved_rev</td>";
            
            echo '<td>'.$approve['approved_by'].'</td>';
            
            
            # Differences
            # ------------
            echo '<td>';
            if ($last_change_date>$last_approved_rev && $last_approved_rev > -1) {
                
                $new = rawwiki($d['id']);
                $old = rawwiki($d['id'],$last_approved_rev);
                
                if ($new==$old) {echo "&cross;";} else echo '<a href="'.DOKU_URL.'doku.php?id='.$d['id'].'&rev='.$last_approved_rev.'&do=diff" target="blank">show</a>';

            } else echo '-';
            echo '</td>';
            
            
            # Approve Checkbox
            # -----------------
            echo '<td>';
            if ($last_change_date>$last_approved_rev) {
                echo '<input type="checkbox" name="CHECK-'.$d['id'].'" >';
            } else {
                echo "-";
            }
            echo '</td>';
            echo '</tr>';
        }
        
        echo "</table>";
        
        ptln("</form>");
        
        ?>
        <script type="text/javascript">
        function toggle(source) {
        var checkboxes = document.querySelectorAll('input[type="checkbox"]');
        for (var i = 0; i < checkboxes.length; i++) {
            if (checkboxes[i] != source)
            checkboxes[i].checked = source.checked;
            }
        }
        </script>
        <?php
    }


    # Get all documents recursively
    # Derived from the nsindex plugin by Oliver Geisen
    private function getList($ns){
        global $conf;
        $res = Array();
        $data = array();
        $nsdir = str_replace(':','/',$ns);
        search($data,$conf['datadir'],'search_index',array(),$nsdir,1);
        foreach ($data as $d){
            if ($d['type'] == "f") {$res[]=$d;} else {
                $l = $this->getList($d['id']);
                $res = array_merge($l,$res);
            }
        }
        return $res;
    }
    
    
    # Derived from the approve plugin
    private function handle_approve($list) {
        global $INFO;
        global $auth;
        
        # Check again: Access ONLY for admins!
        if (!in_array('admin',$auth->getUserData($INFO['client'])['grps'])) return false;

        try {
            /** @var \helper_plugin_approve_db $db_helper */
            $db_helper = plugin_load('helper', 'approve_db');
            $sqlite = $db_helper->getDB();
        } catch (Exception $e) {
            msg($e->getMessage(), -1);
            return false;
        }
        /** @var helper_plugin_approve $helper */
        $helper = plugin_load('helper', 'approve');

        foreach ($list as $id) {
            $res = $sqlite->query('SELECT MAX(version)+1 FROM revision
                                            WHERE page=?', $id);
            $next_version = $sqlite->res2single($res);
            if (!$next_version) {
                $next_version = 1;
            }
            //approved IS NULL prevents from overriding already approved page
            $sqlite->query('UPDATE revision
                            SET approved=?, approved_by=?, version=?
                            WHERE page=? AND current=1 AND approved IS NULL',
                            date('c'), $INFO['client'], $next_version, $id);
        }

        return true;
    }
     
}

