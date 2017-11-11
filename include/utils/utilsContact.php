<?php

function setContactData($userid, $request, $user_role_details)
{
    $first_name = $request->get('first_name');
    $last_name = $request->get('last_name');
    //$title = $request->get('title');
    $email1 = $request->get('email1');
    $email2 = $request->get('email2');
    $phone_work = $request->get('phone_work');
    $phone_mobile = $request->get('phone_mobile');
    $description = $request->get('description');
    $address_street = $request->get('address_street');
    $address_city = $request->get('address_city');
    $address_state = $request->get('address_state');
    $address_postalcode = $request->get('address_postalcode');
    $address_country = $request->get('address_country');
    $status = $request->get('status');
    $data_ld = array(
        "firstname" => $first_name,
        "lastname" => $last_name,
        //"title" => $title,
        "email" => $email1,
        "leadstatus" => $status,
        "secondaryemail" => $email2

    );

    $data_la = array(
        "phone" => $phone_mobile,
        "mobile" => $phone_work,
        "lane" => $address_street,
        "city" => $address_city,
        "state" => $address_state,
        "code" => $address_postalcode,
        "country" => $address_country
    );

    $profileid = $user_role_details['profileid'];

    if ($profileid == 2) {
        $contactid = contactUpsertStatus($userid);
        $roleid = $user_role_details['roleid'];
        $parentrole = $user_role_details['parentrole'];

        $clientid = getClientIDByParentRole($roleid, $parentrole);
        if ($clientid != 0) {
            if ($contactid == '' || $contactid == 0)
                createContact($clientid, $userid, $data_ld, $data_la, $description);
            else
                updateContact($userid, $contactid,  $data_ld, $data_la, $description);
        }
    }
}

function createContact($clientid, $userid, $data_ld, $data_la, $description){
    global $adb, $current_user;
    $current_user = $current_user->id;
    $module = 'Leads';
    $parent_module = 'Accounts';

    $currentdatetime = date("Y-m-d H:i:s");
    $crmid = $adb->getUniqueID("vtiger_crmentity");
    $lead_no = getEntityNum($module);
    insertCRMEntity($crmid, $userid, $current_user, $module, $currentdatetime, $lead_no, $description);

    $entity_values = array(
        'lead_no'=> $lead_no,
        'assigned_user_id'=>$userid,
        'createdtime'=>$currentdatetime,
        'modifiedby'=>$current_user,
        'record_id'=>$crmid,
        'record_module'=>$module
    );

    $all_values = array_merge($entity_values, $data_ld, $data_la);

    $firstname = $data_ld['firstname'];
    $last_name = $data_ld['lastname'];
    $email1 = $data_ld['email'];
    $secondaryemail = $data_ld['secondaryemail'];
    $status = 'Active';

    $homephone = $data_la['phone'];
    $mobile = $data_la['mobile'];
    $mailingstreet = $data_la['lane'];
    $mailingcity = $data_la['city'];
    $mailingstate = $data_la['state'];
    $mailingzip = $data_la['code'];
    $mailingcountry = $data_la['country'];

    $query_cond = "INSERT INTO vtiger_leaddetails
                  (leadid, lead_no, firstname, lastname, email, secondaryemail, accountid, leadstatus)
                  VALUES(?,?,?,?,?,?,?,?)";
    $param = array($crmid, $lead_no, $firstname, $last_name, $email1, $secondaryemail, $clientid, $status);
    $adb->pquery($query_cond, $param);

    $query_concf = "INSERT INTO vtiger_leadscf (leadid) VALUES(?)";
    $param = array($crmid);
    $adb->pquery($query_concf, $param);

    $query_consd = "INSERT INTO vtiger_leadsubdetails (leadsubscriptionid) VALUES(?)";
    $param = array($crmid);
    $adb->pquery($query_consd, $param);

    $query_concd = "INSERT INTO vtiger_leadaddress (leadaddressid, phone, mobile, lane, city, state, code, country)
                    VALUES(?,?,?,?,?,?,?,?)";
    $param = array($crmid, $homephone, $mobile, $mailingstreet, $mailingcity, $mailingstate, $mailingzip, $mailingcountry);
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
    $query = "SELECT l.leadid FROM vtiger_leaddetails l
            INNER JOIN vtiger_crmentity crm ON crm.`crmid` = l.leadid
            WHERE crm.`deleted` = 0 AND crm.smownerid = $userid";
    $sqlQuery = $adb->query($query);
    if ($adb->num_rows($sqlQuery)> 0) {
        $result = $adb->fetch_row($sqlQuery);
        $contactid = $result['leadid'];
    }
    return $contactid;
}

function getClientIDByParentRole($roleid, $parentrole)
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

function updateContact($userid, $contactid, $data_ld, $data_la, $description)
{
    global $adb, $current_user;
    $loggedin_user = $current_user->id;
    $all_values = array_merge($data_ld, $data_la);
    $module = 'Leads';
    $mod_status = 0;
    $basic_id = 0;
    $currentdatetime = date("Y-m-d H:i:s");
    $update_values = '';
    $insert_mod_values = '';
    $sql = "SELECT
            con.firstname,
            con.lastname,
            conad.mobile,
            con.email,
            con.secondaryemail,
            con.leadstatus,
            conad.phone,
            conad.lane,
            conad.city,
            conad.state,
            conad.code,
            conad.country
            FROM vtiger_leaddetails con
            INNER JOIN vtiger_crmentity crm ON crm.`crmid` = con.`leadid`
            INNER JOIN vtiger_leadaddress conad ON conad.`leadaddressid` = con.`leadid`
            WHERE crm.`deleted` = 0 AND con.`leadid` =? ";

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
        $sql = "UPDATE vtiger_leaddetails
                INNER JOIN vtiger_crmentity ON vtiger_crmentity.`crmid` = vtiger_leaddetails.`leadid`
                INNER JOIN vtiger_leadaddress ON vtiger_leadaddress.`leadaddressid` = vtiger_leaddetails.`leadid`
                SET $update_values
                WHERE vtiger_crmentity.`deleted` = 0 AND vtiger_leaddetails.`leadid` = $contactid";
        $adb->query($sql);

        $sql = "INSERT INTO vtiger_modtracker_detail(id,fieldname,prevalue, postvalue) VALUES $insert_mod_values";
        $adb->query($sql);
    }
}