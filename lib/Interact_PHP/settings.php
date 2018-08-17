<?php
  namespace Interact_PHP;

  class Settings
  {
    /* Path of the library, relative to the root of the website. */
    const LIBRARY_ROOT = '/lib/Interact_PHP'; // default: '/lib/Interact_PHP'

    /* Google's reCAPTCHA anti-spam 'I am not a robot' checkbox. Optionnal
    but recommended. Copy paste your reCAPTCHA v2 keys here. */
    const RECAPTCHA_PUBLIC_KEY = NULL; // default: NULL (disabled)
    const RECAPTCHA_SECRET_KEY = NULL; // default: NULL (disabled)

    /* Stylesheet to use. Available:
    - modern.css
    - modern-dark.css
    - creative.css
    You can add your custom stylesheet in the css folder. ss*/
    const THEME_STYLESHEET = 'modern.css'; // default: 'modern.css'

    /* Absolute path of the folder containing ths comments.
    It will *try* to create it if not existing.
    Warning: this folder's permissions should be set to 777. */
    const COMMENTS_ROOT = __DIR__.'/Comments'; // default: __DIR__.'/Comments'

    /* Option to temporaly disable the comments on all of your website.
    You can set a custom warning for yours users. */
    const DISABLE_COMMENTS = false; // default: false
    const DISABLE_COMMENTS_MESSAGE = "Sorry, comments are temporarly disabled.";
  
    /* If enabled, saves the commenters IP along with their comment in
    the xml files.  */
    const ENABLE_SAVE_COMMENTER_IP = false; // default: false

    /* Title of the comment area. */
    const TITLE_COMMENT_BOX = "What is on your mind?";
  
    /* String displayed when there is not any comment. */
    const NO_COMMENTS_MESSAGE = "No comments yet!";
  
    /* Advanced feature: this function is called whenever a comment is sent.
    One typical usage is to notify yourself with a mail on each new comment.
    The conection to the user will be closed BEFORE this function is called; 
    this means that you can do lengthy computation here.
    Warning: the parameters are the user's input. Don't thrust them, be on the safe side!*/
    public static function CommentCallback($titleOfPage,$nameOfCommenter,$commentContent) {
    }

    private function __construct() {}
  }
?>
