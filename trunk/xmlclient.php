<?php

include_once 'Amber/lib/IXR_Library.inc.php';

//$client = new IXR_Client('http://intern-dev.sbz.de/report/xmlserver.php');
$client = new IXR_Client('http://localhost/amber/xmlserver.php');

$client->debug = true;

//if (!$client->query('Amber.writeXML', 'testBericht.xml', '<xml>"test"</xml>')) {
//    die('Something went wrong: '.$client->getErrorCode().' : '.$client->getErrorMessage());
//}

if (!$client->query('Amber.getFormList')) {
    die('Something went wrong: '.$client->getErrorCode().' : '.$client->getErrorMessage());
}

//if (!$client->query('Amber.getCode', 'GLS')) {
//    die('Something went wrong: '.$client->getErrorCode().' : '.$client->getErrorMessage());
//}

var_dump($client->getResponse());


?>
