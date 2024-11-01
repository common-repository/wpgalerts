<?php
/*
Plugin Name: WPGAlerts
Plugin URI: http://www.datainterlock.com
Description: Google Alerts on Wordpress. This plugin is designed to take a low volume of Google Alerts and add them to any WordPress page or text widget. Please be sure to visit the plugin page and read the documentation.
Version: 1.0
Author: DataInterlock
Author URI: http://www.datainterlock.com
License: GPL3

Copyright (C) 2014 Rod Kinnison postmaster@datainterlock.com

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

*/

defined('ABSPATH') OR exit;

function WPGAlerts_plugin_menu()
	{
	$icon_url = plugin_dir_url(__FILE__) . 'images/WPGAicon.png';
	add_menu_page('WPGAlerts', 'WPGAlerts', 'manage_options', 'WPGAlerts-Main-Menu', 'WPGAlerts_plugin_main_menu', $icon_url);
	add_options_page('WPGAlerts Options', 'WPGAlerts', 'manage_options', 'WPGAlerts-Options-Menu', 'WPGAlerts_plugin_options');
	}

function WPGAlerts_plugin_options()
	{
	global $wpdb;
	if (isset($_POST['maxalerts']) && ($_POST['maxalerts'] <> ''))
		{
		$maxalerts = $_POST['maxalerts'];
		if ($maxalerts > 20)
			{
			$maxalerts = 20;
			}
		if ($maxalerts < 1)
			{
			$maxalerts = 1;
			}
		update_option("WPGAlerts_Max_Alerts", $maxalerts);
		update_option("WPGAlerts_Strip_Tags", $_POST['striptags']);
		update_option("WPGAlerts_Content_Post", $_POST['postcontent']);
		update_option("WPGAlerts_Content_Pre", $_POST['precontent']);
		update_option("WPGAlerts_Author_Post", $_POST['postauthor']);
		update_option("WPGAlerts_Author_Pre", $_POST['preauthor']);
		update_option("WPGAlerts_Title_Post", $_POST['posttitle']);
		update_option("WPGAlerts_Title_Pre", $_POST['pretitle']);
		}
	echo '<h2>WPGAlerts Options</h3><p>For complete information on configuring WPGAlerts, please visit our support website at: <a href="http://www.datainterlock.com" target="_blank">http://www.datainterlock.com</a></p>
<form id="form1" name="form1" method="post" action="">
  <table width="100%" border="0" cellspacing="5" cellpadding="5">
    <tr>
      <th width="28%" scope="row"><div align="right">Maximum number of alerts to display</div></th>
      <td width="72%"><div align="left">
        <label for="maxalerts"></label>
        <input type="text" name="maxalerts" id="maxalerts" value="' . get_option("WPGAlerts_Max_Alerts") . '" />
      </div></td>
    </tr>
    <tr>
      <th scope="row"><div align="right">Strip tags from alerts</div></th>
      <td><div align="left">
        <label for="striptags"></label>
        <select name="striptags" id="striptags">';
	if (get_option("WPGAlerts_Strip_Tags") == "No")
		{
		echo '<option value="No">No</option><option value="Yes">Yes</option>';
		}
	else
		{
		echo '<option value="Yes">Yes</option><option value="No">No</option>';
		}
	echo '</select>
      </div></td>
    </tr>
    <tr>
      <th scope="row"><div align="right">Display before Title</div></th>
      <td><div align="left">
        <label for="pretitle"></label>
        <input name="pretitle" type="text" id="pretitle" maxlength="50" value="' . get_option("WPGAlerts_Title_Pre") . '"/>
      </div></td>
    </tr>
    <tr>
      <th scope="row"><div align="right">Display after Title</div></th>
      <td><div align="left">
        <input name="posttitle" type="text" id="posttitle" maxlength="50" value="' . get_option("WPGAlerts_Title_Post") . '"/>
      </div></td>
    </tr>
    <tr>
      <th scope="row"><div align="right">Display before Author</div></th>
      <td><div align="left">
        <input name="preauthor" type="text" id="preauthor" maxlength="50" value="' . get_option("WPGAlerts_Author_Pre") . '"/>
      </div></td>
    </tr>
    <tr>
      <th scope="row"><div align="right">Display after Author</div></th>
      <td><div align="left">
        <input name="postauthor" type="text" id="postauthor" maxlength="50" value="' . get_option("WPGAlerts_Author_Post") . '"/>
      </div></td>
    </tr>
    <tr>
      <th scope="row"><div align="right">Display before Content</div></th>
      <td><div align="left">
        <input name="precontent" type="text" id="precontent" maxlength="50" value="' . get_option("WPGAlerts_Content_Pre") . '"/>
      </div></td>
    </tr>
    <tr>
      <th scope="row"><div align="right">Display after Content</div></th>
      <td><div align="left">
        <input name="postcontent" type="text" id="postcontent" maxlength="50" value="' . get_option("WPGAlerts_Content_Post") . '"/>
      </div></td>
    </tr>
	<tr><td colspan="2"><input name="Update" type="submit" value="Update" /></td></tr>
  </table>
</form>
';
	}
function WPGAlerts_url_exists($url) 
{
$headers = @get_headers($url);
if(strpos($headers[0],'200')===false)
{return false;}
else
{return true;}
}

function WPGAlerts_valid_xml($url)
{
    $xml = XMLReader::open($url);
    $xml->setParserProperty(XMLReader::VALIDATE, true);
    if ($xml->isValid())
	{
		return TRUE;
	}
	else
	{
		return FALSE;
	}
}

function WPGAlerts_check_feed()
	{
	global $wpdb;
	$table_name = $wpdb->prefix . "WPGAFeeds";
	$mylink     = $wpdb->get_results("SELECT Feed,Title FROM $table_name");
	foreach ($mylink as $link)
		{
			if (WPGAlerts_url_exists($link->Feed) && WPGAlerts_valid_xml($link->Feed))
			{
			$WPGAxml    = simplexml_load_file($link->Feed);
			$table_name = $wpdb->prefix . "WPGAlerts";
			foreach ($WPGAxml->entry as $entry)
				{
				$checklink = $wpdb->get_results("SELECT * FROM $table_name where GID='$entry->id'");
				if (count($checklink) == 0)
					{
						$wpdb->insert($table_name, array(
						'GID' => $entry->id,
						'Content' => $entry->content,
						'Title' => $entry->title,
						'Link' => $entry->link['href'],
						'Author' => $entry->author->name,
						'Published' => $entry->published
					));
					}
				}
			}
			else
			{
				echo '<h3>Feed: '.$link->Title.' no longer exists.</h3>';
			}
		}
	}

function WPGAlerts_plugin_main_menu()
	{
	global $wpdb;
	if (!current_user_can('manage_options'))
		{
		wp_die(__('You do not have sufficient permissions to access this page.'));
		}
	$table_name = $wpdb->prefix . "WPGAlerts";
	if (isset($_POST['IDnumber']) && $_POST['IDnumber'] <> '')
		{
		foreach ($_POST['IDnumber'] as $linkid)
			{
			$wpdb->update($table_name, array(
				'Approved' => 0
			), array(
				'ID' => $linkid
			));
			}
		if (isset($_POST['ApproveArt']) && $_POST['ApproveArt'] <> '')
			{
			
			foreach ($_POST['ApproveArt'] as $linkid)
				{
				$wpdb->update($table_name, array(
					'Approved' => '1' // string
				), array(
					'ID' => $linkid
				));
				}
			}
		}
	if (isset($_POST['DeleteArt']) && $_POST['DeleteArt'] <> '')
		{
		foreach ($_POST['DeleteArt'] as $linkid)
			{
			$wpdb->delete($table_name, array(
				'ID' => $linkid
			));
			}
		}
	if (isset($_POST['Clear']))
	{
		$sql="Truncate table $table_name";
		$wpdb->query($sql);
	}
	$table_name = $wpdb->prefix . "WPGAFeeds";
	if (isset($_POST['WPGAXML']) && $_POST['WPGAXML'] <> '')
		{
			if (WPGAlerts_url_exists($_POST['WPGAXML']) && WPGAlerts_valid_xml($_POST['WPGAXML']))
			{
			$WPGAxml       = simplexml_load_file($_POST['WPGAXML']);
			$rows_affected = $wpdb->insert($table_name, array(
				'Feed' => $_POST['WPGAXML'],
				'Title' => $WPGAxml->title
			));
			}
			else
			{
				echo '<h3>Feed: does not exists.</h3>';
			}
		}

	if (isset($_POST['WPGACheck']) && $_POST['WPGACheck'] <> '')
		{
		foreach ($_POST['WPGACheck'] as $linkid)
			{
			$wpdb->delete($table_name, array(
				'ID' => $linkid
			));
			}
		}
	echo '<div class="wrap">';
	echo '<p><h2>WPGAlerts.</h2></p><p>For complete information on configuring WPGAlerts, please visit our support website at: <a href="http://www.datainterlock.com" target="_blank">http://www.datainterlock.com</a></p>';
	echo '<hr>';
	echo '<p><h3>Active Google Alerts XML Feeds</h3></p>';
	$mylink = $wpdb->get_results("SELECT * FROM $table_name");
	echo '<form id="form1" name="form1" method="post" action="">';
	$found = FALSE;
	if (count($mylink) == 0)
		{
		echo 'No Google Alert XML links found. Please add one below<br>';
		}
	else
		{
		$found = TRUE;
		foreach ($mylink as $link)
			{
			echo '  <input type="checkbox" name="WPGACheck[]" value="' . $link->ID . '" id="WPGACheck" /><label for="WPGACheck"></label>';
			echo "<a href='$link->Feed' target='_blank'>$link->Title</a><br>";
			}
		}
	if ($found)
		{
		echo '<p><input type="submit" name="Add" id="Add" value="Delete Checked" /></p>';
		}
	echo '<hr><p><h3>Add a new XML feed<h3></p><label for="WPGAXML"></label><input type="text" name="WPGAXML" id="WPGAXML size="50" maxlength="500" />
  			<input type="submit" name="Add" id="Add" value="Add" /></p>';
	echo '</div><hr>';
	echo '<p><input type="submit" name="Clear" id="Clear" value="Delete Old Articles" /></p><p>By clicking this button the entire article database will be cleared. WPGAlerts will then re-scan all of your XML feeds and load only the newest articles into the database.</p>';
	echo '<hr></form>';
	WPGAlerts_check_feed();
	echo '<p><h3>Current Alert Articles</h3></p>';
	$table_name = $wpdb->prefix . "WPGAlerts";
	if (isset($_GET["page"]))
		{
		$page = $_GET["page"];
		}
	else
		{
		$page = 1;
		}
	;
	$itemlimit = 5;
	if (isset($_GET["p2"]))
		{
		$page = $_GET["p2"];
		}
	else
		{
		$page = 1;
		}
	;
	$start_from = ($page - 1) * $itemlimit;
	$mylink     = $wpdb->get_results("SELECT * FROM $table_name LIMIT $start_from, $itemlimit");
	if (count($mylink) == 0)
		{
		echo "No Alerts Found";
		}
	else
		{
		echo '<form id="form2" name="form2" method="post" action="">';
		echo '<table width="100%" border="0" align="left">';
		foreach ($mylink as $link)
			{
			echo '<tr>';
			echo '<th align="left" width="16%" scope="row"><input type="checkbox" name="ApproveArt[]" id="Approve" value="' . $link->ID . '"';
			if ($link->Approved == 1)
				{
				echo ' checked';
				}
			echo '/>';
			echo '<input name="IDnumber[]" type="hidden" value="' . $link->ID . '" />';
			echo '<label for="Approve"></label>';
			echo 'Approved</th>';
			echo '<td>Title:</td>';
			echo '<td colspan="4"><h3>' . $link->Title . '</h3></td>';
			
			echo '</tr>';
			echo '<tr><td></td>';
			echo '<td width="5%">ID:</td>';
			echo '<td width="17%">' . $link->ID . '</td>';
			echo '<td width="10%">Google ID:</td>';
			echo '<td width="52%">' . $link->GID . '</td>';
			
			echo '</tr>';
			echo '<tr>';
			echo '<th scope="row">&nbsp;</th>';
			echo '<td>Content:</td>';
			echo '<td colspan="3">' . $link->Content . '</td>';
			echo '</tr>';
			echo '<tr>';
			echo '<th scope="row">&nbsp;</th>';
			echo '<td>Author:</td>';
			echo '<td colspan="3">' . $link->Author . '</td>';
			echo '</tr>';
			echo '<tr>';
			echo '<th scope="row" align="left" ><input type="checkbox" name="DeleteArt[]" id="Delete" value="' . $link->ID . '"/>';
			echo '<label for="Delete"></label>';
			echo 'Delete</th>';
			echo '<td>Publish Date:</td>';
			echo '<td colspan="3">' . $link->Published . '</td>';
			echo '</tr>';
			echo '<tr><td></td><td colspan="4"><hr></td></tr>';
			}
		$mylink        = $wpdb->get_results("SELECT * FROM $table_name");
		$total_records = count($mylink);
		$total_pages   = ceil($total_records / $itemlimit);
		$rootpage      = $_SERVER['ORIG_PATH_INFO'];
		$query         = $_SERVER['QUERY_STRING'];
		$query         = explode('&', $query);
		$thispage      = $rootpage . $_SERVER['SCRIPT_NAME'] . '?' . $query[0];
		echo '<tr><td colspan="5" align="center"><';
		for ($i = 1; $i <= $total_pages; $i++)
			{
			echo "<a href='" . $thispage . "&p2=" . $i . "'>" . $i . "</a> ";
			}
		;
		echo '></td></tr>';
		echo '<tr><td colspan="5"><input name="Update" type="submit" value="Update" /></td></tr>';
		echo '</table>';
		echo '</form>';
		}
	}

function WPGAlerts_install()
	{
	global $wpdb, $WPGAlerts_db_version;
	if (!current_user_can('activate_plugins'))
		return;
	
	$table_name = $wpdb->prefix . "WPGAlerts";
	
	$sql = "CREATE TABLE IF NOT EXISTS `$table_name` (
  `ID` int(11) NOT NULL auto_increment,
  `Title` varchar(255) NOT NULL,
  `Link` varchar(255) NOT NULL,
  `Content` mediumtext NOT NULL,
  `Author` varchar(255) NOT NULL,
  `Published` datetime NOT NULL,
  `Approved` tinyint(1) NOT NULL default '0',
  `GID` varchar(255) NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=20 ;
";
	
	$wpdb->query($sql);
	
	$table_name = $wpdb->prefix . "WPGAFeeds";
	
	$sql = "CREATE TABLE IF NOT EXISTS `wp_WPGAFeeds` (
  `ID` int(11) NOT NULL auto_increment,
  `Feed` varchar(500) NOT NULL,
  `Title` varchar(50) NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;
";
	
	$wpdb->query($sql);
	
	add_option("WPGAlerts_Content_Post", "", "", "no");
	add_option("WPGAlerts_Content_Pre", "Content: ", "", "no");
	add_option("WPGAlerts_Author_Post", "");
	add_option("WPGAlerts_Author_Pre", "Author: ", "", "no");
	add_option("WPGAlerts_Title_Post", "", "", "no");
	add_option("WPGAlerts_Title_Pre", "Title: ", "", "no");
	add_option("WPGAlerts_Max_Alerts", 10, "", "no");
	add_option("WPGAlerts_Strip_Tags", "Yes", "", "no");
	add_option("WPGAlerts_db_version", $WPGAlerts_db_version, "", "no");
	
	}

function WPGAlerts_shortcode($atts)
	{
	global $wpdb;
	$striptags   = get_option("WPGAlerts_Strip_Tags");
	$MaxAlerts   = get_option("WPGAlerts_Max_Alerts");
	$titlepre    = get_option("WPGAlerts_Title_Pre");
	$titlepost   = get_option("WPGAlerts_Title_Post");
	$authorpre   = get_option("WPGAlerts_Author_Pre");
	$authorpost  = get_option("WPGAlerts_Author_Post");
	$contentpre  = get_option("WPGAlerts_Content_Pre");
	$contentpost = get_option("WPGAlerts_Content_Post");
	
	$table_name = $wpdb->prefix . "WPGAlerts";
	$mylink     = $wpdb->get_results("SELECT Title,Author,Content,Link FROM $table_name where Approved='1' LIMIT $MaxAlerts");
	$output     = '';
	if (count($mylink) == 0)
		{
		$output = "No approved news was found.";
		}
	else
		{
		foreach ($mylink as $link)
			{
			if (get_option("WPGAlerts_Strip_Tags") == "Yes")
				{
				$title   = strip_tags($link->Title);
				$author  = strip_tags($link->Author);
				$content = strip_tags($link->Content);
				}
			else
				{
				$title   = $link->Title;
				$author  = $link->Author;
				$content = $link->Content;
				}
			$output .= '<p>';
			$output .= $titlepre . '<a href="' . $link->Link . '" target="_blank">' . $title . $titlepost . '</a><br>';
			$output .= $authorpre . $author . $authorpost . '<br>';
			$output .= $contentpre . $content . $contentpost . '<br>';
			$output .= '</p>';
			}
		}
	return ($output);
	}

function WPGAlerts_wp_incompat_notice()
	{
	echo '<div class="error"><p>';
	printf(__('WPGAlerts requires WordPress %s or above. Please upgrade to the latest version of WordPress to enable WPGAlerts or deactivate WPGAlerts to remove this notice.', 'WPGAlerts'), WPGAlerts_MINIMUM_WP_VER);
	echo "</p></div>\n";
	}

function WPGAlerts_deactivation()
	{
	if (!current_user_can('activate_plugins'))
		return;
	$plugin = isset($_REQUEST['plugin']) ? $_REQUEST['plugin'] : '';
	check_admin_referer("deactivate-plugin_{$plugin}");
	//exit( var_dump( $_GET ) );
	}

function WPGAlerts_plugin_meta_links($links, $file)
	{
	
	$plugin = plugin_basename(__FILE__);
	
	// create link
	if ($file == $plugin)
		{
		return array_merge($links, array(
			'<a href="http://www.datainterlock.com">Premium Version</a>'
		));
		}
	return $links;
	
	}
/********************* Defines *******************************/

define('WPGAlerts_MINIMUM_WP_VER', '3.5');
$WPGAlerts_db_version = "1.0";

/********** VERSION CHECK & INITIALIZATION **********/

global $wp_version;
if (version_compare($wp_version, WPGAlerts_MINIMUM_WP_VER, '>='))
	{
	register_activation_hook(__FILE__, 'WPGAlerts_install');
	add_action('admin_menu', 'WPGAlerts_plugin_menu');
	add_shortcode('WPGAlerts', 'WPGAlerts_shortcode');
	register_deactivation_hook(__FILE__, 'WPGAlerts_deactivation');
	//	register_uninstall_hook(    __FILE__, 'WPGAlerts_uninstalll' );
	//add_filter( 'plugin_row_meta', 'WPGAlerts_plugin_meta_links', 10, 2 );
	}
else
	{
	add_action('admin_notices', 'WPGAlerts_wp_incompat_notice');
	}


?>