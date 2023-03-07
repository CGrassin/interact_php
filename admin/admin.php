<?php
namespace Interact_PHP;
require_once('../settings.php');
session_start();

/* Deletes a comment from a XML file */
function deleteComment($filename,$id) {
  if (file_exists($filename)) {
    $xml = simplexml_load_string(file_get_contents($filename));
    unset($xml->xpath("/comments/comment[@id='" . $id . "']")[0][0]);
    $xml->asXML($filename);
    return true;
  }
  return false;
}
/* Promote comment as admin (displays badge next to comment) */
function setAdmin($filename, $id, $isAdmin) {
  if (file_exists($filename)) {
    $xml= simplexml_load_string(file_get_contents($filename));
    foreach ($xml->children() as $comment) {
      if ($comment->attributes()['id'] == $id) {
        
        if(!isset($comment->attributes()['admin']))
          $comment->addAttribute('admin','false');
        
        $comment->attributes()['admin'] = $isAdmin ? 'true' : 'false';
        $xml->asXML($filename);
        return true;
      }
    }
  }
  return false;
}
function endOfPage($p='',$isError=false){
  echo $p;
  if($isError)
    echo "<p style=\"text-align:center\"><a href=\"admin.php\" >Go back</a></p>";
  echo '</div></body></html>';
  exit();
}

?>

<!DOCTYPE html>
<html>
<head>
  <title>Interact PHP Admin</title>
  <!-- <link rel="stylesheet" type="text/css" href="github-markdown.css"> -->
  <link rel="stylesheet" type="text/css" href="admin_interface.css">
  <style>

  </style>
</head>
<body>
  <div class="markdown-body">
    <h1>Interact PHP admin</h1>

  <?php
  // If no login/password is set, admin page is disabled
  if (is_null(Settings::ADMIN_PASSWORD)) {
    http_response_code(403);
    endOfPage('<p class="text-center">Admin page is disabled.</p>',true);    
  }

  // POST ACTIONS
  if (isset($_POST['action'])) {
    // Logout
    if (isset($_SESSION['user']) &&  $_POST['action'] === "logout") {
      session_start();
      session_unset();
      session_destroy();
      header("Location: admin.php");
      exit();
    }
    // Delete
    elseif (isset($_SESSION['user']) &&  $_POST['action'] === "delete") {
      if (isset($_POST['filename']) && isset($_POST['id']))
        if (deleteComment(Settings::COMMENTS_ROOT.'/'.$_POST['filename'], $_POST['id'])) {
          header("Location: admin.php");
          exit();
        } else {
          endOfPage('<p class="text-center">Error while deleting this comment.</p>',true);
        }
    }
    // Promote/demote
    elseif (isset($_SESSION['user']) &&  $_POST['action'] === "toggle_admin") {
      if (isset($_POST['filename']) && isset($_POST['id']) && isset($_POST['isAdmin'])) {
        $state = ($_POST['isAdmin'] === "true") ? false : true;
        setAdmin(Settings::COMMENTS_ROOT.'/'.$_POST['filename'], $_POST['id'], $state);
        header("Location: admin.php");
        exit();
      }
    }
    // Login
    elseif (!isset($_SESSION['user']) && $_POST['action'] === "login") {
      if (!isset($_SESSION['user']) && isset( $_POST['password'])) {
        // Check captcha
        if (!is_null(Settings::RECAPTCHA_PUBLIC_KEY) && !is_null(Settings::RECAPTCHA_SECRET_KEY)) {
          if (!isset($_POST['g-recaptcha-response'])) {
            endOfPage('<p class="text-center">Please enable Javascript and/or check the reCAPTCHA!</p>',true);  
          } else {
            $api_url = "https://www.google.com/recaptcha/api/siteverify?secret=".urlencode(Settings::RECAPTCHA_SECRET_KEY)."&response=".urlencode($_POST['g-recaptcha-response'])."&remoteip=".urlencode($_SERVER['REMOTE_ADDR']);

            $decode = json_decode(file_get_contents($api_url), true);

            if ($decode['success'] == false) {
              endOfPage('<p class="text-center">Please check the reCAPTCHA!</p>',true);  
            }
          }
        }
        // Check password
        if(Settings::ADMIN_PASSWORD===hash('sha256', $_POST['password'])) {
          $_SESSION['user'] = "admin";
          header("location: admin.php");
          die();
        }
        else {
          endOfPage('<p class="text-center">Wrong login/password.</p>',true);    
        }
      }
    }
  }

  // Login form
  if(!isset($_SESSION['user'])) {
    echo '<form class="login" method="post">
    <input name="password" type="password" class="login-input" placeholder="Password">';
    if (!is_null(Settings::RECAPTCHA_PUBLIC_KEY)&&!is_null(Settings::RECAPTCHA_SECRET_KEY)) {
      echo '<div class="text-center"><div style="display: inline-block;" class="g-recaptcha" data-sitekey="'.Settings::RECAPTCHA_PUBLIC_KEY.'"></div></div>';
      echo "<script src='https://www.google.com/recaptcha/api.js'></script>";
    }
    echo '<input type="submit" value="Login" class="button button-xl">
          <input type="hidden" name="action" value="login" />
    </form></body></html>"';
    endOfPage();
  }
  ?>

  <form method="post">
    <input type="hidden" name="action" value="logout" />
    <button class="button button-xl button-red">Logout</button>
  </form>

    <h2 id="files-list">Files list</h2>
    <ul>
    <?php 
      $files = scandir(Settings::COMMENTS_ROOT);
      foreach($files as $file) {
        if (pathinfo($file)['extension'] === "xml") {
          echo "<li><a href=\"#".$file."\">".$file."</a></li>";
        }
      }
    ?>
    </ul>

    <?php  
    libxml_use_internal_errors(true);
    foreach($files as $file) {
      if ($file != "." && $file != "..") {
        $xml = simplexml_load_string(file_get_contents(Settings::COMMENTS_ROOT.'/'.$file));
        if($xml === false) 
          continue;
        echo "<h2 id=\"".$file."\">File \"".$file."\"</h2>";

        echo '<table>';
        echo "<th>Name</th><th>Message</th><th>Date</th><th>Actions</th>";

        foreach ($xml->children() as $comment) {
          echo "<tr>";
          echo '<td>'.htmlspecialchars($comment->{"name"});
          if ($comment->attributes()['admin'] == "true")
            echo " <strong>ADMIN</strong>";
          echo '</td>';
          echo '<td>'.htmlspecialchars($comment->{"message"}).'</td>';
          echo '<td>'.date("F j Y, G:i", intval($comment->{"date"})).'</td>';
          echo '<td>';
          echo '<form method="post">
                    <input type="hidden" name="action" value="toggle_admin" />
                    <input type="hidden" name="id" value="'.$comment->attributes()['id'].'" />
                    <input type="hidden" name="filename" value="'.$file.'" />
                    <input type="hidden" name="isAdmin" value="'.$comment->attributes()['admin'].'" />
                    <button  type="submit" class="button">Toggle admin</button>
                  </form>';
          echo '<form method="post" onsubmit="return confirm(\'Do you really want to delete this comment?\');">
                    <input type="hidden" name="action" value="delete" />
                    <input type="hidden" name="id" value="'.$comment->attributes()['id'].'" />
                    <input type="hidden" name="filename" value="'.$file.'" />
                    <button  type="submit" class="button button-red">Delete</button>
                  </form>';
          echo '</td>';
          echo "</tr>";
        }
        echo "</table>";
        echo "<p style=\"text-align:center\"><a href=\"#files-list\" >Back to top</a></p>";
      }
    }
    endOfPage();
    ?>
