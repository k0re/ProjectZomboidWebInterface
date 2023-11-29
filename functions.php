<?php
require ('config.inc.php');
global $mysql_host, $mysql_user, $mysql_pass, $mysql_db, $rcon_host, $rcon_port, $rcon_pass;
use Jelix\IniFile\IniException;
use Jelix\IniFile\IniModifier;
use Jelix\IniFile\IniModifierInterface;

$db = new mysqli($mysql_host, $mysql_user, $mysql_pass, $mysql_db);
$ini = new IniModifier('/home/pzserver/Zomboid/Server/pzserver.ini');

function get_mods_from() {
    global $db;
    $mods = array();
    $result = $db->query("SELECT * FROM workshop_id");
    while($obj = $result->fetch_object()){
        $mods[$obj->id]['list'] = array();
        $mods[$obj->id]['name'] = $obj->title;
        $mods[$obj->id]['active'] = $obj->active;
        $result2 = $db->query("SELECT * FROM mod_id WHERE workshop_id=".$obj->id);
        $mods[$obj->id]['list'] = array();
        while($obj2 = $result2->fetch_object()){
            $mods[$obj->id]['list'][$obj2->id]['name'] = $obj2->mod_id;
            $mods[$obj->id]['list'][$obj2->id]['active'] = $obj2->active;
        }
    }
    return $mods;
}
function get_workshop_item_for_id($id) {
    return get_workshop_item_for_array(array($id));
}
function get_workshop_item_for_array($arr) {

    $ch = curl_init();
    $post = "itemcount=".count($arr)."&";
    for ($i = 0; $i < count($arr); $i++) {
        $post .= '&publishedfileids['.$i.']='.$arr[$i];
    }

    curl_setopt($ch, CURLOPT_URL,"https://api.steampowered.com/ISteamRemoteStorage/GetPublishedFileDetails/v1/");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $server_output = curl_exec($ch);

    curl_close($ch);

    $resp = json_decode($server_output);
    $matches = array();
    $matches2 = array();
    $desc = $resp->response->publishedfiledetails[0]->description;
    $desc = preg_replace('/\[\/(.*?)\]|\[(.*?)]/', "", $desc);
    preg_match_all('/Workshop ID: \d+\n/', $desc, $matches);
    preg_match_all('/Mod ID: (.*?)(\n|$)/', $desc, $matches2);
    $real_matches = array();
    foreach ($matches2[0] as $m) {
        $l = trim(str_replace("Mod ID: ","",$m));
        if (!in_array($l, $real_matches)) $real_matches[] = $l;
    }
    $ret = array();
    $ret['id'] = $resp->response->publishedfiledetails[0]->publishedfileid;
    $ret['title'] = $resp->response->publishedfiledetails[0]->title;
    $ret['wsid'] = trim(str_replace("Workshop ID: ","",$matches[0][0]));
    $ret['modids'] = $real_matches;
    return (object) $ret;
}
function insert_wsitem($id) {
    global $db;
    $wsitem = get_workshop_item_for_id($id);
    $db->query("DELETE FROM workshop_id WHERE id='".$wsitem->id."'");
    $db->query("INSERT INTO workshop_id (id, title, active) VALUES ('".$wsitem->id."','".addslashes($wsitem->title)."',0)");
    $db->query("DELETE FROM mod_id WHERE workshop_id='".$wsitem->id."'");
    foreach ($wsitem->modids as $mod) {
        $db->query("INSERT INTO mod_id (workshop_id, mod_id, active) VALUES ('".$wsitem->id."','".addslashes($mod)."',0)");
    }
}

/**
 * @throws IniException
 */
function delete_wsitem($id) {
    global $db;
    $db->query("DELETE FROM workshop_id WHERE id='".$id."'");
    $db->query("DELETE FROM mod_id (workshop_id, mod_id, active) WHERE workshop_id='".$id."'");
    sync_to_file();
}

/**
 * @throws IniException
 */
function deactivate_module($id) {
    global $db;
    $db->query("UPDATE mod_id set active=false WHERE id = ".addslashes($id));
    sync_to_file();
}

/**
 * @throws IniException
 */
function deactivate_item($id) {
    global $db;
    $db->query("UPDATE workshop_id set active=false WHERE id='".addslashes($id)."'");
    $db->query("UPDATE mod_id set active=false WHERE workshop_id='".addslashes($id)."'");
    sync_to_file();
}

/**
 * @throws IniException
 */
function activate_module($id) {
    global $db;
    $db->query("UPDATE mod_id set active=true WHERE id='".addslashes($id)."'");
    $wsid = $db->query("SELECT * FROM mod_id WHERE id=".$id)->fetch_object()->workshop_id;
    $db->query("UPDATE workshop_id set active=true WHERE id = ".$wsid);
    sync_to_file();
}

/**
 * @throws IniException
 */
function activate_item($id) {
    global $db;
    $db->query("UPDATE workshop_id set active=true WHERE id='".$id."'");
    $db->query("UPDATE mod_id set active=true WHERE workshop_id='".$id."'");
    sync_to_file();
}
function sync_from_steam() {
    global $db;
    $result = $db->query("SELECT * FROM workshop_id");
    while($obj = $result->fetch_object()){
        $wsitem = get_workshop_item_for_id($obj->id);
        $db->query("UPDATE workshop_id set title='".addslashes($wsitem->title)."' WHERE id='".$wsitem->id."'");
        foreach ($wsitem->modids as $mod) {
            $check = $db->query("SELECT * FROM mod_id WHERE workshop_id='' AND mod_id=''");
            if ($check->num_rows > 0) {
                $db->query("UPDATE mod_id SET workshop_id='".$wsitem->id."', mod_id='".addslashes($mod)."' WHERE id=".$check->id);
            } else {
                $db->query("INSERT INTO mod_id (workshop_id, mod_id, active) VALUES ('".$wsitem->id."','".addslashes($mod)."',1)");
            }

        }
    }
}

/**
 * @throws IniException
 */
function sync_to_file() {
    global $db, $ini;
    $mods = array();
    $result = $db->query("SELECT * FROM workshop_id WHERE active=true");
    while($obj = $result->fetch_object()){
        $mods[$obj->id] = array();
        $result2 = $db->query("SELECT * FROM mod_id WHERE active=true AND workshop_id=".$obj->id);
        while($obj2 = $result2->fetch_object()){
            $mods[$obj->id][] = $obj2->mod_id;
        }
    }
    $idslist = "";
    $modslist = "";
    foreach ($mods as $id=>$mod) {
        $idslist .= $id.";";
        foreach ($mod as $modname) {
            $modslist .= $modname.";";
        }
    }
    $idslist = rtrim($idslist, ";");
    $modslist = rtrim($modslist, ";");
    $ini->setValue('Mods', $modslist);
    $ini->setValue('WorkshopItems', $idslist);
    $ini->save(null, IniModifierInterface::FORMAT_NO_QUOTES);
}

function runServerCommand($cmd)
{
    $restartcommand = 'sudo -H -u pzserver /home/pzserver/webserver restart';
    $startcommand = 'sudo -H -u pzserver /home/pzserver/webserver start';
    $stopcommand = 'sudo -H -u pzserver /home/pzserver/webserver stop';
    $updatecommand = 'sudo -H -u pzserver /home/pzserver/webserver update';
    switch($cmd) {
        case "start":
            shell_exec($startcommand);
            break;
        case "stop":
            shell_exec($stopcommand);
            break;
        case "restart":
            shell_exec($restartcommand);
            break;
        case "update":
            shell_exec($updatecommand);
            break;
        case "updatewebif":
            shell_exec("cd /var/www/pz/ && git pull");
            break;
        case "save":
            saveMap();
            break;
    }
}
function checkTCP($host="localhost",$port=27115){
    $connection = @fsockopen($host, $port);

    if (is_resource($connection))
    {
        fclose($connection);
        return true;
    }
    else
    {
        return false;
    }
}
function renderServerStatus() {
    if (checkTCP()) {
        echo '<span class="fa-solid fa-lock-open" style="color: #6ecb35"></span> Online';
    } else {
        echo '<span class="fa-solid fa-lock-open" style="color: #dc3545"></span> Offline';
    }
}
function saveMap() {
    global $rcon_host, $rcon_port, $rcon_pass;
    $ret = shell_exec("rcon -a ".$rcon_host.":".$rcon_port." -p ".$rcon_pass." save");
}
function getCurrentPlayerCount() {
    global $rcon_host, $rcon_port, $rcon_pass;
    $retval = null;
    $output = null;
    exec("rcon -a ".$rcon_host.":".$rcon_port." -p ".$rcon_pass." players", $output, $retval);
    $matches = array();
    preg_match("/\d+/",$output[0], $matches, PREG_OFFSET_CAPTURE);
    return $matches[0][0];
}
