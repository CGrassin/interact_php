<?php
namespace Interact_PHP;
require_once('settings.php');

class Interact_PHP_Translations{
  private $strings_xml;
  public function __construct($path){
    $this->strings_xml = simplexml_load_string(file_get_contents($path));
  }

  public function get_string($lang, $id){
    return $this->strings_xml->xpath('/strings/translation[@lang="'.$lang.'"]/string[@name="'.$id.'"]')[0];
  }
}

/* Entry function to display the comment zone. 
Optionnal argument: Page title is the title of the current 
article. You may want to set it juste to keep the folder clear;
It will be taken from the url otherwise. */
function Interact_PHP($pageTitle=NULL, $lang="default"){
  $strings = new Interact_PHP_Translations(__DIR__."/strings.xml");

  // If no title is provided, take it from the url (removing the query string first)
  if (is_null($pageTitle)) {
    $pageTitle=strtok($_SERVER["REQUEST_URI"],'?');
  }
  $pageTitle=SanitizeFilename($pageTitle);
  ?>
  
  <link rel="stylesheet" type="text/css" href="<?php echo Settings::LIBRARY_ROOT.'/css/'.Settings::THEME_STYLESHEET; ?>">
  <script src="<?php echo Settings::LIBRARY_ROOT.'/js/interact.min.js'; ?>"></script>
  
  <div style="width: 100%">
  <div class="comment-box">
  
  <p class="comment-title"><?= $strings->get_string($lang,"title") ?></p>
  
  <?php
  if (Settings::DISABLE_COMMENTS) {
    echo '<p class="text-muted text-center">'.$strings->get_string($lang,"comments-diabled").'</p>';
  }
  else { ?>
    <noscript>
    <p class="text-muted text-center"><?= $strings->get_string($lang,"no-js") ?></p>
    </noscript>
    
    <form class="comment-form hidden" method="post" action="<?php echo Settings::LIBRARY_ROOT.'/postComment.php'; ?>" onsubmit="return interactphpSubmit(this, <?php echo Settings::MAX_USERNAME_LENGTH.",".Settings::MAX_COMMENT_LENGTH; ?>)">
      <div class="interactphp-alert hidden" role="alert"></div>
      <div class="interactphp-info hidden" role="status"><?= $strings->get_string($lang,"sending-comment") ?></div>
      
      <label class="sr-only" for="interactphp-message"><?= $strings->get_string($lang,"comment-label") ?></label>
      <textarea class="input" name="message" rows="3" required maxlength="<?php echo Settings::MAX_COMMENT_LENGTH; ?>" placeholder="<?= $strings->get_string($lang,"comment-placeholder") ?>" onfocus="recaptchaDisplay(this.parentElement.parentElement)"></textarea>
      
      <div class="input-group">
        <div class="interactphp-nickname">
          <label class="sr-only" for="interactphp-name"><?= $strings->get_string($lang,"name-placeholder") ?></label>
          <input class="input" type="text" name="name" placeholder="<?= $strings->get_string($lang,"name-placeholder") ?>" maxlength="<?php echo Settings::MAX_USERNAME_LENGTH; ?>" required onfocus="recaptchaDisplay(this.parentElement.parentElement.parentElement)">
        </div>
        
        <div class="interactphp-submit">
        <button class="input" type="submit"><?= $strings->get_string($lang,"submit-btn") ?></button>
        </div>
      </div>
      
      <input type="hidden" class="hidden" name="page" value="<?php echo $pageTitle; ?>">
      <input type="hidden" class="hidden" name="lang" value="<?php echo $lang; ?>">
      
      <?php
      if (!is_null(Settings::RECAPTCHA_PUBLIC_KEY)&&!is_null(Settings::RECAPTCHA_SECRET_KEY)) {
        echo '<div class="google-recaptcha text-center"><div style="display: inline-block;" class="g-recaptcha" data-sitekey="'.Settings::RECAPTCHA_PUBLIC_KEY.'"></div></div>';
        echo "<script src='https://www.google.com/recaptcha/api.js'></script>";
      } ?>
    </form>
    
    <?php } ?>
    
    <ul class="comment-list">
    <?php displayComments($pageTitle, $strings, $lang); ?>
    </ul>
    </div>
    </div>
    
    <?php
  }
  
  /* Displays the comments for $filename, if any exists. Displays
  a cutomizable 'no comments' message otherwise.
  SECURITY: if for some reason, $page where to be compromized, the
  attacker would NOT be able to see anything else than comments thank
  to the restriction of 'NameToCommentFile'.*/
  function displayComments($page, $strings, $lang) {
    $filename = NameToCommentFile($page);
    if (file_exists($filename)) {
      $xml = simplexml_load_string(file_get_contents($filename));
      $count=0;
      foreach ($xml->children() as $comment) {
        $count++;
        echo '<li class="comment">';
        echo '<p class="comment-author"><span class="comment-rank">#'.$count.'</span> ';
        if ($comment->attributes()['admin'] == "true")
          echo ' <span class="badge">'.$strings->get_string($lang,"admin-badge").'</span> ';
        echo htmlspecialchars($comment->{"name"}).'</p>';
        echo '<p class="comment-message">';
        if(Settings::ENABLE_MARKDOWN_SYNTAX){
          echo parseMarkdown(preg_replace("/\\\\n/","<br>",htmlspecialchars($comment->{"message"})));
        } else {
          echo preg_replace("/\\\\n/","<br>",htmlspecialchars($comment->{"message"}));
        }
        echo '</p>';
        echo '<p class="comment-date text-muted">on '.date("F j Y, G:i", intval($comment->{"date"})).'</p>';
        echo '</li>';
      }
      if ($count===0) {
        echo '<li class="comment"><p>'.$strings->get_string($lang,"no-comment").'</p></li>';
      }
    }
    else {
      echo '<li class="comment"><p>'.$strings->get_string($lang,"no-comment").'</p></li>';
    }
  }
  
  /* Adds a comment to the XML datafile $filename.
  SECURITY: In case the user modified the form to enter a random/nasty 
  '$page', it will simply add a safely sanitized random file in the 
  'Commnents' directory, preventing any harm to be done even if the 
  whole filesystem is in 777.
  SECURITY 2: htmlspecialchars is used to prevent breaking the XML structure
  with nasty input. */
  function addComment($page,$name,$message) {
    try
    {
      $filename = NameToCommentFile($page);
      
      if (!file_exists(Settings::COMMENTS_ROOT)) {
        mkdir(Settings::COMMENTS_ROOT, 0777, true);
      }
      
      if (file_exists($filename)) {
        $xml = simplexml_load_string(file_get_contents($filename));
      } else  {
        $xml = simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?><comments></comments>');
      }
      
      $comment = $xml->addChild('comment');
      $comment->addAttribute('id',uniqid());
      $comment->addChild('date', time());
      $comment->addChild('name', htmlspecialchars($name));
      $comment->addChild('message', htmlspecialchars($message));
      if (Settings::ENABLE_SAVE_COMMENTER_IP) {
        $comment->addChild('ip', htmlspecialchars($_SERVER['REMOTE_ADDR']));
      }
      $xml->asXML($filename);
      return true;
    } catch(Exception $e){}
      return false;
    }
    
    /* Parse Markdown in comments.
    Inspired by https://gist.github.com/jbroadway/2836900 */
    function parseMarkdown($string){
      $rules = array (
        '/(\*\*|__)(.*?)\1/' => '<b>\2</b>',            // bold
        '/(\*|_)(.*?)\1/' => '<i>\2</i>',               // emphasis
        '/\~\~(.*?)\~\~/' => '<del>\1</del>',           // del
        '/`(.*?)`/' => '<code>\1</code>'                // inline code
      );
      foreach ($rules as $regex => $replacement) {
        $string = preg_replace ($regex, $replacement, $string);
      }
      return trim ($string);
    }
    
    /* Sanitizes string to be used as a filename. It takes no chances, and
    works on any filesystem. */
    function SanitizeFilename($page) {
      return substr(preg_replace( '/[^a-z0-9]+/', '-', strtolower( $page ) ),0,30);
    }
    
    /* Converts a page title to a safe filepath to save the comments. */
    function NameToCommentFile($page) {
      return Settings::COMMENTS_ROOT.'/'.SanitizeFilename($page).'.xml';
    }
    ?>
    
