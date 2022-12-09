<?php

function register_user($stmt){
	$dao = SOY2DAOFactory::create("user.SOYShop_UserDAO");

	$i = 0;
	for(;;){
		$dao->setOrder("id ASC");
		$dao->setLimit(RECORD_LIMIT);
		$dao->setOffset(RECORD_LIMIT * $i++);
		try{
			$users = $dao->get();
			if(!count($users)) break;
		}catch(Exception $e){
			break;
		}

		foreach($users as $user){
			$stmt->execute(array(
				":id" => $user->getId(),
				":mail_address" => $user->getMailAddress(),
				":user_code" => $user->getUserCode(),
				":attribute1" => $user->getAttribute1(),
				":attribute2" => $user->getAttribute2(),
				":attribute3" => $user->getAttribute3(),
				":name" => $user->getName(),
				":reading" => $user->getReading(),
				":honorific" => $user->getHonorific(),
				":nickname" => $user->getNickName(),
				":account_id" => $user->getAccountId(),
				":profile_id" => $user->getProfileId(),
				":image_path" => $user->getImagePath(),
				":gender" => $user->getGender(),
				":birthday" => $user->getBirthday(),
				":zip_code" => $user->getZipCode(),
				":area" => $user->getArea(),
				":address1" => $user->getAddress1(),
				":address2" => $user->getAddress2(),
				":address3" => $user->getAddress3(),
				":telephone_number" => $user->getTelephoneNumber(),
				":fax_number" => $user->getFaxNumber(),
				":cellphone_number" => $user->getCellphoneNumber(),
				":url" => $user->getUrl(),
				":job_name" => $user->getJobName(),
				":job_zip_code" => $user->getJobZipCode(),
				":job_area" => $user->getJobArea(),
				":job_address1" => $user->getJobAddress1(),
				":job_address2" => $user->getJobAddress2(),
				":job_telephone_number" => $user->getJobTelephoneNumber(),
				":job_fax_number" => $user->getJobFaxNumber(),
				":memo" => $user->getMemo(),
				":mail_error_count" => $user->getMailErrorCount(),
				":not_send" => $user->getNotSend(),
				":is_error" => $user->getIsError(),
				":is_publish" => $user->getIsPublish(),
				":is_disabled" => $user->getIsDisabled(),
				":is_profile_display" => $user->getIsProfileDisplay(),
				":register_date" => $user->getRegisterDate(),
				":update_date" => $user->getUpdateDate(),
				":real_register_date" => $user->getRealRegisterDate(),
				":user_type" => $user->getUserType(),
				":address_list" => $user->getAddressList(),
				":password" => $user->getPassword(),
				":attributes" => $user->getAttributes()
			));
		}
	}
}
