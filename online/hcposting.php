<?php

/*
 * MyPHPpa
 * Copyright (C) 2003 Jens Beyer
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

if ($nowin && $nowin == 1) {
  $close_script = "";
} else  {
  $close_script="<SCRIPT LANGUAGE=\"javascript\">\n".
    "<!--\n".
    "// Begin\n".
    "function wclose() {".
    "  this.close();".
    "}\n// END\n//-->\n</SCRIPT>\n";
}

if ($create) {
     $extra_header = "   <TITLE>Create new thread</TITLE>\n$close_script";
     $mail_id = $reply; 
} else if ($reply) {
     $extra_header = "   <TITLE>Reply</TITLE>\n$close_script";
} else {
     $extra_header = "   <TITLE>Edit</TITLE>\n$close_script";
}
require "standard_pop.php";

if ($submit) {
  require "post_func.inc";
  check_post();
}

require "planet_util.php";
require "logging.php";

function get_alliance () {
  global $db, $myrow;

  if ($myrow[alliance_id] == 0) return 0;

  $q = "SELECT * FROM alliance WHERE id='$myrow[alliance_id]'";
  $res = mysql_query( $q, $db);
  if ($res)
    $all = mysql_fetch_array($res);
  else
    return 0;
  $all["status"] = !($myrow["status"] & 2);

  return $all;
}

$all =  get_alliance();

if ($all && $all["hc"] == $Planetid &&
    ($all["members"] > 2 || $Planetid<=2)) {
  $gal_id = 1023;
} else {
  echo "You are not allowed to be here.";
  die;
} 

if ($submit) {

  if ((("$subject"!="") || $reply || $edit)  
      && "$text"!="") {

    if (!$reply && !$edit) {
      $q = "INSERT INTO politics SET planet_id='$Planetid', date=NOW(),".
	 "subject='$subject',gal_id='$gal_id',creator='$all[hcname]'";
      $result = mysql_query ($q, $db);
      $thid = mysql_insert_id ($db);

    } else {
      // check if id is valid

      if ($reply) {
        $postid = $reply;
        $q = "SELECT gal_id FROM politics ".
 	   "WHERE id='$reply' AND gal_id='$gal_id'";
      } else {
        $postid = $edit;
        $q = "SELECT gal_id FROM politics,poltext ".
           "WHERE poltext.id='$edit' AND politics.id=poltext.thread_id ".
           "AND gal_id='$gal_id'";
      }
      $result = mysql_query ($q, $db);

      if ($result && mysql_num_rows($result) == 1) {
	$thid = $postid;

        if ($reply) {
          $q = "UPDATE politics SET date=NOW(),planet_id='$Planetid', ".
             "replies=replies+1 WHERE id='$thid'";
          $result = mysql_query ($q, $db);

          $q = "UPDATE planet set has_politics = has_politics | 2 ".
             "WHERE alliance_id='$myrow[alliance_id]' AND id!='$Planetid'";
          $result = mysql_query ($q, $db);
        }
      } else {
	echo "Wrong parameter found - this incidence will be reported<br>";
	$q = "INSERT INTO news set planet_id=1,date=now(),type=10,".
	   "text='Alliance Forum warning\npid: $Planetid\ntext=$text\n'";
	$result = mysql_query ($q, $db);
        do_log_me (3, 1, "Wrong alliance forum post");
      }
    }

    if ($thid) {
      if (!$edit) {
        $q = "INSERT INTO poltext SET thread_id='$thid',text='$text',".
	   "planet_id='$Planetid',date=NOW()";
        $msg = "Successfully posted";
      } else {
        // edit
        $dbtext = $text . "\n*** Edited ***";
        $q = "UPDATE poltext SET text='$dbtext' ".
           "WHERE id='$thid' AND planet_id='$Planetid'";
        $text = $dbtext;
        $msg = "Successfully edited your post";
      }
      $result = mysql_query ($q, $db);

    }

  } else {
    echo "You have to suply all fields<br>";
  }
}

echo "<center><br>";
if ($msg) {
  echo "$msg<br>";
}
?>

<form method="post" action="<?php 

if ($reply) {
  echo "$PHP_SELF?reply=$reply";
  $postid = $reply;
} else if ($edit) {
  echo "$PHP_SELF?edit=$edit";
} else {
  echo $PHP_SELF;
}

?>">

<table width="500" border="1">
<tr><th class="f3" colspan="2">
<?php

$text = "";
$subject = "";

if ($reply) {
  $q = "SELECT subject FROM politics WHERE id='$reply'";
  $result = mysql_query ($q, $db);

  if ($result && mysql_num_rows($result) == 1) {
    $row=mysql_fetch_array($result);

    $subject = ereg_replace ("<", "&lt;",$row[0]);
  } else 
    $subject = "Empty";
    
  $text = "";
  echo "HC Alliance Forum: Reply to thread</th></tr>";
} else if ($edit) {
  // needs check for planetid

  $q = "SELECT subject,text FROM politics, poltext ".
    "WHERE politics.id=poltext.thread_id AND poltext.id='$edit'";
  $result = mysql_query ($q, $db);

  if ($result && mysql_num_rows($result) == 1) {
    $row=mysql_fetch_array($result);

    $subject = ereg_replace ("<", "&lt;",$row[0]);
    $text = ereg_replace ("<", "&lt;",$row[1]);
  } else {
    $subject = "Empty";
  }

  echo "HC Aliance Forum: Edit Posting</th></tr>";
  
} else {
  $text = "";
  echo "HC Alliance Forum: New thread</th></tr>";
}

?>

<tr><td class="f3" width="80">Subject:</th>
<td width="420">
<?php
if (!$subject || $subject == "") {
  echo "<input type=\"text\" name=\"subject\" size=\"55\" maxlength=\"249\">";
} else {
  echo "$subject";
}
?>

</td></tr>
<tr><td colspan="2" width="50">
<textarea name="text" cols="60" rows="12" wrap="virtual"><? echo $text ?></textarea>
</td></tr><tr><td colspan="2" align="center">
<input type="submit" name="submit" value="      Submit      ">
&nbsp;&nbsp; &nbsp;
<input type="reset" value="Clear posting">
&nbsp;&nbsp; &nbsp;
</td>
</tr>
</table>
</form>

<table width="500" border="0"><tr><td align="right">
<?php
  if($mysettings & 2) {
    if ($reply)
      echo "<a href=\"hcforum.php?thread=$reply\">Return to thread</a>\n";
    else
      echo "<a href=\"hcforum.php\">Return to HC Forum</a>\n";
  } else {
    echo "<a href=\"javascript:close()\">Close this Window</a>\n";
  }

echo "</td></tr></table>\n\n";

require "footer.php";
?>
