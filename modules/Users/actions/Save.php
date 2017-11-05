<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
require_once('include/utils/utilsCustom.php');
require_once('include/utils/utilsContact.php');
class Users_Save_Action extends Vtiger_Save_Action {

	public function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$record = $request->get('record');
		$recordModel = Vtiger_Record_Model::getInstanceById($record, $moduleName);
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		if(!Users_Privileges_Model::isPermitted($moduleName, 'Save', $record) || ($recordModel->isAccountOwner() && 
							$currentUserModel->get('id') != $recordModel->getId() && !$currentUserModel->isAdminUser())) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
		}
	}

	/**
	 * Function to get the record model based on the request parameters
	 * @param Vtiger_Request $request
	 * @return Vtiger_Record_Model or Module specific Record Model instance
	 */
	public function getRecordModelFromRequest(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$recordId = $request->get('record');
		$currentUserModel = Users_Record_Model::getCurrentUserModel();

		if(!empty($recordId)) {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
			$modelData = $recordModel->getData();
			$recordModel->set('id', $recordId);
			$sharedType = $request->get('sharedtype');
			if(!empty($sharedType))
				$recordModel->set('calendarsharedtype', $request->get('sharedtype'));
			$recordModel->set('mode', 'edit');
		} else {
			$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
			$modelData = $recordModel->getData();
			$recordModel->set('mode', '');
		}

		foreach ($modelData as $fieldName => $value) {
			$requestFieldExists = $request->has($fieldName);
			if(!$requestFieldExists){
				continue;
			}
			$fieldValue = $request->get($fieldName, null);
			if ($fieldName === 'is_admin' && (!$currentUserModel->isAdminUser() || !$fieldValue)) {
				$fieldValue = 'off';
			}
			//to not update is_owner from ui
			if ($fieldName == 'is_owner') {
				$fieldValue = null;
			}
			if($fieldValue !== null) {
				if(!is_array($fieldValue)) {
					$fieldValue = trim($fieldValue);
				}
				$recordModel->set($fieldName, $fieldValue);
			}
		}
		$homePageComponents = $recordModel->getHomePageComponents();
		$selectedHomePageComponents = $request->get('homepage_components', array());
		foreach ($homePageComponents as $key => $value) {
			if(in_array($key, $selectedHomePageComponents)) {
				$request->setGlobal($key, $key);
			} else {
				$request->setGlobal($key, '');
			}
		}
		if($request->has('tagcloudview')) {
			// Tag cloud save
			$tagCloud = $request->get('tagcloudview');
			if($tagCloud == "on") {
				$recordModel->set('tagcloud', 0);
			} else {
				$recordModel->set('tagcloud', 1);
			}
		}
		return $recordModel;
	}

	public function process(Vtiger_Request $request) {
		$result = Vtiger_Util_Helper::transformUploadedFiles($_FILES, true);
		$_FILES = $result['imagename'];

		$recordId = $request->get('record');
		if (!$recordId) {
			$module = $request->getModule();
			$userName = $request->get('user_name');
			$userModuleModel = Users_Module_Model::getCleanInstance($module);
			$status = $userModuleModel->checkDuplicateUser($userName);
			if ($status == true) {
				throw new AppException(vtranslate('LBL_DUPLICATE_USER_EXISTS', $module));
			}
		}

		$recordModel = $this->saveRecord($request);

		$userid = ($recordId) ? $recordId : $recordModel->getId();
		$this->createCustomEntity($request, $userid);

		if ($request->get('relationOperation')) {
			$parentRecordModel = Vtiger_Record_Model::getInstanceById($request->get('sourceRecord'), $request->get('sourceModule'));
			$loadUrl = $parentRecordModel->getDetailViewUrl();
		} else if ($request->get('isPreference')) {
			$loadUrl =  $recordModel->getPreferenceDetailViewUrl();
		} else if ($request->get('returnmodule') && $request->get('returnview')){
			$loadUrl = 'index.php?'.$request->getReturnURL();
		} else if($request->get('mode') == 'Calendar'){
			$loadUrl = $recordModel->getCalendarSettingsDetailViewUrl();
		}else {
			$loadUrl = $recordModel->getDetailViewUrl();
		}

		header("Location: $loadUrl");
	}

	function createCustomEntity($request, $recordId){
		if($recordId) {
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

			setContactData($recordId, $data_cd, $data_csd, $data_ca, $description);
		}
	}
}
