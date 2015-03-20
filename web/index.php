<?php

#Load paths
require_once "paths.php";

#Load configuration file
require_once PATH_CONFIGS;


$app = new Phalcon\Mvc\Micro();

$app->post('/token/generate', function() use ($app) {
  $token = "";
  $status_code='200';
  $status_message='OK';
  $response = new Phalcon\Http\Response();

  if ( strcmp( $app->request->getPost("secret"), SECRET ) == 0 ) 
  {
    $token = shell_exec('uuidgen | sed -e s/-//g');

    exec("touch /dev/shm/" . $token, $empty, $retval );
  }
  else 
  {
    $status_code='409';    
    $status_message='AUTH FAILED';
    $token = "Access Denied.\n";
  }
 
  // Prepare Response
  $response->setStatusCode($status_code, $status_message);
  $response->setContent($token);
  $response->send();
});

$app->post('/data_bag/decrypt', function () use ($app) {

  $enc_databag = "";
  $status_code='200';
  $status_message='OK';

  $response = new Phalcon\Http\Response();

  $databag = json_encode($app->request->getJsonRawBody());

  try
  {
    $databag_id = json_decode($databag,true)['id'];
  } catch (Exception $e)
  {
      $enc_databag = "[ERROR] Data bag is missing an id entry.";
  }

  if ( is_null($databag_id) && empty($databag_id) )
  {
    $status_code='409';
    $status_message='MISSING ID';
    $enc_databag = "[ERROR] Data bag is missing an id entry.\n";
  }
  else
  {  
    $fp = fopen(TMPFILE, "w");
    fputs($fp, $databag);
    fclose($fp);

    // Create Temporary Data Bag on Chef Server
    exec("knife data bag create " . PID . " " . KNIFE_OPT , $empty , $retval);

    // Import Data Bag 
    exec("knife data bag from file ". PID . " " . TMPFILE . " " . KNIFE_OPT, $empty, $retval);

    // List Data Bag in decrypted format
    $dec_databag = shell_exec("knife data bag show " . PID . " " . $databag_id . " -Fj --secret-file=" . PATH_CHEF . "encrypted_data_bag_secret ". KNIFE_OPT );

    // Delete the Data Bag from Chef Server
    exec("echo y | knife data bag delete " . PID . " " . KNIFE_OPT, $empty, $retval);
  
    // Delete temporary file
    unlink(TMPFILE);
  }

  // Prepare Response
  $response->setHeader("Content-Type", "application/json");
  $response->setStatusCode($status_code, $status_message);
  $response->setContent("$dec_databag");
  $response->send(); 

});

$app->post('/data_bag/encrypt', function () use ($app) {
  
  $enc_databag = ""; 
  $status_code='200';
  $status_message='OK';

  $response = new Phalcon\Http\Response();

  $databag = json_encode($app->request->getJsonRawBody());

  try 
  {
    $databag_id = json_decode($databag,true)['id'];
  } catch (Exception $e)
  {
      $enc_databag = "[ERROR] Data bag is missing an id entry.";
  }


  if ( is_null($databag_id) && empty($databag_id) )
  {
    $status_code='409';
    $status_message='MISSING ID';      
    $enc_databag = "[ERROR] Data bag is missing an id entry.\n"; 
  }
  else
  {
    // Write data bag on disk
    $fp = fopen(TMPFILE, "w");
    fputs($fp, $databag);
    fclose($fp);

    // Create Temporary Data Bag on Chef Server
    exec("knife data bag create " . PID . " " . KNIFE_OPT, $empty , $retval);

    // Import and Encrypt data bag into temporary databag location 
    exec("knife data bag from file ". PID . " " . TMPFILE . " --secret-file=" . PATH_CHEF . "encrypted_data_bag_secret " . KNIFE_OPT, $empty, $retval);

    // Extract Encrypted databag in JSON format
    $enc_databag = shell_exec("knife data bag show " . PID . " " . $databag_id . " -Fj " . KNIFE_OPT);

    // Delete the Data Bag from Chef Server
    exec("echo y | knife data bag delete " . PID . " " . KNIFE_OPT, $empty, $retval);

    // Delete temporary file
    unlink(TMPFILE);
  }

  // Prepare Response
  $response->setHeader("Content-Type", "application/json");
  $response->setStatusCode($status_code, $status_message);
  $response->setContent("$enc_databag");
  $response->send();

});

$app->handle();

