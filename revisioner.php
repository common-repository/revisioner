<?php
/*
Plugin Name: Revisioner
Plugin URI: http://www.ipublicis.com
Description: Clears <strong>all revisions</strong> from your database. Like it? <a href="http://smsh.me/7kit" target="_blank" title="Paypal Website"><strong>Donate</strong></a> | <a href="http://www.amazon.co.uk/wishlist/2NQ1MIIVJ1DFS" target="_blank" title="Amazon Wish List">Amazon Wishlist</a>
Author: Lopo Lencastre de Almeida - iPublicis.com
Version: 1.0.1
Author URI: http://www.ipublicis.com
Donate link: http://smsh.me/7kit
*/

/*
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License version 3 as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/


/*
  Changelog:
  
  From now on, see the readme for changelog.
  
*/

// setting some internal information
$revisioner_dirname = plugin_basename(dirname(__FILE__));
$revisioner_url = WP_PLUGIN_URL . '/' . $revisioner_dirname;
$donate = '<a href="http://smsh.me/7kit">donation</a>';

//load translation file if any for the current language
load_plugin_textdomain('revisioner', PLUGINDIR . '/' . $revisioner_dirname . '/locale');

revisioner_revisions();

function rev_init_options() {

// create options array. if options already exists add_option function does nothing.

	$revisioner_options['revisions'] = 5;
	$revisioner_options['autosave'] = 5;
	
	add_option('revisioner_options', $revisioner_options );
  
}

function revisioner_options_subpanel() {

	global $revisioner_url, $donate, $wpdb;

	$rev_options = get_option('revisioner_options');  

	if (get_magic_quotes_gpc()) {
    	$_POST = array_map('rev_bnc_stripslashes_deep', $_POST);
	    $_GET = array_map('rev_bnc_stripslashes_deep', $_GET);
	    $_COOKIE = array_map('rev_bnc_stripslashes_deep', $_COOKIE);
	    $_REQUEST = array_map('rev_bnc_stripslashes_deep', $_REQUEST);
	}


  	if (isset($_POST['info_update'])) {
		// Update Global Options
		$rev_options['revisions'] = intval($_POST['revisions']);
		$rev_options['autosave'] = intval($_POST['autosave']);
		
		if(intval($_POST['clearall']) == 1) {
			$table_prefix = $wpdb->prefix;
		
			$sqlDelete  = 	"DELETE a,b,c FROM `" . $table_prefix . "_posts` a" .
										" LEFT JOIN `" . $table_prefix . "_term_relationships` b ON (a.ID = b.object_id)" .
										" LEFT JOIN `" . $table_prefix . "_postmeta` c ON (a.ID = c.post_id)" .
										" WHERE a.post_type = 'revision'";
			$results = $wpdb->query($sqlDelete);
			
			$delmsg = '<div id="message" class="updated fade"><p><strong>' .
								__('Revisions were removed. Do this from time to time to avoid having your database cluttered with trash.') .
								'</strong></p></div>';
		}

		// Update values
		update_option( 'revisioner_options', $rev_options );
				
		echo '<div id="message" class="updated fade"><p><strong>' . __('Settings saved.') . '</strong></p></div>' . $delmsg;

	} 

?>
<div class="wrap">
	 <div id="icon-options-general" class="icon32"><br /></div>
	 
	 <h2><?php _e('Revisioner Options','revisioner') ?></h2>
 
	<p><?php _e('Revisioner allows you to to define how much revisions per post you want on your blog.','revisioner') ?></p>
	<p><?php _e('You can also remove all current revisions saved in your database. This is not reversible so be careful.','revisioner') ?></p>

  <form method="post" name="options" action="">
      <br />
          
      <table width="100%" cellspacing="0" class="widefat">
        <thead>
          <tr>
            <th width="200"><?php _e('Setting','revisioner'); ?></th>
            <th width="450">&nbsp;</th>
            <th><?php _e('Description','revisioner'); ?></th>
          </tr>
        </thead>
        
		<tr><th><?php _e('Revisions','revisioner') ?></th>
			<td><input type="text" name="revisions" class="widefat" value="<?php echo intval($rev_options['revisions']); ?>" /></td>
			<td class="description"><?php _e('Define here how many revisions you want to keep for every post in your blog. Zero value disables it.','revisioner') ?></td>
		</tr>        
        
		<tr><th><?php _e('Auto Save','revisioner') ?></th>
			<td><input type="text" name="autosave" class="widefat" value="<?php echo intval($rev_options['autosave']); ?>" /></td>
			<td class="description"><?php _e('Define here time elapsed between each revision being saved automaticaly. Zero value disables it.','revisioner') ?></td>
		</tr>        
        
		<tr><th><?php _e('Clear All?','revisioner') ?></th>
			<td>Yes:<input type="radio" value="1" name="clearall">&nbsp;&nbsp;
					<strong>NO</strong><input type="radio" value="0" name="clearall"  checked /></td>
			<td class="description"><?php _e('This will clear <strong>ALL</strong> revisions currently existing in your database. Be careful hence this is not reversible.','revisioner') ?></td>
		</tr>        
        
      </table>

	   <div class="submit"><input type="submit" class="button-primary" name="info_update" value="<?php _e('Save settings','revisioner') ?>" /></div>

     </form>
     
    <p>
		<?php _e("If you find this plugin useful, please consider to make a ".$donate." or send a <a href='http://www.amazon.co.uk/wishlist/2NQ1MIIVJ1DFS'>gift</a> to Revisioner's author (anything will be appreciated).",'revisioner') ?>
    </p>

    </div>

</div>
	<?php
}

function revisioner_add_plugin_option() {
 
    $revisioner_plugin_name = 'Revisioner';
	$revisioner_plugin_prefix  = 'revisioner_';

    if (function_exists('add_options_page')) 
    {
       $rev_options_page = add_management_page($revisioner_plugin_name, $revisioner_plugin_name, 'manage_options', basename(__FILE__), $revisioner_plugin_prefix . 'options_subpanel');
    }
    
}

function revisioner_add_settings_link($links) {
	$settings_link = '<a class="edit" href="options-general.php?page=revisioner.php" title="'. __('Go to settings page','revisioner') .'">' . __('Settings','revisioner') . '</a>';
	array_unshift( $links, $settings_link ); // before other links
	return $links;
}

function revisioner_revisions() {
	
	$rev_options   = get_option('revisioner_options');  
	$rev_num_save = intval($rev_options['revisions']);  
	$rev_auto_save = intval($rev_options['autosave']) * 60;  

	/* limit number of post revisions */ 
	define('WP_POST_REVISIONS', $rev_num_save);
	define('AUTOSAVE_INTERVAL', $rev_auto_save);
}

register_activation_hook( __FILE__, 'rev_init_options' );
add_action('admin_menu', 'revisioner_add_plugin_option');
add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'revisioner_add_settings_link', -10);

?>
