<?php
// TODO
// * File with all texts
// * better sanitation
// * admin

// Features :
// * comments in xml
// * Super simple setup (no DB, no login, no cookies, 2 lines to add a comment section to any page)
// * Customizable style, easy to match look & feel to your website
// * email on new comment
// * PHP 5 & 7
namespace Interact_PHP;

require_once('settings.php');


/* Entry function to display the comment zone. 
Optionnal argument: Page title is the title of the current 
article. You may want to set it juste to keep the folder clear;
It will be taken from the url otherwise. */
function Interact_PHP($pageTitle=NULL){

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

      <p class="comment-title"><?php echo Settings::TITLE_COMMENT_BOX; ?></p>

      <?php
      if (Settings::DISABLE_COMMENTS) {
       echo '<p class="text-muted text-center">'.Settings::DISABLE_COMMENTS_MESSAGE.'</p>';
     }
     else { ?>

       <noscript>
        <style>#commentForm {display:none;}</style>
        <p class="text-muted text-center">Please enable javascript to comment.</p>
      </noscript>

      <form class="comment-form" method="post" action="<?php echo Settings::LIBRARY_ROOT.'/postComment.php'; ?>" id="commentForm" onsubmit="return interactphpSubmit()">
        <div id="interactphp-alert" class="hidden" role="alert">Error sending comment...</div>

        <label class="sr-only" for="interactphp-message">Comment</label>
        <textarea id="interactphp-message" class="input" name="message" rows="3" required maxlength="<?php echo Settings::MAX_COMMENT_LENGTH; ?>" placeholder="Enter your comment..." onblur="recaptchaDisplay()"></textarea>

        <div class="input-group">
          <div class="interactphp-nickname">
            <label class="sr-only" for="interactphp-name">Nickname</label>
            <input id="interactphp-name" class="input" type="text" name="name" placeholder="Nickname" maxlength="<?php echo Settings::MAX_USERNAME_LENGTH; ?>" required onblur="recaptchaDisplay()">
          </div>

          <div class="interactphp-submit">
            <button class="input" type="submit"><?php echo Settings::COMMENT_BUTTON; ?></button>
          </div>
        </div>

        <input type="text" class="hidden" name="page" value="<?php echo $pageTitle?>">

        <?php
        if (!is_null(Settings::RECAPTCHA_PUBLIC_KEY)&&!is_null(Settings::RECAPTCHA_SECRET_KEY)) {
          echo '<div id="google-recaptcha" class="text-center"><div style="display: inline-block;" class="g-recaptcha" data-sitekey="'.Settings::RECAPTCHA_PUBLIC_KEY.'"></div></div>';
          echo "<script src='https://www.google.com/recaptcha/api.js'></script>";
        } ?>

      </form>

    <?php } ?>

    <ul class="comment-list">
      <?php displayComments($pageTitle); ?>
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
function displayComments($page=NULL) {
  $filename = NameToCommentFile($page);
  if (file_exists($filename)) {
    $xml= simplexml_load_string(file_get_contents($filename));
    $count=0;
    foreach ($xml->children() as $comment) {
     $count++;
     echo '<li class="comment">';
     echo '<p class="comment-author"><span class="comment-rank">#'.$count.'</span> '.htmlspecialchars($comment->{"name"});
     if ($comment->attributes()['admin'] == "true") {
       echo ' <span class="badge">'.Settings::ADMIN_BADGE.'</span>';
     }
     echo '</p>';
     echo '<p class="comment-message">'.parseMarkdown(preg_replace("/\\\\n/","<br>",htmlspecialchars($comment->{"message"}))).'</p>';
     echo '<p class="comment-date text-muted">on '.date("F j Y, G:i", intval($comment->{"date"})).'</p>';
     echo '</li>';
   }
 }
 else {
  echo '<li class="comment"><p>'.Settings::NO_COMMENTS_MESSAGE.'</p></li>';
}
}

/* Adds a comment to the XML datafile $filename.
SECURITY: In case the user modified the form to enter a random/nasty 
'$page', it will simply add a safely sanitized random file in the 
'Commnents' directory, preventing any harm to be done even if the 
whole filesystem is in 777.
SECURITY 2: htmlspecialchars is used to prevent breaking the XML structure
with nasty input. */
function addComment($page,$name,$message,$isAdmin=FALSE) {
  try
  {
    $filename = NameToCommentFile($page);

    if (!file_exists(Settings::COMMENTS_ROOT)) {
      mkdir(Settings::COMMENTS_ROOT, 0777, true);
    }

    if (file_exists($filename)) 
    {
     $xml = simplexml_load_string(file_get_contents($filename));
   } 
   else 
   {
    $xml = simplexml_load_string('<?xml version="1.0" encoding="UTF-8"?><comments></comments>');
  }

  $comment = $xml->addChild('comment');
  $comment->addAttribute('id',uniqid());
  if ($isAdmin === TRUE) {
    $comment->addAttribute('admin','true');
  }
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

/* Deletes a comment from a XML file */
function deleteComment($filename,$id) {
  if (file_exists($filename)) {
    $xml = simplexml_load_string(file_get_contents($filename));
    unset($xml->xpath("//comments/comment[@id='" . $id . "']")[0][0]);
    $xml->asXML($filename);
    return true;
  }
  return false;
}

/* Promote comment as admin (displays badge next to comment) */
function setAdmin($filename,$id,$isAdmin) {
  if (file_exists($filename)) {
    $xml= simplexml_load_string(file_get_contents($filename));
    foreach ($xml->children() as $comment) {
      if ($comment->attributes()['id'] == $id) {

        if(!isset($comment->attributes()['admin']))
          $comment->addAttribute('admin','false');

        if ($isAdmin === true)
          $comment->attributes()['admin'] = 'true';
        else
          $comment->attributes()['admin'] = 'false';

        $xml->asXML($filename);
        return true;
      }
    }
  }
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
