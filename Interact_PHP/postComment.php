<?php
namespace Interact_PHP;

require_once('settings.php');
require_once('Interact_PHP.php');

// Get strings
$lang = isset($_POST['lang']) ? $_POST['lang'] : 'default';
$strings = new Interact_PHP_Translations(__DIR__."/strings.xml");

/* If comments are disabled but user tried to send a comment with 
some manual POST request, forbid action. */
if (Settings::DISABLE_COMMENTS) {
  echo $strings->get_string($lang, "comments-diabled");
  exit;
}

/* If Google's reCAPTCHA are enabled, check the validity of the 
CAPTCHA.*/
if (!is_null(Settings::RECAPTCHA_PUBLIC_KEY)&&!is_null(Settings::RECAPTCHA_SECRET_KEY)) {
  if (!isset($_POST['g-recaptcha-response'])) 
  {
    echo $strings->get_string($lang, "captcha-error");
    exit;
}
else
{
    $api_url = "https://www.google.com/recaptcha/api/siteverify?secret=".urlencode(Settings::RECAPTCHA_SECRET_KEY)."&response=".urlencode($_POST['g-recaptcha-response'])."&remoteip=".urlencode($_SERVER['REMOTE_ADDR']);

    $decode = json_decode(file_get_contents($api_url), true);

    if ($decode['success'] == false) {
      echo $strings->get_string($lang, "captcha-error");
      exit;
  }
}
}

/* Check the presence of the POST fields. */
if(!isset($_POST['name']) || !isset($_POST['message']) || !isset($_POST['page'])){
    echo $strings->get_string($lang, "system-error");
    exit;
}

/* Check the validity of the input. */
$name=preg_replace("/\r|\n/", " ", $_POST['name']);
if(strlen($name)<=0 || strlen($name)>Settings::MAX_USERNAME_LENGTH){
    echo str_replace("{}", Settings::MAX_USERNAME_LENGTH, $strings->get_string($lang, "name-error"));
    exit;
}
$message=preg_replace("/(\r|\n)+/", "\\n", $_POST['message']);
if(strlen($message)<=0 || strlen($message)>Settings::MAX_COMMENT_LENGTH){
    echo str_replace("{}", Settings::MAX_USERNAME_LENGTH, $strings->get_string($lang, "comment-error"));
    exit;
}
$page=SanitizeFilename($_POST['page']);
if(strlen($page)<=0){
    echo $strings->get_string($lang, "system-error");
    exit;
}

/* Check spam filter. */
foreach (Settings::SPAM_FILTER as $filter) {
    if(str_contains($message, $filter)){
        echo $strings->get_string($lang, "system-error");
        exit;
    }
}

/* At this point, the input is valid. Add the comment to the XML file. */
ignore_user_abort(true);
set_time_limit(0);
ob_start();

if(addComment($page,$name,$message,false)){
    echo "return_ok";
}else{
    echo $strings->get_string($lang, "system-error");
    exit;
}

/* Flush PHP buffer to answer to the AJAX call without having to wait for the
completion of the callback function (which, in the case of emails is 2s+)*/
header('Connection: close');
header('Content-Length: '.ob_get_length());
ob_end_flush();
ob_flush();
flush();

/* Callback */
Settings::CommentCallback($_POST['page'],$name,$_POST['message']);
?>
