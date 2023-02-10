<?php
  namespace Interact_PHP;

  class Settings
  {
    /* Path of the library, relative to the root of the website. */
    const LIBRARY_ROOT = '/Interact_PHP'; // default: '/Interact_PHP'

    /* Google's reCAPTCHA anti-spam 'I am not a robot' checkbox. Optionnal
    but recommended. Copy paste your reCAPTCHA v2 keys here. */
    const RECAPTCHA_PUBLIC_KEY = NULL; // default: NULL (disabled)
    const RECAPTCHA_SECRET_KEY = NULL; // default: NULL (disabled)

    /* Stylesheet to use. Available:
    - modern.css
    - modern-dark.css
    - creative.css
    You can add your custom stylesheet in the css folder. */
    const THEME_STYLESHEET = 'modern.css'; // default: 'modern.css'

    /* Absolute path of the folder containing ths comments.
    It will *try* to create it if not existing.
    Warning: this folder's permissions should be set to 777. */
    const COMMENTS_ROOT = __DIR__.'/Comments'; // default: __DIR__.'/Comments'

    /* Option to temporaly disable the comments on all of your website.
    You can set a custom HTML warning for yours users. */
    const DISABLE_COMMENTS = false; // default: false
    const DISABLE_COMMENTS_MESSAGE = "Sorry, comments are temporarily disabled.";
  
    /* If enabled, saves the commenters IP along with their comment in
    the xml files.  */
    const ENABLE_SAVE_COMMENTER_IP = false; // default: false

    /* Title of the comment area (HTML). */
    const TITLE_COMMENT_BOX = "What is on your mind?";

    /* Comment submit comment button text. */
    const COMMENT_BUTTON = "Comment";
  
    /* String displayed when there is not any comment (HTML). */
    const NO_COMMENTS_MESSAGE = "No comments yet!";

    /* Comment max allowed length. */
    const MAX_COMMENT_LENGTH = 2000; // default: 2000
    /* Username max allowed length. */
    const MAX_USERNAME_LENGTH = 30; // default: 30

    /* Enable/disable Markdown syntax in comments. */
    const ENABLE_MARKDOWN_SYNTAX = true; // default: true
  
    /* Advanced feature: this function is called whenever a comment is sent.
    One typical usage is to notify yourself with an email on each new comment.
    The connection to the user will be closed BEFORE this function is called; 
    this means that you can do lengthy computation here.
    Warning: the parameters are the unsanitized user's input. Don't thrust them!*/
    public static function CommentCallback($titleOfPage,$nameOfCommenter,$commentContent) {
      // require_once $_SERVER['DOCUMENT_ROOT'].'/PHPMailer/SMTP.php';
      // require_once $_SERVER['DOCUMENT_ROOT'].'/PHPMailer/PHPMailer.php';
      // require_once $_SERVER['DOCUMENT_ROOT'].'/PHPMailer/Exception.php';
  
      // try {
      //   $mail = new \PHPMailer\PHPMailer\PHPMailer();
  
      //     // Server settings
      //   $mail->IsSMTP(); 
      //   $mail->Host = "ssl://smtp.gmail.com:465";
      //   $mail->SMTPAuth = true; 
      //   $mail->Username = "mail@example.com";
      //   $mail->Password = 'password';
  
      //   // Content
      //   $mail->IsHTML(true);
      //   $mail->Subject = "Comment on ".$titleOfPage;
      //   $mail->Body = 'Comment posted by "'.$nameOfCommenter.'" on '.date('l j F Y, H:i')."\".<br>\n<br>\n".$commentContent;
  
      //   $mail->send();
      // } catch (Exception $e) {}
    }

    private function __construct() {}
  }
?>
