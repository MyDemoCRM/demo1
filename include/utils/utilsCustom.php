<?php
require_once('include/database/PearDatabase.php');
require_once('include/utils/utils.php'); //new

function getEntityNum($module) {
    global $adb;
    $query_num = $adb->query("select prefix, cur_id from vtiger_modentity_num where semodule='$module' and active = 1");
    $result_num = $adb->fetch_array($query_num);
    $prefix = $result_num['prefix'];
    $cur_id = $result_num['cur_id'];
    $entity_num = $prefix.$cur_id;
    $next_curr_id = $cur_id + 1;
    $adb->query("update vtiger_modentity_num set cur_id = ".$next_curr_id." where semodule='$module' and active = 1");
    return $entity_num;
}

function insertCRMEntity($crmid, $userid, $current_user, $module, $currentdatetime, $entity_no, $description = ''){
    global $adb;
    $source = 'CRM';
    $smgroupid = 0;
    $query_crm = "INSERT INTO vtiger_crmentity
                  (crmid, smcreatorid, smownerid, modifiedby , setype, createdtime, modifiedtime, description, smgroupid, source, label)
                  VALUES (?,?,?,?,?,?,?,?,?,?,?)";
    $param = array($crmid, $current_user, $userid, $current_user, $module, $currentdatetime, $currentdatetime, $description, $smgroupid, $source, $entity_no);
    $adb->pquery($query_crm, $param);
}

function updateCRMEntity($crmid, $userid, $currentdatetime){
    global $adb;
    $query_crm = "UPDATE vtiger_crmentity SET
                  smownerid =?, modifiedby =?, modifiedtime =? WHERE crmid =?";
    $param = array($userid, $userid, $currentdatetime, $crmid);
    $adb->pquery($query_crm, $param);
}

function setModtrackerBasic($module, $crmid, $userid, $mod_status){
    global $adb;
    $currentdatetime = date("Y-m-d H:i:s");
    $thisid = $adb->getUniqueId('vtiger_modtracker_basic');
    $sql = "INSERT INTO vtiger_modtracker_basic
                (id, crmid, module, whodid, changedon, status)
                VALUES(?,?,?,?,?,?)";
    $param = array($thisid, $crmid, $module, $userid, $currentdatetime, $mod_status);
    $adb->pquery($sql, $param);
    return $thisid;
}

function setModtrackerDetails($thisid, $all_values){ // When data insert
    global $adb;
    $counter = 0;
    $values = '';
    foreach($all_values as $key=>$row) {
        if($row != '') {
            if ($counter == 0) {
                $values = "('$thisid', '$key', '$row')";
            } else {
                $values .= ", ('$thisid', '$key', '$row')";
            }
        }
        $counter++;
    }
    $sql = "INSERT INTO vtiger_modtracker_detail
                    (id, fieldname, postvalue)
                    VALUES $values";
    $adb->query($sql);
}

function setCRMEntityRel($parent_id, $parent_module, $crmid, $module){
    global $adb;
    $sql = "INSERT INTO vtiger_crmentityrel VALUES (?,?,?,?)";
    $param = array($parent_id, $parent_module, $crmid, $module);
    $adb->pquery($sql, $param);
}