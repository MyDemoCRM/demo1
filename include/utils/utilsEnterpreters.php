<?php

function setEnterpretersData($userid, $request, $user_role_details)
{
    $first_name = $request->get('first_name');
    $last_name = $request->get('last_name');
    $title = $request->get('title');
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
    $data_cd = array(
        "firstname" => $first_name,
        "lastname" => $last_name,
        "mobile" => $phone_work,
        "title" => $title,
        "email" => $email1,
        "secondaryemail" => $email2

    );

    $data_csd = array(
        "homephone" => $phone_mobile

    );

    $data_ca = array(
        "mailingstreet" => $address_street,
        "mailingcity" => $address_city,
        "mailingstate" => $address_state,
        "mailingzip" => $address_postalcode,
        "mailingcountry" => $address_country

    );

    $profileid = $user_role_details['profileid'];

    if ($profileid == 4) {
        $contactid = contactUpsertStatus($userid);
        $roleid = $user_role_details['roleid'];
        $parentrole = $user_role_details['parentrole'];

        $clientid = getClientIDByParentRole($roleid, $parentrole);
        if ($clientid != 0) {
            if ($contactid == '' || $contactid == 0)
                createEnterpreters($clientid, $userid, $data_cd, $data_csd, $data_ca, $description);
            else
                updateEnterpreters($userid, $contactid, $data_cd, $data_csd, $data_ca, $description);
        }
    }
}

function createEnterpreters($clientid, $userid, $data_cd, $data_csd, $data_ca, $description){
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

function updateEnterpreters($userid, $contactid, $data_cd, $data_csd, $data_ca, $description)
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