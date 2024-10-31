<?php
/*
Plugin Name: NIC Image Gallery
Plugin URI: http://www.indianic.com
Description: An easy image gallery with rollover effect and image slideshow with discription.
Version: 1.0
Author: IndiaNIC
Author URI: http://profiles.wordpress.org/indianic/
*/

class iNIC_images {


	var $pluginPath;
	var $pluginUrl;
	var $rootPath;
	var $wpdb;


	public function __construct() {

		global $wpdb;
		$this->wpdb = $wpdb;
		$this->ds = DIRECTORY_SEPARATOR;
		$this->pluginPath = dirname(__FILE__) . $this->ds;
		$this->rootPath = dirname(dirname(dirname(dirname(__FILE__))));		
		$this->pluginUrl = WP_PLUGIN_URL ."/".trim(dirname(plugin_basename(__FILE__))).$this->ds;

		add_action('admin_menu', array($this, 'nic_image_register_menu'));
		add_action('admin_init', array($this,'nic_image_add_admin_JS_CSS'));
		add_action('add_meta_boxes', array($this,'nic_image_meta_box_add' ));
		add_action( 'save_post', array($this,'nic_image_updated_custom_meta'));
		add_action( 'wp_enqueue_scripts', array($this,'nic_front_JS_CSS'));
		add_shortcode('nic-image-gallery-view-mode', array($this, 'nic_image_view_mode_listing')); //Listing page shortcode

	}

	

	public function nic_image_register_menu() {
		add_submenu_page('edit.php?post_type=nic_image', 'settings', 'Settings', 'manage_options', 'nic_image_options', array($this,'show_menu'));
	}

	function show_menu()
	{
		if (!current_user_can('manage_options'))
		{
			wp_die( __("You do not have sufficient permissions to access this page.","imagetext") );
		}
		echo '<div class="wrap">';
		echo '<h2>'.__("NIC Image gallery settings","imagetext").'</h2>';
		echo '<h2> '.__("Use shortcode","imagetext").' [nic-image-gallery-view-mode] '.__("in post or page content area.","imagetext").'</h2>';
		echo '</div>';
		
		
		echo '<div style="clear:left;"></div>';
		
		echo '<div class="preview_img">';
		echo '<div class="heading">';
			echo '<h3> Set Default Image  </h3>';
		echo '</div>';
		
		echo '<div class="image_view">';
				echo '<img src="'.plugins_url('images/nic_default.png',__FILE__).'"  width="150" height="150" >';	
		echo '</div>';	
		?>
		<div style="float:left;">
		<form method="POST" action="" enctype="multipart/form-data">
			<p>
				<label for="image">Image: </label>
				<input type="file" name="image">
			</p>
			<p><input type="Submit" name="upload" id="upload" value="Upload" ></p>
		</form>
		<?php 	
		
		if(array_key_exists('upload',$_POST))
		{
		 	define('UPLOAD_DIR', $this->pluginPath.'images/');
			$size = 150; // the thumbnail height
			$filedir = UPLOAD_DIR; // the directory for the original image
			$thumbdir = UPLOAD_DIR; // the directory for the thumbnail image
			$prefix = 'small_'; // the prefix to be added to the original name
			$maxfile = '2000000';
			$mode = '0666';
		
			$userfile_name = $_FILES['image']['name'];
			$userfile_tmp = $_FILES['image']['tmp_name'];
			$userfile_size = $_FILES['image']['size'];
			$userfile_type = $_FILES['image']['type'];
			
			$allowed =  array('image/png','image/jpeg' ,'image/gif');
			
			if (isset($_FILES['image']['name']) && in_array($_FILES['image']['type'],$allowed)) 
			{
				
				$file = str_replace(' ', '_', $_FILES['image']['name']);
				$userfile_name = str_replace($file, "nic_default.png",$file);
	
				$prod_img = $filedir.$userfile_name;
				$prod_img_thumb = $thumbdir.$prefix.$userfile_name;
		
				move_uploaded_file($userfile_tmp, $prod_img);
				chmod ($prod_img, octdec($mode));
				
				$sizes = getimagesize($prod_img);
		
				$aspect_ratio = $sizes[1]/$sizes[0]; 
		
				if ($sizes[1] <= $size)
				{
					$new_width = $sizes[0];
					$new_height = $sizes[1];
				}else{
					$new_height = $size;
					$new_width = $size;
				}
				switch(strtolower($sizes['mime']))
				{
				    case 'image/png':
				        $srcimg = imagecreatefrompng($prod_img);
				        break;
				    case 'image/jpeg':
				        $srcimg = imagecreatefromjpeg($prod_img);
				        break;
				    case 'image/gif':
				        $srcimg = imagecreatefromgif($prod_img);
				        break;
				    default: die();
				}
					
				$destimg=ImageCreateTrueColor($new_width,$new_height)
					or die('Problem In Creating image');
				if(function_exists('imagecopyresampled'))
				{
					imagecopyresampled($destimg,$srcimg,0,0,0,0,$new_width,$new_height,ImageSX($srcimg),ImageSY($srcimg))
					or die('Problem In resizing');
				}else{
					Imagecopyresized($destimg,$srcimg,0,0,0,0,$new_width,$new_height,ImageSX($srcimg),ImageSY($srcimg))
					or die('Problem In resizing');
				}
				ImageJPEG($destimg,$prod_img_thumb,90)
					or die('Problem In saving');
				imagedestroy($destimg);
			}
			else
			{
				echo "File format is not support";
			}
	
		}
		echo '</div></div>';
		echo '<div style="clear:left;"></div>';
	}
	
	function nic_front_JS_CSS()
	{
		wp_enqueue_script('jquery');
	}
	function nic_image_add_admin_JS_CSS()
	{

		wp_enqueue_script( 'media-upload' );
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_script( 'quicktags' );
		wp_enqueue_script( 'jquery-ui-resizable' );
		wp_enqueue_script( 'jquery-ui-draggable' );
		wp_enqueue_script( 'jquery-ui-button' );
		wp_enqueue_script( 'jquery-ui-position' );
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_script( 'wpdialogs' );
		wp_enqueue_script( 'wplink' );
		wp_enqueue_script( 'wpdialogs-popup' );
		wp_enqueue_script( 'wp-fullscreen' );
		wp_enqueue_script( 'editor' );
		wp_enqueue_script( 'word-count' );
		wp_enqueue_script( 'img-mb', plugins_url('/js/get-images.js',__FILE__), array( 'jquery','media-upload','thickbox','set-post-thumbnail' ) );
		wp_enqueue_script( 'img-mb1', plugins_url('/js/custom.js',__FILE__), array( 'jquery') );
		wp_enqueue_style( 'img-style', plugins_url('/css/custom-css.css',__FILE__) );
		wp_enqueue_style( 'thickbox' );

	}

	function nic_image_custom_init()
	{
		$labels = array(
		'name' => __("NIC Image Gallery", "imagetext"),
		'singular_name' => __("NIC Image Gallery", "imagetext"),
		'add_new' => __("Add Image", "imagetext"),
		'add_new_item' => __("Add New Image", "imagetext"),
		'edit_item' => __("Edit Image", "imagetext"),
		'new_item' => __("New Image", "imagetext"),
		'all_items' => __("All Images", "imagetext"),
		'view_item' => __("View Image", "imagetext"),
		'search_items' => __("Search Image", "imagetext"),
		'not_found' =>  __("No Image found", "imagetext"),
		'not_found_in_trash' => __("No Image found in Trash", "imagetext"),
		'parent_item_colon' => '',
		'menu_name' => __("NIC Image Gallery", "imagetext")
		);

		$args = array(
		'labels' => $labels,
		'public' => true,
		'publicly_queryable' => true,
		'show_ui' => true,
		'show_in_menu' => true,
		'query_var' => true,
		'show_in_admin_bar' => true,
		'rewrite' => array( 'slug' => 'NIC Image Gallery', 'with_front' => true ),
		'capability_type' => 'page',
		'has_archive' => true,
		'hierarchical' => false,
		'menu_position' => null,
		'can_export' => true,
		'menu_icon' => $this->pluginUrl . "/images/icon.png",
		'supports' => array( 'title','editor','page-attributes')
		);
		register_post_type( 'NIC_image', $args );

	}


	function nic_image_meta_box_add()
	{
		add_meta_box('Additional Images', __('Additional Images', 'imagetext'), array( __CLASS__,'nic_imageboxes'), 'NIC_image', 'normal', 'high');

	}

	function nic_imageboxes($post)
	{

		$list_images = apply_filters('list_images',array(
		'image1' => '_image1',
		'image2' => '_image2',
		'image3' => '_image3',
		'image4' => '_image4'
		), 'false' );

		wp_nonce_field( 'image-liee-save_'.$post->ID, 'image-liee-nonce');
		echo '<div id="droppable">';

		echo '<input type="hidden" id="post_ID" name="post_ID" value="'.$post->ID.'" />';

		$values = get_post_meta($post->ID);
		$check = isset( $values['is_hover'][0] ) ? esc_attr( $values['is_hover'][0] ) : '';

		if($post->post_status == 'auto-draft' || $check == 'on')
		{
			$chk = 'checked="checked"';
		}
		else
		{
			$chk = '';
		}

		echo '<input type="checkbox" id="is_hover" name="is_hover" '.$chk.'  />';
		echo '<label for="is_hover"> Is hover ? </label>';
		echo '<div style="clear:left;"></div>';

		$z =1;
		foreach($list_images as $k=>$i)
		{
			$meta = get_post_meta($post->ID,$k,true);
			if($meta)
			$img =  '<img src="'.wp_get_attachment_thumb_url($meta).'" width="150" height="150" alt="" draggable="false">';
			else
			$img ='<img src="'.plugins_url('images/nic_default.png',__FILE__).'" width="150" height="150" alt="" draggable="false">';

			echo '<div class="image-entry" draggable="true">';
			echo '<input type="hidden" name="'.$k.'" id="'.$k.'" class="id_img" data-num="'.$z.'" value="'.$meta.'">';
			echo '<div class="img-preview" data-num="'.$z.'">'.$img.'</div>';
			echo '<a href="javascript:void(0);" class="get-image button-secondary" data-num="'.$z.'">'.__('Add New','indianictext').'</a><a href="javascript:void(0);" class="del-image button-secondary" data-num="'.$z.'">'.__('Delete','indianictext').'</a>';
			echo '</div>';
			$z++;
		}
		echo '</div>';

		echo '<div style="clear:left;"></div>';

	}

	function nic_image_updated_custom_meta( $post_id )
	{
		if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

		update_post_meta($_POST['ID'],'is_hover',trim($_POST['is_hover']));
		update_post_meta($_POST['ID'],'image1',trim($_POST['image1']));
		update_post_meta($_POST['ID'],'image2',trim($_POST['image2']));
		update_post_meta($_POST['ID'],'image3',trim($_POST['image3']));
		update_post_meta($_POST['ID'],'image4',trim($_POST['image4']));

	}




	function nic_image_view_mode_listing()
	{

		global $wpdb;
		wp_enqueue_script('jquery');
		wp_enqueue_script( 'lytebox', plugins_url('/js/lytebox.js', __FILE__), array('jquery'), '1.0.0' );
		wp_enqueue_style( 'lytebox', plugins_url('css/lytebox.css', __FILE__));
		wp_enqueue_script( 'custom_jquery_hover', plugins_url('/js/jquery-hover-effect.js', __FILE__), array('jquery'),'1.0.0' );
		wp_enqueue_style( 'custom_style', plugins_url('/css/custom-css.css', __FILE__ ));
		wp_enqueue_style( 'default_style', plugins_url('/css/style.css', __FILE__ ));
		load_plugin_textdomain('imagetext', false, dirname(plugin_basename( __FILE__ )));

		?>
		 
	<script type="text/javascript">
	//Image Hover
	jQuery(document).ready(function(){
		jQuery(function() {
			
			jQuery('ul.da-thumbs > li').hoverdir();

		});
	});
	</script>
	   
		<?php
		global $wp;
		$args = array(
		'post_type' => 'NIC_image'
		);


		$query = new WP_Query( $args );

		if($query->have_posts()):
		echo '<div class="image_grid portfolio_4col"><ul style="height: 495px;" id="list" class="portfolio_list da-thumbs">';
		while ($query->have_posts()) : $query->the_post();

		echo "<li>";


		if ( get_post_meta(get_the_ID(), 'image1', true) ) :
		$meta_id1 = get_post_meta(get_the_ID(), 'image1', true);
		$img1 = wp_get_attachment_url($meta_id1);
		$post_thumbnail_id1 = wp_get_attachment_image_src($meta_id1);
		echo '<img class="gridsquare_img" src="'.$post_thumbnail_id1[0].'"  />';
		else:
		echo '<img src="'.plugins_url('images/small_nic_default.png',__FILE__).'"   class="gridsquare_img" >';
		$img1 = plugins_url('images/nic_default.png',__FILE__);
		endif;

		if ( get_post_meta(get_the_ID(), 'image2', true) ) :
		$meta_id2 = get_post_meta(get_the_ID(), 'image2', true);
		$img2= wp_get_attachment_url($meta_id2);
		$post_thumbnail_id2 = wp_get_attachment_image_src($meta_id2);
		echo '<img class="gridsquare_img" src="'.$post_thumbnail_id2[0].'"  />';
		else:
		echo '<img src="'.plugins_url('images/small_nic_default.png',__FILE__).'"  class="gridsquare_img" >';
		$img2 = plugins_url('images/nic_default.png',__FILE__);
		endif;

		if ( get_post_meta(get_the_ID(), 'image3', true) ) :
		$meta_id3 = get_post_meta(get_the_ID(), 'image3', true);
		$img3= wp_get_attachment_url($meta_id3);
		$post_thumbnail_id3 = wp_get_attachment_image_src($meta_id3);
		echo '<img class="gridsquare_img" src="'.$post_thumbnail_id3[0].'"  />';
		else:
		echo '<img src="'.plugins_url('images/small_nic_default.png',__FILE__).'"  class="gridsquare_img" >';
		$img3 = plugins_url('images/nic_default.png',__FILE__);
		endif;

		if ( get_post_meta(get_the_ID(), 'image4', true) ) :
		$meta_id4 = get_post_meta(get_the_ID(), 'image4', true);
		$img4= wp_get_attachment_url($meta_id4);
		$post_thumbnail_id4 = wp_get_attachment_image_src($meta_id4);
		echo '<img class="gridsquare_img" src="'.$post_thumbnail_id4[0].'" />';
		else:
		echo '<img class="gridsquare_img" src="'.plugins_url('images/small_nic_default.png',__FILE__).'" >';
		$img4 = plugins_url('images/nic_default.png',__FILE__);
		endif;


		$is_hover =  get_post_meta(get_the_ID(), 'is_hover', true);
		if($is_hover)
		{
			?>
		
		<a class="lytebox" data-lyte-options="group:orion<?php echo the_ID(); ?> titleTop:true navTop:true"  data-description="<?php echo esc_attr( the_content() ); ?>" data-title="<?php echo esc_attr(the_title()); ?>"  href="<?php echo $img1;?>">
				<article class="da-animate da-slideFromRight" style="display: block;">
					<h3>
						<?php echo the_title(); ?>
					</h3>	
					<div class="forspan"><span class="zoom"></span></div>
				</article>
		</a> 
			
		
		<div style="display:none;">
			<a class="lytebox"  data-lyte-options="group:orion<?php echo the_ID(); ?> titleTop:true navTop:true"  data-description="<?php echo esc_attr( the_content() ); ?>" data-title="<?php echo esc_attr(the_title()); ?>" href="<?php echo $img2;?>" >
				<?php echo '<img class="" src="'.$img2.'"  />'; ?>
			</a>
			
			<a class="lytebox" data-lyte-options="group:orion<?php echo the_ID(); ?> titleTop:true navTop:true"   data-description="<?php echo esc_attr( the_content() ); ?>" data-title="<?php echo esc_attr(the_title()); ?>" href="<?php echo $img3;?>">
				<?php echo '<img class="" src="'.$img3.'"  />'; ?>
			</a>
			
			<a class="lytebox" data-lyte-options="group:orion<?php echo the_ID(); ?> titleTop:true navTop:true"  data-description="<?php echo esc_attr( the_content() ); ?>" data-title="<?php echo esc_attr(the_title()); ?>" href="<?php echo $img4;?>">
				<?php echo '<img class="" src="'.$img4.'" title = "'.esc_attr( the_content() ).'" />'; ?>
			</a>
		</div>
		
		<?php
		}

		echo "</li>";
		endwhile;

		echo "</ul></div>";
		endif;

		return;
	}


}

add_action("init", "register_nic_image_gallery_plugin");

function register_nic_image_gallery_plugin() {
	global $nic_image,$post;
	$nic_image = new iNIC_images();
	$nic_image->nic_image_custom_init();
}

register_activation_hook(__FILE__, 'nicImageInstall');
global $jal_db_version;
$jal_db_version = "1.1";

function nicImageInstall() {

	global $wpdb;
	global $jal_db_version;

}

$installed_ver = get_option("jal_db_version1");

if ($installed_ver != $jal_db_version) {
	nicImageInstall();
}

