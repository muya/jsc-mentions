<?php
/**
* this class contains some useful utility functions
*/
include_once dir(__FILE__). '/../configs/configs.php';
class Utils {

    /**
     * function to connect to MySQL using more secure PDO method
     * 
     */
    public static function PDOConnect($db = null, $host = null, $user = null, $pass = null){
        if($db === null){
            $db = DATABASE;
        }
        if($host === null){
            $host = HOST;
        }
        if($user === null){
            $user = USER;
        }
        if($pass === null){
            $pass = PASSWORD;
        }
        try {
            $connString = 'mysql:host='.$host.';dbname='.$db;
            $conn = new PDO($connString, $user, $pass);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            Utils::log('EXCEPTION', 'AN ERROR OCCURRED WHILE TRYING TO CONNECT '.
                'TO THE DATABASE | '.$e->getCode(). ' | '.$e->getMessage(), __LINE__, __FUNCTION__);
            return null;
        }
        return $conn;
    }

    /**
     * function that utilizes prepared statements & PDO methods to fetch data from db
     * @param string $tableName table from which to fetch data
     * @param array $columnsToFetch an array of columns to return, defaults to '' (all)
     * @param array $filters parameters to be used in WHERE as criteria
     * @return array    returns a formattedResponse (from @method formatResponse)
     */
    public static function PDOFetchRecords($tableName, $columnsToFetch = array(), $filters = array(), $dbConn = null, $fetchMode = PDO::FETCH_ASSOC){
        if($dbConn == null){
            $dbConn = PDOConnect();
        }
        if($dbConn == null){
            return Utils::formatResponse(null, SC_GENERIC_FAILURE_CODE, 4, 'THERE WAS AN ERROR CONNECTING TO THE DATABASE');
        }
        
        //construct prepared statement
        //columns to fetch
        if(empty($columnsToFetch)){
            $columns = ' * ';
        }
        else{
            $columns = implode(' , ', $columnsToFetch);
        }
        
        //where clause
        if(empty($filters)){
            $where = '';
        }
        else{
            $where = ' WHERE ';
            $ANDCounter = 0;
            foreach($filters as $k=>$v){
                if($ANDCounter > 0){
                    $where .= ' AND ';
                }
                $where .= $k.'=:'.$k.' ';
                $ANDCounter++;
            }
            $where = rtrim($where, 'AND');
        }
        $preparedStatement = 'SELECT '.$columns.' FROM '.$tableName.$where;
        
        $resultData = Utils::executePreparedStatement($preparedStatement, $filters, $fetchMode, $dbConn);
        
        return $resultData;
    }

    /**
     * function to execute prepared mysql statements
     * @param type $SQL the prepared statement to be executed
     * @param type $params  the params to bind to the statement
     * @param type $fetchMode   format of fetched data(if any) defaults to associative array (PDO::FETCH_ASSOC)
     * @param type $dbConn  the db connection to be used
     * @param type $noFetch if set to true, method will not attempt to fetch any 
     * data SET TO true FOR INSERT & UPDATE STATEMENTS
     * @return formatResponse ARRAY
     */
    public static function executePreparedStatement($SQL, $params, $fetchMode = PDO::FETCH_ASSOC, $dbConn = null, $noFetch = false, $getLastInsert = false){
        if($dbConn == null){
            $dbConn = self::PDOConnect();
        }
        if($dbConn == null){
            return Utils::formatResponse(null, SC_GENERIC_FAILURE_CODE, 4, 'THERE WAS AN ERROR CONNECTING TO THE DATABASE');
        }
        try {
            
            Utils::log('INFO', 'SQL TO BE EXECUTED: '.$SQL, __CLASS__, __FUNCTION__, __LINE__);
            $stmt = $dbConn->prepare($SQL);
            $executeStatus = $stmt->execute($params);
            if($noFetch == true){
                $results = $executeStatus;
    //            if($getLastInsert == true){
    //                $fetchLastInsert = $dbConn->prepare('SELECT LAST_INSERT_ID()');
    //                $fetchLastInsertExecute = $fetchLastInsert->execute();
    //                $lastInsert = ($fetchLastInsertExecute) ? $fetchLastInsert->fetchAll() : null;
    //                $results['DATA']['PK'] = $lastInsert['LAST_INSERT_ID()'];
    //            }
            }
            else{
                $results = $stmt->fetchAll($fetchMode);
            }
            
        } catch (PDOException $exc) {
            Utils::log('EXCEPTION', 'A DATABASE ERROR OCCURRED | '.$exc->getCode(). ' | '.$exc->getMessage(), __CLASS__, __FUNCTION__, __LINE__);
            return Utils::formatResponse(null, SC_GENERIC_FAILURE_CODE, 4, $exc->getMessage());
        } catch (Exception $e){
            Utils::log('EXCEPTION', 'A DATABASE ERROR OCCURRED | '.$e->getCode(). ' | '.$e->getMessage(), __CLASS__, __FUNCTION__, __LINE__);
            return Utils::formatResponse(null, SC_GENERIC_FAILURE_CODE, 4, $e->getMessage());
        }
        //return data

        return Utils::formatResponse($results, SC_GENERIC_SUCCESS_CODE, 1, SC_GENERIC_SUCCESS_DESC);    
    }
    
    /**
    * Function to to format the returned response
    */
   public static function formatResponse($data=null, $statusCode=null, $statusType =null, $description =null)
   {
       return array(
               'DATA' => $data,
               'STAT_CODE' => $statusCode,
               'STAT_TYPE' => $statusType,
               'STAT_DESCRIPTION' => $description,
           );
   }

    public static function random_gen($length) {
        $random = "";
        srand((double) microtime() * 1000000);
        $char_list = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $char_list .= "abcdefghijklmnopqrstuvwxyz";
        $char_list .= "1234567890";
        // Add the special characters to $char_list if needed

        for ($i = 0; $i < $length; $i++) {
            $random .= substr($char_list, (rand() % (strlen($char_list))), 1);
        }
        return $random;
    }

    public static function getRandomMSISDN($operatorPrefix = null, $countryPrefix = null, $minMainNumber = null, $maxMainNumber = null, $operatorPrefixesArray = null) {
        if($operatorPrefixesArray == null){
            $operatorPrefixesArray = array('700', '701', '702', '703', '704', '705', '706', '707', '708',
                '709', '710', '711', '712', '713', '714', '715', '716', '717', '718', '719', '720',
                '721', '722', '723', '724', '725', '726', '727', '728', '729', '730', '731', '732',
                '733', '734', '735', '736', '737', '738', '739', '780', '781', '782', '783', '784',
                '785', '786', '787', '788', '789', '750', '751', '752', '753', '754', '770', '772', '773');
        }
        
        
        $countryPrefixesArray = array('254', '255', '256', '233', '260');

        $countOperators = count($operatorPrefixesArray);
        $countCountries = count($countryPrefixesArray);

        if ($operatorPrefix === null) {
            $operatorPrefix = $operatorPrefixesArray[rand(0, $countOperators - 1)];
        }
        if ($countryPrefix === null) {
            $countryPrefix = $countryPrefixesArray[rand(0, $countCountries - 1)];
        }
        if($maxMainNumber == null){
            $maxMainNumber = 512000;
        }
        if($minMainNumber == null){
            $minMainNumber = 512130;
        }
        $mainNumber = strval(rand($minMainNumber, $maxMainNumber));
        $MSISDN = $countryPrefix . $operatorPrefix . $mainNumber;
        // echo $MSISDN;
        return $MSISDN;
    }
    
    public static function getSalutation(){
        $salutations = array(
            0=>'Mr',1=>'Mrs', 2=>'Miss', 3=>'Dr', 4=>'Prof', 5=>'Esq'
        );
        return $salutations[rand(0, count($salutations) -1)];
    }

    /**
     * function to add time to $original time, returned in the format YYYY-MM-DD HH:MM:SS
     * @param datetime formatted string $originalTime
     * @param string $timeToAdd e.g. '+1 hour', '+3 months'
     * @param string $timeZone
     * @return datetime formatted string
     */
    public static function addTime($originalTime, $timeToAdd, $timeZone = 'Africa/Nairobi') {
        $tz = new DateTimeZone($timeZone);
        if (!$tz) {
            return null;
        }
        $date = new DateTime($originalTime, $tz);
        if (!$date) {
            return null;
        }
        $date->modify($timeToAdd);
        $date->format('Y-m-d H:i:s');
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * Return the current Date and time in the standard format
     * @param int [$format]
     * @return text
     */
    public static function now($format = 'Y-m-d H:i:s', $timestamp = null) {
        if($timestamp == null)
            $timestamp = time();
        return date($format, $timestamp);
    }

    /**
     * Returns the number of unique combinations without repetition
     * @param integer $totalValues total number of possible values to choose from (n)
     * @param integer $requiredValues number of values to be chosen (r)
     * @return integer $combinations
     */
    public static function getUniqueCombinations($totalValues, $requiredValues) {
        /*
         * $combinations = (n)!/(n-r)!
         */

        $numerator = self::factorial($totalValues);
        $denominator = self::factorial($totalValues - $requiredValues);

        $combinations = (int) ($numerator / $denominator);
        return $combinations;
    }

    /**
     * Returns the number of permutations
     * @param integer $totalValues total number of possible values to choose from (n)
     * @param integer $requiredValues number of values to be chosen (r)
     */
    public static function getPermutations($totalValues, $requiredValues) {
        /*
         * $permutations = n^r
         */
        return (int) (pow($totalValues, $requiredValues));
    }

    /**
     * function to calculate factorial of a number
     * @param int $in
     * @return int 
     */
    public static function factorial($in) {
        // 0! = 1! = 1
        $out = 1;

        // Only if $in is >= 2
        for ($i = 2; $i <= $in; $i++) {
            $out *= $i;
        }

        return $out;
    }
    
    /**
     * Log String to log File in a predetermined format
     * @param int/text $logLevel 0 = 'CRITICAL', 1 = 'FATAL', 2 = 'ERROR', 3 = 'WARNING', 4 = 'INFO', 5 = 'SEQUEL', 6 = 'TRACE', 7 = 'DEBUG', 8 = 'CUSTOM', 9 = 'UNDEFINED';
     * @param string $logString
     * @param string $filename
     * @param string $function
     * @param int $lineNo
     */
    public static function log($logLevel, $logString = null, $fileName = null, $function = null, $lineNo = null) {
        $SYSTEM_LOG_LEVEL = SYSTEM_LOG_LEVEL;

        $logDirectory = LOG_PATH;
        $file = $logDirectory . "DEBUG.log";
        $date = date("Y-m-d H:i:s");
        $logType = null;
        $logType[0] = 'CRITICAL';
        $logType[1] = 'FATAL';
        $logType[2] = 'ERROR';
        $logType[3] = 'WARNING';
        $logType[4] = 'INFO';
        $logType[5] = 'SEQUEL';
        $logType[6] = 'TRACE';
        $logType[7] = 'DEBUG';
        $logType[8] = 'CUSTOM';
        $logType[9] = 'UNDEFINED';
        $logTitle = 'UNDEFINED';

        // covert ID to file Name
        if (!is_int($logLevel)) { // level is a string convert back to int and overide the default file
            if (strtolower(substr($logLevel, (strlen($logLevel) - 4), 4)) == '.log' or strtolower(substr($logLevel, (strlen($logLevel) - 4), 4)) == '.txt') { // overide the current paths {{faster than changing all scripts with custom paths}}
                $file = $logDirectory . basename($logLevel);
            } else { // file does not have the correct extension.
                $file = $logDirectory . basename($logLevel) . '.log';
            }

            $logLevel = 8;
        } else {
            if (isset($logType[$logLevel])) {
                // overide the current paths {{faster than changing all scripts with custom paths}}
                $file = $logDirectory . basename($logType[$logLevel]) . ".log";
            } else {
                $logLevel = 9;
            }
        }

        $logTitle = $logType[$logLevel];

        if ($fileName == null)
            $fileName = $_SERVER['PHP_SELF'];
        // should be <= $DEBUG_LEVEL
        if ($logLevel <= $SYSTEM_LOG_LEVEL) {
            if ($fo = fopen($file, 'ab')) {
                fwrite($fo, "$date -[$logTitle] $fileName:$lineNo $function| $logString\n");
                fclose($fo);
            } else {
                trigger_error("flog Cannot log '$logString' to file '$file' ", E_USER_WARNING);
            }
        }
    }
}