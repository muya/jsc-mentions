<?php

include_once dirname(__FILE__) . '/libs/Utils.php';
include_once dirname(__FILE__).'/libs/phpmailer/JPhpMailer.php';

/*
 * PROCESS
 * fetch data
 * --mentions per platform
 * --mentions per location
 * save it in a JSON file
 */

//fetch data
$fetchMentionsPerPlatformSQL = 'select platformID, p.platformName, count(*) as mentions '
        . 'from jsc_mentions jsc inner join platforms p  on p.id=jsc.platformID '
        . 'group by platformID';

$fetchMentionsPerLocationSQL = 'select locationID, l.locationName, count(*) as mentions from '
        . 'jsc_mentions jsc inner join locations l on l.id=jsc.locationID group '
        . 'by locationID';

$fetchMentionsPerPlatformResponse = Utils::executePreparedStatement($fetchMentionsPerPlatformSQL, array());
$fetchMentionsPerLocationResponse = Utils::executePreparedStatement($fetchMentionsPerLocationSQL, array());

if ($fetchMentionsPerLocationResponse['STAT_TYPE'] != SC_GENERIC_SUCCESS_CODE) {
    Utils::log(ERROR, 'an error occurred while fetching mentions per location | '
            . json_encode($fetchMentionsPerLocationResponse), __FILE__, __FUNCTION__, __LINE__);
}

if ($fetchMentionsPerPlatformResponse['STAT_TYPE'] != SC_GENERIC_SUCCESS_CODE) {
    Utils::log(ERROR, 'an error occurred while fetching mentions per platform | '
            . json_encode($fetchMentionsPerLocationResponse), __FILE__, __FUNCTION__, __LINE__);
}

$mentionsPerLocation = $fetchMentionsPerLocationResponse['DATA'];
$mentionsPerPlatform = $fetchMentionsPerPlatformResponse['DATA'];

Utils::log(INFO, 'mentions per location data: '.json_encode($mentionsPerLocation));
Utils::log(INFO, '==============================================================');
Utils::log(INFO, 'mentions per platform data: '.json_encode($mentionsPerPlatform));

//convert data to JSON and write to file
$platformDataFileName = LOG_DIRECTORY . 'jsc_mentions_per_platform_'.Utils::now('YmdHis').'.json';
$locationDataFileName = LOG_DIRECTORY . 'jsc_mentions_per_location_'.Utils::now('YmdHis').'.json';

Utils::writeToFile(json_encode($mentionsPerLocation), $locationDataFileName);
Utils::writeToFile(json_encode($mentionsPerPlatform), $platformDataFileName);

//mail the data
$emailRecipients = explode('|', EMAIL_RECIPIENTS);
$attachments = array(
    array(
        'filename' => 'jsc_mentions_per_platform_'.Utils::now('Ymd').'.json',
        'attachment' => $platformDataFileName
    ),
    array(
        'filename' => 'jsc_mentions_per_location_'.Utils::now('Ymd').'.json',
        'attachment' => $locationDataFileName
    ),
);

foreach($emailRecipients as $r){
    Utils::sendMessage('JSC Mentions Report', $r, null, 'JSC Mentions Data', $attachments);
}