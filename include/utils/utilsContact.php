<?php

function setContactData($userid, $data_cd, $data_csd, $data_ca, $description)
{

    $data = getChildRoleDetail($userid);
    $profileid = $data['profileid'];

    if ($profileid == 2) {
        $contactid = contactUpsertStatus($userid);
        $roleid = $data['roleid'];
        $parentrole = $data['parentrole'];

        $clientid = getClientID($roleid, $parentrole);
        if ($clientid != 0) {
            if ($contactid == '' || $contactid == 0)
                createContact($clientid, $userid, $data_cd, $data_csd, $data_ca, $description);
            else
                updateContact($userid, $contactid, $data_cd, $data_csd, $data_ca, $description);
        }
    }
}

function createContact($clientid, $userid, $data_cd, $data_csd, $data_ca, $description){
    global $adb, $current_user;
    $current_user = $current_user->id;
    $module = 'Contacts';
    $parent_module = 'Accounts';

    $currentdatetime = date("Y-m-d H:i:s");
    $crmid = $adb->getUniqueID("vtiger_crmentity");
    $contact_no = getEntityNum($module);
    insertCRMEntity($crmid, $userid, $current_user, $module, $currentdatetime, $contact_no, $description);

    $entity_values = array(
        'contact_no'=> $contact_no,
        'assigned_user_id'=>$userid,
        'createdtime'=>$currentdatetime,
        'modifiedby'=>$current_user,
        'record_id'=>$crmid,
        'record_module'=>$module
    );

    $all_values = array_merge($entity_values, $data_cd,  $data_csd, $data_ca);

    $firstname = $data_cd['firstname'];
    $last_name = $data_cd['lastname'];
    $mobile = $data_cd['mobile'];
    $title = $data_cd['title'];
    $email1 = $data_cd['email'];
    $secondaryemail = $data_cd['secondaryemail'];

    $homephone = $data_cd['homephone'];

    $mailingstreet = $data_ca['mailingstreet'];
    $mailingcity = $data_ca['mailingcity'];
    $mailingstate = $data_ca['mailingstate'];
    $mailingzip = $data_ca['mailingzip'];
    $mailingcountry = $data_ca['mailingcountry'];

    $query_cond = "INSERT INTO vtiger_contactdetails
                  (contactid, contact_no, firstname, lastname, mobile, title, email, secondaryemail, accountid)
                  VALUES(?,?,?,?,?,?,?,?,?)";
    $param = array($crmid, $contact_no, $firstname, $last_name, $mobile, $title, $email1, $secondaryemail, $clientid);
    $adb->pquery($query_cond, $param);

    $query_concsd = "INSERT INTO vtiger_contactsubdetails (contactsubscriptionid,homephone) VALUES(?,?)";
    $param = array($crmid, $homephone);
    $adb->pquery($query_concsd, $param);

    $query_concf = "INSERT INTO vtiger_contactscf (contactid) VALUES(?)";
    $param = array($crmid);
    $adb->pquery($query_concf, $param);

    $query_custd = "INSERT INTO vtiger_customerdetails (customerid) VALUES(?)";
    $param = array($crmid);
    $adb->pquery($query_custd, $param);

    $query_concd = "INSERT INTO vtiger_contactaddress (contactaddressid, mailingstreet, mailingcity, mailingstate, mailingzip, mailingcountry)
                    VALUES(?,?,?,?,?,?)";
    $param = array($crmid, $mailingstreet, $mailingcity, $mailingstate, $mailingzip, $mailingcountry);
    $adb->pquery($query_concd, $param);

    $mod_status = 2;
    $basicid = setModtrackerBasic($module, $crmid, $current_user, $mod_status);
    setModtrackerDetails($basicid, $all_values);
    setCRMEntityRel($clientid, $parent_module, $crmid, $module);
}

function contactUpsertStatus($userid)
{
    global $adb;
    $contactid = 0;
    $query = "SELECT con.`contactid` FROM vtiger_contactdetails con
            INNER JOIN vtiger_crmentity crm ON crm.`crmid` = con.`contactid`
            WHERE crm.`deleted` = 0 AND crm.smownerid = $userid";
    $sqlQuery = $adb->query($query);
    if ($adb->num_rows($sqlQuery)> 0) {
        $result = $adb->fetch_row($sqlQuery);
        $contactid = $result['contactid'];
    }
    return $contactid;
}

function getChildRoleDetail($userid)
{
    global $adb;
    $data = array();
    $query = "SELECT r2p.profileid, u2r.`roleid`, r.parentrole FROM vtiger_users u
            INNER JOIN vtiger_user2role u2r ON u2r.`userid` = u.id
            INNER JOIN vtiger_role2profile r2p ON r2p.roleid = u2r.`roleid`
            INNER JOIN vtiger_role r ON r.roleid  = u2r.`roleid`
            WHERE u.status = 'Active' AND u.id = $userid";
    $sqlQuery = $adb->query($query);
    if ($adb->num_rows($sqlQuery)> 0) {
        $result = $adb->fetch_row($sqlQuery);
        $profileid = $result['profileid'];
        $roleid = $result['roleid'];
        $parentrole = $result['parentrole'];
        $data['profileid'] = $profileid;
        $data['roleid'] = $roleid;
        $data['parentrole'] = $parentrole;
    }
    return $data;
}

function getClientID($roleid, $parentrole)
{
    global $adb;
    $clientid = 0;
    $query = "SELECT crm.`crmid` FROM
                vtiger_role r
                INNER JOIN vtiger_user2role u2r ON u2r.`roleid` = r.`roleid`
                INNER JOIN vtiger_crmentity crm ON crm.`smownerid` = u2r.userid
                WHERE crm.`deleted` = 0 AND r.`roleid` <> '$roleid' AND r.parentrole LIKE '$parentrole%' LIMIT 1";
    $sqlQuery = $adb->query($query);
    if ($adb->num_rows($sqlQuery)> 0) {
        $result = $adb->fetch_row($sqlQuery);
        $clientid = $result['crmid'];
    }
    return $clientid;
}

function updateContact($userid, $contactid, $data_cd, $data_csd, $data_ca, $description)
{
    global $adb, $current_user;
    $loggedin_user = $current_user->id;
    $all_values = array_merge($data_cd, $data_csd, $data_ca);
    $module = 'Contacts';
    $mod_status = 0;
    $basic_id = 0;
    $currentdatetime = date("Y-m-d H:i:s");
    $update_values = '';
    $insert_mod_values = '';
    $sql = "SELECT
            con.firstname,
            con.lastname,
            con.mobile,
            con.title,
            con.email,
            con.secondaryemail,
            consd.homephone,
            conad.mailingstreet,
            conad.mailingcity,
            conad.mailingstate,
            conad.mailingzip,
            conad.mailingcountry
            FROM vtiger_contactdetails con
            INNER JOIN vtiger_crmentity crm ON crm.`crmid` = con.`contactid`
            INNER JOIN vtiger_contactsubdetails consd ON consd.`contactsubscriptionid` = con.`contactid`
            INNER JOIN vtiger_contactaddress conad ON conad.`contactaddressid` = con.`contactid`
            WHERE crm.`deleted` = 0 AND con.`contactid` =? ";

    $param = array($contactid);
    $fld_result = $adb->pquery($sql, $param);
    if ($adb->num_rows($fld_result) > 0) {
        $store_row = $adb->fetchByAssoc($fld_result);
        $counter = 0;
        foreach ($all_values as $key => $row) {
            if (vtlib_purify($store_row[$key]) != $row) {
                $trim_row = trim($row);
                if($counter == 0)
                    $basic_id = setModtrackerBasic($module, $contactid, $current_user, $mod_status);
                if ($trim_row != '' && $trim_row != '0.0000' && $trim_row != null && !empty($trim_row)) {
                    $pre_value = $store_row[$key];
                    if ($counter == 0) {
                        $update_values = "$key = '$row'";
                        $insert_mod_values = "('$basic_id', '$key', '$pre_value', '$row')";
                    } else {
                        $update_values .= ", $key = '$row'";
                        $insert_mod_values .= ", ('$basic_id', '$key', '$pre_value', '$row')";
                    }
                    $counter++;
                }
            }
        }
    }


    if ($update_values != '') {
        $update_values .= ", smownerid = '$userid', modifiedby = '$userid', modifiedtime = '$currentdatetime'";
        $insert_mod_values .= ", ('$basic_id', 'modifiedby', '$loggedin_user', '$loggedin_user')";
        $sql = "UPDATE vtiger_contactdetails
                INNER JOIN vtiger_crmentity ON vtiger_crmentity.`crmid` = vtiger_contactdetails.`contactid`
                INNER JOIN vtiger_contactsubdetails ON vtiger_contactsubdetails.`contactsubscriptionid` = vtiger_contactdetails.`contactid`
                INNER JOIN vtiger_contactaddress ON vtiger_contactaddress.`contactaddressid` = vtiger_contactdetails.`contactid`
                SET $update_values
                WHERE vtiger_crmentity.`deleted` = 0 AND vtiger_contactdetails.`contactid` = $contactid";
        $adb->query($sql);

        $sql = "INSERT INTO vtiger_modtracker_detail(id,fieldname,prevalue, postvalue) VALUES $insert_mod_values";
        $adb->query($sql);
    }
}