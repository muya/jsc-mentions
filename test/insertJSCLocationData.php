<?php

include_once dirname(__FILE__) . '/../libs/Utils.php';
/*
 * test script to insert records into JSC Data db
 */
$locationNames = array("Baragoi", "Bungoma", "Busia", "Butere", "Dadaab",
    "Diani Beach", "Eldoret", "Embu", "Garissa", "Gede", "Hola", "Homa Bay",
    "Isiolo", "Kajiado", "Kakamega", "Kakuma", "Kapenguria", "Kericho", "Kiambu",
    "Kilifi", "Kisii", "Kisumu", "Kitale", "Lamu", "Langata", "Litein", "Lodwar",
    "Lokichoggio", "Londiani", "Loyangalani", "Machakos", "Malindi", "Mandera",
    "Maralal", "Marsabit", "Meru", "Mombasa", "Moyale", "Mumias", "Muranga",
    "Nairobi", "Naivasha", "Nakuru", "Namanga", "Nanyuki", "Naro Moru", "Narok",
    "Nyahururu", "Nyeri", "Ruiru", "Shimoni", "Takaungu", "Thika", "Vihiga", "Voi",
    "Wajir", "Watamu", "Webuye", "Wundanyi");

//loop through the location names, running inserts
$insertLocationSQL = 'insert into locations (locationName, dateCreated) values '
        . '(:locationName, :dateCreated)';
foreach ($locationNames as $loc) {
    $insertLocationParams = array(
        ':locationName' => $loc,
        ':dateCreated' => Utils::now(),
    );
    $insertResponse = Utils::executePreparedStatement($insertLocationSQL, $insertLocationParams, null, null, true);
    Utils::log(INFO, 'insert response for: '.$loc. '| '.json_encode($insertResponse), __FILE__, __FUNCTION__, __LINE__);
}