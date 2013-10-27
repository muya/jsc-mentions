<?php

/*
 * this file contains config options
 */
//DB CONFIGS
define('DBHOST', '127.0.0.1');
define('DBUSER', 'learn');
define('DBPASS', 't3achM35thN3W');
define('DBNAME', 'JSCData');

/* LOG CONFIGS */
define('LOG_DIRECTORY', '/var/log/applications/JSCMentions/');

//log levels
define('ERROR', 'ERROR');
define('INFO', 'INFO');
define('DEBUG', 'DEBUG');
define('EXCEPTION', 'EXCEPTION');
define('UNDEFINED', 'UNDEFINED');
define('FATAL', 'FATAL');
define('SYSTEM_LOG_LEVEL', 10);

//pipe separated list of email recipients
define('EMAIL_RECIPIENTS', 'fred@example.com');

//email configs
define('MAILER_USERNAME', 'some_cool_gmail_handle@gmail.com'); //Email username--to log in when sending emails
define('MAILER_PASS', 'AV3ryH@rdPAS5w0rd'); //Email password used to send emails
define('MAILER_EMAIL', 'some_cool_gmail_handle@gmail.com'); //email --to send emails

define('EMAIL_HOST', 'smtp.gmail.com');
define('EMAIL_PORT', 465);
define('EMAIL_SMTPSecure', 'ssl');
define('EMAIL_FROM_NAME', 'Report Generator');