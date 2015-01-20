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

require_once "popup_header.inc";
require_once "standard.php";

require_once "forum.inc";

$fstyle = 0;
$fid = 0;

$msg .= forum_init ($fstyle, $fid);

top_header ($myrow);

$msg .= forum_submit ($fstyle, $fid, $fthread);

$ftitle = forum_title ($fstyle);

titlebox ($ftitle, $msg);

echo "<center>\n";

if ($fthread) {
  forum_show_thread ($fstyle, $fid, $fthread);
} else {
  forum_list_thread ($fstyle, $fid);
}

require "footer.inc";
?>
