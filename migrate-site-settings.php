<?php
/*
Plugin Name: Migrate Site Settings
Plugin URI: http://developingelegance.com/
Description: This plugin saves your Wordpress site preferences for import into another Wordpress site, making deployment of many sites with the same preferences easy.
Version: 0.1
Author: Tim Mahoney
Author URI: http://timothymahoney.com/
License: GPLv2
*/
/*
Copyright 2010  Tim Mahoney  (email : tim@developingelegance.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

http://www.gnu.org/licenses/gpl.txt
*/



if(!class_exists("Migrate_Site_Settings")){
	class Migrate_Site_Settings {

// List of options we want to save. 
// Next version, maybe make a page where the user can select these themselves?
		var $optionstosave = array(
'admin_email',
'users_can_register',
'default_role',
'timezone_string',
'date_format',
'time_format',
'start_of_week',

'default_post_edit_rows',
'use_smilies',
'use_balanceTags',
'mailserver_url',
'mailserver_login',
'mailserver_pass',
'mailserver_port',
'enable_app',
'enable_xmlrpc',
'ping_sites',

'posts_per_page',
'posts_per_rss',
'rss_use_excerpt',
'blog_charset',

'default_comment_status',
'default_ping_status',
'default_pingback_flag',
'require_name_email',
'comment_registration',
'close_comments_for_old_posts',
'close_comments_days_old',
'thread_comments',
'thread_comments_depth',
'page_comments',
'comments_per_page',
'default_comments_page',
'comment_order',
'comments_notify',
'moderation_notify',
'comment_moderation',
'comment_whitelist',
'comment_max_links',
'moderation_keys',
'blacklist_keys',

'show_avatars',
'avatar_rating',
'avatar_default',

'thumbnail_size_w',
'thumbnail_size_h',
'thumbnail_crop',
'medium_size_w',
'medium_size_h',
'large_size_w',
'large_size_h',
'embed_autourls',
'embed_size_w',
'embed_size_h',
'upload_path',
'upload_url_path',
'uploads_use_yearmonth_folders',

'blog_public',

'permalink_structure',
'category_base',
'tag_base'
);
		
		function __construct( ) {
			add_action( 'admin_menu', 		array( $this, 'addOptionsPage' ) );
		}
		public function addOptionsPage() {
    		add_options_page('Migrate Site Settings', 'Migrate Site Settings', 'administrator', basename(__FILE__), array($this,'optionsPageView'));
		}
		public function optionsPageView() {
			
			// If there's a file upload, process it.
			if ( isset($_POST['fileuploadsubmit']) ) {
				
				// If there's a file upload error, notify the user
				if ($_FILES["xmlfile"]["error"] > 0 || $_FILES["xmlfile"]["type"] != "text/xml") {
					echo "Error: " . $_FILES["xmlfile"]["error"] . "<br />";
				} else {
					// load the file
					$xml = simplexml_load_file($_FILES["xmlfile"]["tmp_name"]);
				}
				
				//Build a list of status updates to tell the user what has been changed.
				$status = "<ol>";
				foreach($xml as $key => $setting) {
					// Trim the SimpleXML crap out of the setting string.
					$setting_final = trim((string)$setting);
					if(get_option($key) != $setting_final ) {
						$status .= '<li>Setting "'.$key.'" used to be '.get_option($key).' and has successfully been set to '.$setting_final.'</li>';
						// Update the option
						update_option($key, $setting_final);
					}
				}
				$status .= "</ol>";
				
			}
			
			if ( isset($_POST['deletexmlsubmit']) ) {
				$path = "../wp-content/plugins/migrate-site-settings";
				$directory = opendir($path);
				while($entryName = readdir($directory)) {
					$dirArray[] = $entryName;
				}
				closedir($directory);
				foreach($dirArray as $file) {
					$ext = substr($file, strrpos($file, '.') + 1);
					// echo $ext;
					if($ext == "xml"){
						unlink($path.'/'.$file);
					}
				}
			}
			
			?>
			<div class="wrap">
			<div id="icon-options-general" class="icon32"><br /></div>
			<h2>Migrate Site Settings</h2>
			<h4>This plugin will export all your basic site preferences / settings, for re-import later.<br>
			Currently it automatically pulls settings from:<br>
			General, Writing, Reading, Discussion, Media, Privacy, and Permalinks.</h4>
			<h4>If you find yourself enjoying this plugin, please <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=CP99UR2YMG5ZJ">donate!</a><br>
			Functionality planned:</h4>
			<ul style="list-style: disc;margin-left: 15px;">
			<li>allow users to select which preferences they would like to export</li>
			<li>come up with a way to export the fact that plugins are installed, and when you import, install and configure them as well.</li>
			<li>Suggestions and constructive criticism are welcome! Just leave a message for me somewhere on my <a href="http://www.developingelegance.com">website!</a></li>
			</ul>
			<p>Hit the button to generate an XML file, and download it for re-use later<br>
			An MD5 hash is used to keep people from linking directly to the file.</p>
			
			<?php
			
			$xml = $this->generateXMLString();
			if ( isset($_POST['generatexmlsubmit']) ) {
				$filename = $this->generateXMLFile($xml);
			}
			?>
			<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>" enctype="multipart/form-data">
			<p><input type="submit" name="generatexmlsubmit" value="Generate XML file"></p>
			<?php
				if ( isset($_POST['generatexmlsubmit']) ) {
			?>
			<p>Right Click and Save Target As: <a href="../wp-content/plugins/migrate-site-settings/<?php echo $filename; ?>" target="_blank"><?php echo $filename; ?></a></p>
			<?php
				}
			?>
			<p><input type="file" name="xmlfile"><input type="submit" name="fileuploadsubmit" value="Upload New Settings" onclick="javascript:return confirm('Are you sure you want to replace the settings?')"></p>
			<p><input type="submit" name="deletexmlsubmit" value="Delete all XML files" onclick="javascript:return confirm('Are you sure you want to delete all XML files?')"></p>
			<?php
			if(isset($status)) {
				echo "<h4>Update Results</h4>";
				if( $status != "<ol></ol>" ) {
					echo $status;
				} else {
					echo "No Changes Needed";
				}
			} else {
				echo "<h4>Current Settings in XML format</h4>";
				echo "<pre>".htmlentities($xml)."</pre>";
			}
			?>
			</form>
			<?php
		}
		
	   /* 
	    * generateXMLString
		* This function generates the XML displayed on the Admin page, and will be used to save the file.
		*/
		public function generateXMLString() {
			$xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
			$xml .= '<site_settings>'."\n";
			foreach($this->optionstosave as $ots){
				$option = get_option( $ots );
				$option = htmlentities($option);
				$xml .= "\t<".$ots.">".$option."</".$ots.">\n";
			}
			$xml .= '</site_settings>';
			return $xml;
		}

	   /* 
	    * generateXMLFile
		* Save the file to the plugin folder so that the user can download it.
		*/
		public function generateXMLFile($xmlString) {
			$filename = "Settings_Export_".md5(time()).".xml";
			$myFile = "../wp-content/plugins/migrate-site-settings/".$filename;
			$fh = fopen($myFile, 'w');
			fwrite($fh, $xmlString);
			fclose($fh);
			return $filename;
		}
		
	}

	
	
}

global $migrateSettings;
$migrateSettings = new Migrate_Site_Settings();


?>
