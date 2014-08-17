<?php
/*
Plugin Name: myQaptcha
Version:     1.0.4
Plugin URI:  http://blog.30c.org/2006.html
Description: 在单页文章评论处添加滑动解锁,使用Session技术防止垃圾评论和机器人,让你不用整天忙于文章审核.纯绿色插件,不修改数据库、无需中转页面、无需加载任何第三方代码、安装简单卸载干净、轻巧迅速
Author:      Clove
Author URI:  http://blog.30c.org
License: GPL v2 - http://www.gnu.org/licenses/gpl.html
*/

function my_scripts_method() {
    wp_deregister_script( 'jquery' );
	wp_deregister_script( 'jquery ui' );
    wp_register_script( 'jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js');
	wp_register_script( 'jquery ui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js');	
    wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'jquery ui' );
} 

function myQaptcha_wp_footer() {
	if (is_singular() && !is_user_logged_in()) {
		$url = get_bloginfo("wpurl");
		$outer = '<link rel="stylesheet" href="' . $url . '/wp-content/plugins/myqaptcha/jquery/myQaptcha.jquery.css" type="text/css" />'."\n";		
		$outer.= '<script type="text/javascript" src="' . $url . '/wp-content/plugins/myqaptcha/jquery/jquery.ui.touch.js"></script>'."\n";
		$outer.= '<script type="text/javascript">var myQaptchaJqueryPage="' . $url . '/wp-content/plugins/myqaptcha/jquery/myQaptcha.jquery.php";</script>'."\n";
		$outer.= '<script type="text/javascript" src="' . $url . '/wp-content/plugins/myqaptcha/jquery/myqaptcha.jquery.js"></script>'."\n";		
		$outer.= '<script type="text/javascript">jQuery(document).ready(function(){if(jQuery("p:has(\'textarea\')").length>0) jQuery("p:has(\'textarea\')").before(\'<div class="QapTcha"></div>\'); else jQuery("#comment").before(\'<div class="QapTcha"></div>\');jQuery(\'.QapTcha\').QapTcha({disabledSubmit:true,autoRevert:true});});</script>'."\n";
		echo $outer;
	} 
}

function myQaptcha_preprocess_comment($comment) {
	if (!is_user_logged_in()) {
		if(!session_id()) session_start();
		if ( isset($_SESSION['30corg']) && $_SESSION['30corg']) {
			unset($_SESSION['30corg']);
			return($comment);
		} else {
			if (isset($_POST['isajaxtype']) && $_POST['isajaxtype'] > -1) {
				//header('HTTP/1.1 405 Method Not Allowed');   clove   find some error with ajax submit  2012-03-02
				die("请滑动滚动条解锁");
			} else {
				if(function_exists('err'))
					err("请滑动滚动条解锁");
				else 
					wp_die("请滑动滚动条解锁");
			} 
		} 
	} else {
		return($comment);
	} 
} 
add_action('wp_enqueue_scripts', 'my_scripts_method');
add_action('wp_footer', 'myQaptcha_wp_footer');
add_action('preprocess_comment', 'myQaptcha_preprocess_comment');
?>