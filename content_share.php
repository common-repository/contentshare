<?php
/*
Plugin Name: ContentShare
Version: 1.0.5
Description: Facebook and Twitter Buttons
Author: cicerofeijo
Plugin URI: http://cicerofeijo.com/wordpress_plugins/
Author URI: http://cicerofeijo.com
License: GPLv2
*/

/*
This program is free software; you can redistribute it and/or modify 
it under the terms of the GNU General Public License as published by 
the Free Software Foundation; version 2 of the License.

This program is distributed in the hope that it will be useful, 
but WITHOUT ANY WARRANTY; without even the implied warranty of 
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the 
GNU General Public License for more details. 

You should have received a copy of the GNU General Public License 
along with this program; if not, write to the Free Software 
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA 
*/

if (!class_exists("ContentShare")) {
	class ContentShare {
		var $wp_plugin_url;
	
		function __construct() { //constructor
			$this->setPluginUrl();
			$this->insertStyleCode();
			add_action('admin_menu', array(&$this, 'createAdminMenu'), 1);
		}
		
		##############################################################
		# PRIVATE METHODS
		##############################################################

		private function setPluginUrl() {
			$this->wp_plugin_url = trailingslashit( get_bloginfo('wpurl') ).PLUGINDIR.'/'. dirname( plugin_basename(__FILE__) );
		}
		
		/*
		* insertStyleCode : add style code on the head of the page
		*/
		private function insertStyleCode() {
			wp_enqueue_style('contentshare-style', $this->wp_plugin_url .'/css/content_share.css');
		}

		/*
		* addTWScriptCode : add tw script code on the head of the page
		*/
		private function addTWScriptCode() {
			wp_enqueue_script('twitter-widget', 'http://platform.twitter.com/widgets.js');
		}

		/*
		* addFbMetaTags : add the metatags on the head of the page
		*/		
		private function addFbMetaTags(){
			$title = is_home() ? get_bloginfo('title') : get_the_title();

			$metaTags = '<meta property="og:title" content="' . $title . '" />';
			if(get_option('contentShare_fbMetaType') != ''){
				$metaTags.= '<meta property="og:type" content="' . get_option('contentShare_fbMetaType') . '" />';
			}
			if(get_option('contentShare_fbMetaAdmins') != ''){
				$metaTags.= '<meta property="fb:admins" content="' . get_option('contentShare_fbMetaAdmins') . '" />';
			}
			if(get_option('contentShare_fbMetaImage') != ''){
				$metaTags.= '<meta property="og:image" content="' . get_option('contentShare_fbMetaImage') . '" />';
			}

			$metaTags.= '<meta property="og:url" content="' . get_permalink() . '" />';
			
			return $metaTags;
		}

		/*
		* addGooglePlusOne : add the google plus one script on the header
		*/
		private function addGooglePlusOneScript(){
			if(get_option('contentShare_googlePlusOne') != ''){
				$language = get_option('contentShare_googlePlusOneLanguage') == '' ? 'en-US' : get_option('contentShare_googlePlusOneLanguage');
				$language = "{lang: '" . $language . "'}";
				$scriptPlusOne = '<script type="text/javascript" src="https://apis.google.com/js/plusone.js">' . $language . '</script>';
				
				return $scriptPlusOne;
			}else{
				return '';
			}
		}		
		
		/*
		* addFbLikeCode : add the fbLike code on the post
		*/ 
		private function addFbLikeCode(){
			$url        = get_permalink();
			$locale     = get_option('contentShare_fbLikeLanguage') == '' || get_option('contentShare_fbLikeLanguage') == false ? 'en_US' : get_option('contentShare_fbLikeLanguage');
			$sendButton = get_option('contentShare_fbSend') != '' ? 'send=true&amp;' : '';
			$fbAction   = get_option('contentShare_fbAction') == '' ? 'like' : get_option('contentShare_fbAction');
			$width      = $sendButton != '' ? '220' : '90';
			$iframeLike = '<iframe class="fb-like" height="25" width="' . $width . '" scrolling="no" frameborder="0" allowtransparency="true" src="http://www.facebook.com/plugins/like.php?locale=' . $locale . '&amp;href=' . $url . '&amp;' . $sendButton . 'layout=button_count&amp;show_faces=false&amp;action=like&amp;colorscheme=light&amp;action=' . $fbAction . '" class="facebooklike-layout-button-count"></iframe>';
			
			return $iframeLike;
		}

		/*
		* addFbShareCode : add the fbShare code on the post
		*/ 		
		private function addFbShareCode(){
			$url      = get_permalink();
			$popUp    = "window.open('http://www.facebook.com/share.php?u=" . $url . "','facebookWindow','width=640,height=390')";
			$imgShare = get_option('contentShare_fbShareIcon') != '' ? get_option('contentShare_fbShareIcon') : $this->wp_plugin_url . '/img/btShare.gif';
			$icon     = '<img src="' . $imgShare . '"/>';
			
			$shareButton = '<a href="javascript:void(0);" class="fb-share" onClick="' . $popUp . '"/>' . $icon . '</a>';
			
			return $shareButton;
		}
		
		/*
		* addTwCode	: add the twitt button on the page
		*/
		private function addTwCode(){
			$url  = get_permalink();
			$via  = get_option('contentShare_twVia');
			$text = get_the_title($post->post_title);
			$lang = get_option('contentShare_twitterLanguage');
			
			$iframeTw = '<a href="http://twitter.com/share" class="twitter-share-button" data-lang="' . $lang . '" data-via="' . $via . '" data-url="' . $url . '" data-text="' . $text . '" data-count="horizontal">Tweet</a>';

			return $iframeTw;
		}
		
		/*
		* addGooglePlusOne : add the google plus one button on the post
		*/
		private function addGooglePlusOne(){
			$size     = get_option('contentShare_googlePlusOneSize') == '' ? 'medium' : get_option('contentShare_googlePlusOneSize');
			$cssClass = $size;
			$size     = 'size="' . $size . '"';
			$count    = get_option('contentShare_googlePlusOneCount') == "checked" ? 'true' : 'false';
			$cssClass.= $count == 'true' ? 'Count' : '';
			$count    = ' count="' . $count . '" ';
			
			
			return '<div class="googlePlusOne ' . $cssClass . '"><g:plusone ' . $size . $count . '></g:plusone></div>';
		}
		
		/*
		* addContentSharerBlock : add the content share panel on the post
		*/		
		public function addContentSharerBlock() {
			$align = get_option('contentShare_positionRight') != '' ? 'right' : 'left';
		
			$contentSharerBlock = '<div class="contentSharer"><div class="contentSharerPanel ' . $align . '">';

			$contentSharerBlock.= get_option('contentShare_googlePlusOne') != '' ? $this->addGooglePlusOne() : '';			
			$contentSharerBlock.= get_option('contentShare_twShare') != '' ? $this->addTwCode() : '';
			$contentSharerBlock.= get_option('contentShare_fbShare') != '' ? $this->addFbShareCode() : '';
			$contentSharerBlock.= get_option('contentShare_fbLike') != '' ? $this->addFbLikeCode() : '';
			
			$contentSharerBlock.= '</div></div>';
			
			return $contentSharerBlock;
		}
		
		# ADMIN MENU
		private function saveAdminOptions(){
			
			# =============================
			# facebook
			# =============================				
			# fbLike
			if(isset($_POST['fbLike'])){
				update_option('contentShare_fbLike', $_POST['fbLike']);
			}else{
				update_option('contentShare_fbLike', '');
			}

			# fbLikeLanguage
			if(isset($_POST['fbLikeLanguage'])){
				update_option('contentShare_fbLikeLanguage', $_POST['fbLikeLanguage']);
			}else{
				update_option('contentShare_fbLikeLanguage', '');
			}
			
			# fbAction
			if(isset($_POST['fbAction'])){
				update_option('contentShare_fbAction', $_POST['fbAction']);
			}else{
				update_option('contentShare_fbAction', '');
			}
		
			#fbShare
			if(isset($_POST['fbShare'])){
				update_option('contentShare_fbShare', $_POST['fbShare']);
			}else{
				update_option('contentShare_fbShare', '');
			}
			#fbSend
			if(isset($_POST['fbSend'])){
				update_option('contentShare_fbSend', $_POST['fbSend']);
			}else{
				update_option('contentShare_fbSend', '');
			}	
			#fbShareIcon
			if(isset($_POST['fbShareIcon'])){
				update_option('contentShare_fbShareIcon', $_POST['fbShareIcon']);
			}else{
				update_option('contentShare_fbShareIcon', '');
			}
		
			# fb meta tags
			if(isset($_POST['fbMetaImage'])){
				update_option('contentShare_fbMetaImage', $_POST['fbMetaImage']);
			}else{
				update_option('contentShare_fbMetaImage', '');
			}
			if(isset($_POST['fbMetaType'])){
				update_option('contentShare_fbMetaType', $_POST['fbMetaType']);
			}else{
				update_option('contentShare_fbMetaType', '');
			}
			if(isset($_POST['fbMetaAdmins'])){
				update_option('contentShare_fbMetaAdmins', $_POST['fbMetaAdmins']);
			}else{
				update_option('contentShare_fbMetaAdmins', '');
			}				
			
			# =============================
			# googlePlusOne
			# =============================	
			if(isset($_POST['googlePlusOne'])){
				update_option('contentShare_googlePlusOne', $_POST['googlePlusOne']);
			}else{
				update_option('contentShare_googlePlusOne', '');
			}
	
			#googlePlusOneLanguage
			if(isset($_POST['googlePlusOneLanguage'])){
				update_option('contentShare_googlePlusOneLanguage', $_POST['googlePlusOneLanguage']);
			}else{
				update_option('contentShare_googlePlusOneLanguage', '');
			}
			
			#googlePlusOneSize
			if(isset($_POST['googlePlusOneSize'])){
				update_option('contentShare_googlePlusOneSize', $_POST['googlePlusOneSize']);
			}else{
				update_option('contentShare_googlePlusOneSize', '');
			}
			
			#googlePlusOneCount
			if(isset($_POST['googlePlusOneCount'])){
				update_option('contentShare_googlePlusOneCount', $_POST['googlePlusOneCount']);
			}else{
				update_option('contentShare_googlePlusOneCount', '');
			}
			
			# =============================
			# Twitter
			# =============================
			if(isset($_POST['twShare'])){
				update_option('contentShare_twShare', $_POST['twShare']);
			}else{
				update_option('contentShare_twShare', '');
			}
			
			#twLanguage
			if(isset($_POST['twitterLanguage'])){
				update_option('contentShare_twitterLanguage', $_POST['twitterLanguage']);
			}else{
				update_option('contentShare_twitterLanguage', '');
			}
			
			# via
			if(isset($_POST['twVia'])){
				update_option('contentShare_twVia', $_POST['twVia']);
			}else{
				update_option('contentShare_twVia', '');
			}			
			
			# =============================
			# position
			# =============================
			if(isset($_POST['position'])){
				update_option('contentShare_positionLeft', '');
				update_option('contentShare_positionRight', '');
				
				switch($_POST['position']){
					case 'left':
						update_option('contentShare_positionLeft', 'checked');
					break;
					case 'right':
						update_option('contentShare_positionRight', 'checked');
					break;
					default:

					break;
				}
			}

			if(isset($_POST['positionY'])){
				update_option('contentShare_positionTop', '');
				update_option('contentShare_positionBottom', '');
				
				switch($_POST['positionY']){
					case 'top':
						update_option('contentShare_positionTop', 'checked');
					break;
					case 'bottom':
						update_option('contentShare_positionBottom', 'checked');
					break;
					default:

					break;
				}
			}
		}
		
		##############################################################
		# PUBLIC METHODS
		##############################################################		
		
		# ADMIN MENU
		/*
		* showAdminOptions : show the config panel on the admin environment
		*/
		public function showAdminOptions(){
			if(isset($_POST['save'])){
				$this->saveAdminOptions($_POST);
			}
			
			$html = '<form method="POST" id="contentSharerAdminForm">';
			$html.= '<h3>Content Share Configuration</h3>';
			$html.= '<div class="session networks">&raquo; Networks to Share</div>';
			
			# =============================
			# networks panel selection
			# =============================			
			$html.= '<div class="networkPanel">';
			$html.= '<div class="network twitter">';
			$html.= '<input type="checkbox" name="twShare" id="twShare" value="checked" ' . get_option('contentShare_twShare') . '><label for="twShare">Tweet Button</label>';
			$html.= '</div>';

			$html.= '<div class="network facebook">';
			$html.= '<input type="checkbox" name="fbLike" id="fbLike" value="checked" ' . get_option('contentShare_fbLike') . '><label for="fbLike">FB Like</label><br/>';
			$html.= '<input type="checkbox" name="fbSend" id="fbSend" value="checked" ' . get_option('contentShare_fbSend') . '><label for="fbSend">FB Send - <font color="red">problematic</font></label><br/>';
			$html.= '<input type="checkbox" name="fbShare" id="fbShare" value="checked" ' . get_option('contentShare_fbShare') . '><label for="fbShare">FB Share</label><br/>';
			$html.= '</div>';
			
			$html.= '<div class="network googlePlusOne">';
			$html.= '<input type="checkbox" name="googlePlusOne" id="googlePlusOne" value="checked" ' . get_option('contentShare_googlePlusOne') . '><label for="googlePlusOne">Google Plus One</label><br/>';			
			$html.= '</div>';
			$html.= '<div class="clear"></div>';
			$html.= '</div>';
			
			# =============================
			# position on blog
			# =============================
			$html.= '<div class="session position">&raquo; Position on your site</div>';
			$html.= '<input type="radio" name="position" id="position" value="left" ' . get_option('contentShare_positionLeft') . '>Left<br/>';
			$html.= '<input type="radio" name="position" id="position" value="right" ' . get_option('contentShare_positionRight') . '>Right<br/>';
			$html.= '<input type="radio" name="positionY" id="positionY" value="top" ' . get_option('contentShare_positionTop') . '>Top<br/>';
			$html.= '<input type="radio" name="positionY" id="positionY" value="bottom" ' . get_option('contentShare_positionBottom') . '>Bottom<br/>';			
			
			# =============================
			# facebook configuration
			# =============================
			$html.= '<div class="session facebook">&raquo; Facebook Configuration</div>';
			
			$html.= '<label for="fbLikeLanguage">Facebook Like Language:</label><br/>';
			
			$arrLanguages = array('en_US','es_ES','pt_BR');
			
			$html.= '<select id="fbLikeLanguage" name="fbLikeLanguage">';
			for($a = 0; $a < sizeof($arrLanguages); $a++){
				if($arrLanguages[$a] == get_option('contentShare_fbLikeLanguage')){
					$selected = 'selected';
				}else{
					$selected = '';
				}
			
				$html .= '<option value="' . $arrLanguages[$a] . '"' . $selected . '>' . $arrLanguages[$a] . '</option>';
			}
			$html.= '</select>';
			$html.= ' *default is en_US';
			$html.= '<br/>';
			
			$html.= '<label for="fbAction">Facebook Like Label:</label><br/>';
			$arrFBAction  = array('like','recommend');
			$actualAction = get_option('contentShare_fbAction') == '' ? 'recommend' : get_option('contentShare_fbAction');
			$html.= '<select id="fbAction" name="fbAction">';
			for($a = 0; $a < sizeof($arrFBAction); $a++){
				if($arrFBAction[$a] == $actualAction){
					$selected = 'selected';
				}else{
					$selected = '';
				}
			
				$html .= '<option value="' . $arrFBAction[$a] . '"' . $selected . '>' . $arrFBAction[$a] . '</option>';
			}
			$html.= '</select>';
			
			$html.= '<label for="fbShareIcon">Facebook Share Icon:</label><br/>';
			$html.= '<input type="text" name="fbShareIcon" id="fbShareIcon" value="' . get_option('contentShare_fbShareIcon') . '" size="100" />';
			$html.= '<br/>';
			
			# meta tags
			$html.= '<label for="fbMetaImage">Facebook Meta Tag (og:image):</label><br/>';
			$html.= '<input type="text" name="fbMetaImage" id="fbMetaImage" value="' . get_option('contentShare_fbMetaImage') . '" size="100" />';
			$html.= '<br/>';
			$html.= '<label for="fbMetaType">Facebook Meta Tag (og:type):</label><br/>';
			
			$metaType = get_option('contentShare_fbMetaType') == '' || get_option('contentShare_fbMetaType') == false ? 'article' : get_option('contentShare_fbMetaType');
			
			$html.= '<input class="required" type="text" name="fbMetaType" id="fbMetaType" value="' . $metaType . '" size="100" /> *you need fill this field to use Facebook Like (default is "article")';
			$html.= '<br/>';
			$html.= '<label for="fbMetaAdmins">Facebook Meta Tag (fb:admins):</label><br/>';
			$html.= '<input class="required" type="text" name="fbMetaAdmins" id="fbMetaAdmins" value="' . get_option('contentShare_fbMetaAdmins') . '" size="100" /> *you need fill this field to use Facebook Like';
			$html.= '<p>For more information about this parameters use <a href="http://developers.facebook.com/docs/reference/plugins/like/" target="_blank">FB Docs</a></p>';			
			
			# =============================
			# twitter configuration
			# =============================
			$html.= '<div class="session twitter">&raquo; Twitter Configuration</div>';
			$html.= 'Profile to follow (via @...)<br/>';
			$html.= '<input type="text" name="twVia" id="twVia" value="' . get_option('contentShare_twVia') . '" maxlength="50" size="20" /><br/>';
			
			$arrTwLanguages    = array('en', 'fr', 'de', 'it', 'ja', 'ko', 'pt', 'ru', 'es', 'tr');
			$arrTwLanguagesStr = array('English', 'French', 'German', 'Italian', 'Japanese', 'Korean', 'Portuguese', 'Russian', 'Spanish', 'Turkish');
			$actualTwLanguage  = get_option('contentShare_twitterLanguage') == '' ? 'en' : get_option('contentShare_twitterLanguage');
			
			$html.= '<label for="twitterLanguage">Language:</label><br/>';
			$html.= '<select id="twitterLanguage" name="twitterLanguage">';
			for($i = 0; $i < sizeof($arrTwLanguages); $i++){
				if($arrTwLanguages[$i] == $actualTwLanguage){
					$selected = 'selected';
				}else{
					$selected = '';
				}
				
				$html .= '<option value="' . $arrTwLanguages[$i] . '"' . $selected . '>' . $arrTwLanguagesStr[$i] . '</option>';
			}				
			$html.="</select>";
			$html.= '<p>For more information about this parameters use <a href="http://twitter.com/about/resources/tweetbutton" target="_blank">Twitter Docs</a></p>';			
			
			# =============================
			# google plus one
			# =============================
			$arrLanguagesPlusOne = array('ar','bg','ca','zh-CN','zh-TW','hr','cs','da','nl','en-US','en-GB','et','fil','fi','fr','de','el','iw','hu','id','it','ja','ko','lv','lt','ms','no','fa','pl','pt-BR','pt-PT','ro','ru','sr','sv','sk','sl','es','es-419','tr','uk','vi');
			$arrLanguagesPlusOneStr = array('Árabe - العربية','Búlgaro - български','Catalão - català','Chinês (simplificado) - 中文 &rlm;（簡体）','Chinês (tradicional) - 中文 &rlm;（繁體）','Croata - hrvatski','Tcheco - čeština', 'Dinamarquês - dansk', 'Holandês - Nederlands', 'Inglês (EUA) - English &rlm;(US)', 'Inglês (Reino Unido) - English &rlm;(UK)', 'Estoniano - eesti', 'Filipino - Filipino', 'Finlandês - suomi', 'Francês - français', 'Alemão - Deutsch', 'Grego - Ελληνικά', 'Hebraico - עברית', 'Húngaro - magyar', 'Indonésio - Bahasa Indonesia', 'Italiano - italiano', 'Japonês - 日本語', 'Coreano - 한국어', 'Letão - latviešu', 'Lituano - lietuvių', 'Malaio - Bahasa Melayu', 'Norueguês - norsk', 'Persa - فارسی', 'Polonês - polski', 'Português (Brasil) - português &rlm;(Brasil)', 'Português (Portugal) - Português &rlm;(Portugal)', 'Romeno - română', 'Russo - русский', 'Sérvio - српски', 'Sueco - svenska', 'Eslovaco - slovenský', 'Esloveno - slovenščina', 'Espanhol - español', 'Espanhol (América Latina) - español &rlm;(Latinoamérica y el Caribe)', 'Turco - Türkçe', 'Ucraniano - українська', 'Vietnamita - Tiếng Việt');
			$actualPlusOneLanguage = get_option('contentShare_googlePlusOneLanguage') == '' ? 'en-US' : get_option('contentShare_googlePlusOneLanguage');

			$html.= '<div class="session googlePlusOne">&raquo; Google Plus One Configuration</div>';
			$html.= '<label for="googlePlusOneLanguage">Language:</label><br/>';
			$html.= '<select id="googlePlusOneLanguage" name="googlePlusOneLanguage">';
			for($b = 0; $b < sizeof($arrLanguagesPlusOne); $b++){
				if($arrLanguagesPlusOne[$b] == $actualPlusOneLanguage){
					$selected = 'selected';
				}else{
					$selected = '';
				}
			
				$html .= '<option value="' . $arrLanguagesPlusOne[$b] . '"' . $selected . '>' . $arrLanguagesPlusOneStr[$b] . '</option>';
			}						
			$html.= '</select><br/>';
			
			$arrSizePlusOne = array('small', 'medium', 'standard', 'tall');
			$actualSizePlusOne = get_option('contentShare_googlePlusOneSize');
			$html.= '<label for="googlePlusOneSize">Size:</label><br/>';
			$html.= '<select id="googlePlusOneSize" name="googlePlusOneSize">';
			
			for($c = 0; $c < sizeof($arrSizePlusOne); $c++){
				if($arrSizePlusOne[$c] == $actualSizePlusOne){
					$selected = 'selected';
				}else{
					$selected = '';
				}
			
				$html .= '<option value="' . $arrSizePlusOne[$c] . '"' . $selected . '>' . $arrSizePlusOne[$c] . '</option>';
			}						
			$html.= '</select><br/>';			
			
			$html.= '<label for="googlePlusOneCount">Counter:</label><br/>';			
			$html.= '<input type="checkbox" name="googlePlusOneCount" id="googlePlusOneCount" value="checked" ' . get_option('contentShare_googlePlusOneCount') . '/> Show count</br>';
			$html.= '<p>For more information about Google Plus One <a href="http://code.google.com/intl/pt-BR/apis/+1button/" target="_blank">Google Plus One Docs</a></p>';			
			
			# =============================
			# end form
			# =============================
			$html.= '<input type="hidden" name="action" id="action" value="save">';
			$html.= '<hr/>';

			$html.= '<div class="clear"></div>';
			$html.= '<input type="submit" name="save" id="save" value="Save Configuration">';
			$html.= '</form>';
			# =============================
			# donate form
			# =============================			
			$html.= '<br/><form action="https://www.paypal.com/cgi-bin/webscr" method="post">';
			$html.= '<input type="hidden" name="cmd" value="_s-xclick">';
			$html.= '<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----">';
			$html.= '<input name="btDonate" style="float: left;" type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!"><div class="donate">Thank you for using Content Share!</div>';
			$html.= '<img alt="" border="0" src="https://www.paypalobjects.com/pt_BR/i/scr/pixel.gif" width="1" height="1">';
			$html.= '</form>';			
			
			echo $html;
		}
		
		/*
		* createAdminMenu : create the admin menu on CMS
		*/
		public function createAdminMenu(){
			add_menu_page('Content Share Options', 'Content Share', 'manage_options', 'configure-content-share', array(&$this, 'showAdminOptions'));
		}
		
		/*
		* addHeaderCode : add the scripts and css files on the header of the page
		*/
		public function addHeaderCode() {
			$this->addTWScriptCode();
			echo $this->addFbMetaTags();
			echo $this->addGooglePlusOneScript();
		}
		
		/*
		* addPostCode : add the contentShare panel on the post
		*/
		public function addPostCode($thePost) {
		
			if(get_option('contentShare_positionTop') != ''){
				return $this->addContentSharerBlock() . $thePost;
			}else{
				return $thePost . $this->addContentSharerBlock();
			}
			
		}
	}
}

if (class_exists("ContentShare")) {
	$cs = new ContentShare();
}

//Actions and Filters	
if (isset($cs)) {
	# to site
	add_action('wp_head', array(&$cs, 'addHeaderCode'), 1);
	add_action('the_content', array(&$cs, 'addPostCode'), 1);
}
?>