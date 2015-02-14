<?php

require_once "paths.php";

define( 'PID', getmypid() );
define( 'TMPFILE', "/dev/shm/" . PID . ".json" );

$app = new Phalcon\Mvc\Micro();

$app->post('/data_bag/decrypt', function () use ($app) {
  $fp = fopen(TMPFILE, "w");
 
  $l = json_encode($app->request->getJsonRawBody());
  $jdec = json_decode($l,true);
 
  fputs($fp, $l);
  fclose($fp);

  
  exec("knife data bag create " . PID , $empty , $retval);
  exec("knife data bag from file ". PID . " " . TMPFILE, $empty, $retval);
  $dec_databag = shell_exec("knife data bag show " . PID . " " . $jdec['id'] . " -Fj --secret-file=" . PATH_CHEF . "encrypted_data_bag_secret");
  exec("echo y | knife data bag delete " . PID, $empty, $retval);

  $response = new Phalcon\Http\Response();
  $response->setHeader("Content-Type", "application/json");
  $response->setStatusCode(200, "OK");
  $response->setContent("$dec_databag");
  $response->send();

});

$app->post('/data_bag/encrypt', function () use ($app) {

  $fp = fopen(TMPFILE, "w");
 
  $l = json_encode($app->request->getJsonRawBody());
  $jdec = json_decode($l,true);
  fputs($fp, $l);
  fclose($fp);

  
  exec("knife data bag create " . PID , $empty , $retval);
  exec("knife data bag from file ". PID . " " . TMPFILE . " --secret-file=" . PATH_CHEF . "encrypted_data_bag_secret", $empty, $retval);
  $enc_databag = shell_exec("knife data bag show " . PID . " " . $jdec['id'] . " -Fj");
  exec("echo y | knife data bag delete " . PID, $empty, $retval);

  $response = new Phalcon\Http\Response();
  $response->setHeader("Content-Type", "application/json");
  $response->setStatusCode(200, "OK");
  $response->setContent("$enc_databag");
  $response->send();

});

$app->handle();
