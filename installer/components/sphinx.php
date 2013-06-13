<?php

if(AppConfig::get(AppConfigAttribute::UPGRADE_FROM_VERSION))
{
	Logger::logMessage(Logger::LEVEL_INFO, "Populating old content to the sphinx");
	$appDir = AppConfig::get(AppConfigAttribute::APP_DIR);
	$populateScripts = array(
		"$appDir/deployment/base/scripts/populateSphinxCategories.php",
		"$appDir/deployment/base/scripts/populateSphinxCategoryKusers.php",
		"$appDir/deployment/base/scripts/populateSphinxCuePoints.php",
		"$appDir/deployment/base/scripts/populateSphinxEntries.php",
		"$appDir/deployment/base/scripts/populateSphinxEntryDistributions.php",
		"$appDir/deployment/base/scripts/populateSphinxKusers.php",
		"$appDir/deployment/base/scripts/populateSphinxTags.php",
	);
	$cmd = sprintf("%s/ddl/migrations/20130606_falcon_to_gemini/Falcon2Gemini.sh $arguments", AppConfig::get(AppConfigAttribute::DWH_DIR));
	if (!OsUtils::execute($cmd)){
		return "Failed running data warehouse upgrade script";
	}
}

return true;