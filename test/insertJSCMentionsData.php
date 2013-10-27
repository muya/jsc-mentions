<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
include_once dirname(__FILE__) . '/../libs/Utils.php';


/*
  LOGIC
  get id's & count of platforms
  get id's & count of locations

  loop n times taking a random of each, inserting into jsc_mentions table
 */
$n = 500;

//fetch platforms
$fetchPlatformsSQL = 'SELECT id, platformName FROM platforms';

$fetchPlatformsResponse = Utils::executePreparedStatement($fetchPlatformsSQL, array());

Utils::log(INFO, 'platform data: ' . json_encode($fetchPlatformsResponse), __FILE__, __FUNCTION__, __LINE__);

if ($fetchPlatformsResponse['STAT_TYPE'] != SC_GENERIC_SUCCESS_CODE) {
    Utils::log(ERROR, 'unable to fetch platforms | full response: '
            . json_encode($fetchPlatformsResponse), __FILE__, __FUNCTION__, __LINE__);
    die();
}

$platformsData = $fetchPlatformsResponse['DATA'];

//fetch locations
$fetchLocationsSQL = 'SELECT id, locationName FROM locations';

$fetchLocationsResponse = Utils::executePreparedStatement($fetchLocationsSQL, array());

Utils::log(INFO, 'locations data: ' . json_encode($fetchLocationsResponse), __FILE__, __FUNCTION__, __LINE__);

if ($fetchLocationsResponse['STAT_TYPE'] != SC_GENERIC_SUCCESS_CODE) {
    Utils::log(ERROR, 'unable to fetch locations | full response: '
            . json_encode($fetchLocationsResponse), __FILE__, __FUNCTION__, __LINE__);
    die();
}

$locationsData = $fetchLocationsResponse['DATA'];

//count
$locationsCount = count($locationsData);
$platformsCount = count($platformsData);

for ($i = 0; $i < $n; $i++) {
    $randLocation = rand(0, ($locationsCount - 1));
    $randPlatform = rand(0, ($platformsCount - 1));

    $currLocation = $locationsData[$randLocation];
    $currPlatform = $platformsData[$randPlatform];

    Utils::log(INFO, 'current location: ' . $currLocation['id'] . ': '
            . $currLocation['locationName'] . "\n");
    Utils::log(INFO, 'current platform: ' . $currPlatform['id'] . ': '
            . $currPlatform['platformName'] . "\n");

    $insertJSCMentionSQL = 'insert into jsc_mentions '
            . '(platformID, locationID, dateCreated, dateModified) values '
            . '(:platformID, :locationID, :dateCreated, :dateModified)';
    $insertJSCMentionData = array(
        ':platformID' => $currPlatform['id'],
        ':locationID' => $currLocation['id'],
        ':dateCreated' => Utils::now(),
        ':dateModified' => Utils::now(),
    );

    $insertJSCMentionResult = Utils::executePreparedStatement($insertJSCMentionSQL, $insertJSCMentionData, null, null, true);

    Utils::log(INFO, 'insert jsc mention data result: '
            . json_encode($insertJSCMentionResult), __FILE__, __FUNCTION__, __LINE__);
}