<?php

include_once dirname(__FILE__) . '/../libs/Utils.php';
/*
 * test script to insert records into JSC Data db
 */
$platformNames = array("Facebook", "Twitter", "Google +", "Reddit", "LinkedIn");

//loop through the platform names, running inserts
$insertPlatformSQL = 'insert into platforms (platformName, dateCreated, '
        . 'dateModified) values (:platformName, :dateCreated, :dateModified)';
foreach ($platformNames as $p) {
    $insertPlatformParams = array(
        ':platformName' => $p,
        ':dateCreated' => Utils::now(),
        ':dateModified' => Utils::now(),
    );
    $insertResponse = Utils::executePreparedStatement($insertPlatformSQL, $insertPlatformParams, null, null, true);
    Utils::log(INFO, 'insert response for: '.$p. '| '.json_encode($insertResponse), __FILE__, __FUNCTION__, __LINE__);
}