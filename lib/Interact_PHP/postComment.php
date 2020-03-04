<?php
namespace Interact_PHP;

require_once('settings.php');
require_once('Interact_PHP.php');

/* If comments are disabled but user tried to send a comment with 
some manual POST request, forbid action. */
if (Settings::DISABLE_COMMENTS) {
  echo Settings::DISABLE_COMMENTS_MESSAGE;
  exit;
}

/* If Google's reCAPTCHA are enabled, check the validity of the 
CAPTCHA.*/
if (!is_null(Settings::RECAPTCHA_PUBLIC_KEY)&&!is_null(Settings::RECAPTCHA_SECRET_KEY)) {
  if (!isset($_POST['g-recaptcha-response'])) 
  {
    echo "Please enable Javascript and/or check the reCAPTCHA!";
    exit;
}
else
{
    $api_url = "https://www.google.com/recaptcha/api/siteverify?secret=".Settings::RECAPTCHA_SECRET_KEY."&response=".$_POST['g-recaptcha-response']."&remoteip=".$_SERVER['REMOTE_ADDR'];

    $decode = json_decode(file_get_contents($api_url), true);

    if ($decode['success'] == false) {
      echo 'Please check the reCAPTCHA!';
      exit;
  }
}
}

/* Check the presence of the POST fields. */
if(!isset($_POST['name']) || !isset($_POST['message']) || !isset($_POST['page'])){
    echo "System error... please try again later.";
    exit;
}

/* Check the validity of the input. */
$name=preg_replace("/\r|\n/", " ", $_POST['name']);
if(strlen($name)<=0 || strlen($name)>Settings::MAX_USERNAME_LENGTH){
    echo "Error: name must contain between 1 and ".Settings::MAX_USERNAME_LENGTH." characters.";
    exit;
}
$message=preg_replace("/(\r|\n)+/", "\\n", $_POST['message']);
if(strlen($message)<=0 || strlen($message)>Settings::MAX_COMMENT_LENGTH){
    echo "Error: your comment must contain between 1 and ".Settings::MAX_COMMENT_LENGTH." characters.";
    exit;
}
$page=SanitizeFilename($_POST['page']);
if(strlen($page)<=0){
    echo "System error... please try again later.";
    exit;
}

/* At this point, the input is valid. Add the comment to the XML file. */
ignore_user_abort(true);
set_time_limit(0);
ob_start();

if(addComment($page,$name,$message,false)){
    echo "ok";
}else{
    echo "System error... please try again later.";
}

/* Flush PHP buffer to answer to the AJAX call without having to wait for the
completion of the callback function (which, in the case of emails is 2s+)*/
header('Connection: close');
header('Content-Length: '.ob_get_length());
ob_end_flush();
ob_flush();
flush();

/* Callback */
Settings::CommentCallback($page,$name,$message);
?>
