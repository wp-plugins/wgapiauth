<?php
/*
Plugin Name: WGAPIAuth
Plugin URI: https://blog.splatform.tk/
Description: Плагин, который позволяет пользователям входить на сайт в один клик, используя единый аккаунт Wargaming.net ID. Для проверки данных используются методы Wargaming Public API или OpenID. Cсылка для аутентификации будет добавлена в форму добавления комментариев а так же на страницу авторизации. Возможна работа во всех игровых регионах ([RU] Россия, [NA] America, [EU] Europe, [ASIA] Asia, [KR] 대한민국).
Version: 0.3.2
Author: STREJlA
Author URI: http://samber.ru/
*/
function WGAPIA_get_option($option_name=false){
	$cache_option=get_option('WGAPIA_options');
	if($cache_option){
		$cache_option=json_decode($cache_option,true);
	}else{
		$cache_option=array(
			'auth_type' => 'openid',//api или openid
			'ignore_users_can_register' => true,//api или openid
			'link_label' => 'Войти через единый аккаунт Wargaming.net ID',
			'link_comment_label' => 'Войти через единый аккаунт Wargaming.net ID',
			'application_id' => 'demo',
			'region' => 'ru',
			'show_game_profil' => true,
			'show_game_profil_list' => array(
				'wot' => true,
				'wowp' => true,
				'wows' => false
			)
		);
	}
	$cache_option['wg_openid']='http://'.$cache_option['region'].'.wargaming.net/id/';
	$cache_option['region_domain']=$cache_option['region'];
	if($cache_option['region']=="na")$cache_option['region_domain']="com";
	if($option_name)return $cache_option[$option_name];
	return $cache_option;
}

function WGAPIA_set_option($new_option=array()){
	if(count($new_option)){
		$cache_option=WGAPIA_get_option();
		foreach($new_option as $option=>$value){
			$cache_option[$option]=$value;
		}
		update_option('WGAPIA_options',json_encode($cache_option));
	}
	return true;
}

/*Изменить форму авторизации*/
function WGAPIA_login_page(){
	$link_label=WGAPIA_get_option('link_label');
	if($link_label){
		echo '<a href="'.site_url('/?action=generateAuthUrl&rt='.site_url()).'" class="button button-primary button-large" style="position:relative;margin-top:-30px;top:76px;left:117px;padding:0 100px;">'.$link_label.'</a>';
	}
}
add_action('login_form','WGAPIA_login_page');
/*Изменить форму добавления комментариев*/
function WGAPIA_comment_page(){
	if(!is_user_logged_in()){
		$link_comment_label=WGAPIA_get_option('link_comment_label');
		if($link_comment_label){
			echo '<a href="'.site_url('/?action=generateAuthUrl').'">'.$link_comment_label.'</a>';
		}
	}
}
add_action('comment_form_after','WGAPIA_comment_page');

/*Добавляем страницу настроек плагина в админке*/
function WGAPIA_admin_menu_print_settings_page(){
	$option=WGAPIA_get_option();
	if(isset($_POST['update_WGAPIAuthPluginSettings'])){
		$option['region'] = $_POST['region'];
		$option['link_label'] = ($_POST['link_label'])?$_POST['link_label']:false;
		$option['link_comment_label'] = ($_POST['link_comment_label'])?$_POST['link_comment_label']:false;
		$option['auth_type'] = $_POST['auth_type'];
		$option['application_id'] = $_POST['application_id'];
		$option['ignore_users_can_register'] = ($_POST['ignore_users_can_register']=='on')?true:false;
		$option['show_game_profil']=false;
		if($_POST['show_game_profil']=='on'){
			$option['show_game_profil']=true;
			$option['show_game_profil_list']['wot']=($_POST["show_game_profil_wot"]=='on')?true:false;
			$option['show_game_profil_list']['wowp']=($_POST["show_game_profil_wowp"]=='on')?true:false;
			$option['show_game_profil_list']['wows']=($_POST["show_game_profil_wows"]=='on')?true:false;
		}
		$option['ignore_users_can_register'] = ($_POST['ignore_users_can_register']=='on')?true:false;
		WGAPIA_set_option($option);
		$save=true;
		// echo "<hr><pre>";
		// var_dump($_POST);
		// var_dump($option);
		// var_dump(WGAPIA_get_option());
		// echo "</pre><hr>";
	}
	$page = file_get_contents('templates/settings_page.html',true);
	$page = str_replace('{AUTH_TYPE_OPENID_CHECKED}',checked("openid",$option["auth_type"],false),$page);
	$page = str_replace('{AUTH_TYPE_API_CHECKED}',checked("api",$option["auth_type"],false),$page);
	
	$page = str_replace('{SHOW_WELCOME}',(!WGAPIA_get_option('show_welcome'))?' style="display:none"':'',$page);
	
	$page = str_replace('{SHOW_GAME_PROFIL_CHECKED}',checked(true,$option["show_game_profil"],false),$page);
	$page = str_replace('{SHOW_GAME_PROFIL_LIST_SETTINGS}',(!$option["show_game_profil"])?' style="display:none"':'',$page);
	$page = str_replace('{SHOW_GAME_PROFIL_WOT_CHECKED}',checked(true,$option["show_game_profil_list"]['wot'],false),$page);
	$page = str_replace('{SHOW_GAME_PROFIL_WOWP_CHECKED}',checked(true,$option["show_game_profil_list"]['wowp'],false),$page);
	$page = str_replace('{SHOW_GAME_PROFIL_WOWS_CHECKED}',checked(true,$option["show_game_profil_list"]['wows'],false),$page);
	
	$page = str_replace('{IGNORE_USERS_CAN_REGISTER}',checked(true,$option["ignore_users_can_register"],false),$page);
	
	$page = str_replace('{APPID_SETTINGS}',($option["auth_type"]!="api")?' style="display:none"':'',$page);
	
	$page = str_replace('{REGION_RU}',selected("ru",$option["region"],false),$page);
	$page = str_replace('{REGION_NA}',selected("na",$option["region"],false),$page);
	$page = str_replace('{REGION_EU}',selected("eu",$option["region"],false),$page);
	$page = str_replace('{REGION_ASIA}',selected("asia",$option["region"],false),$page);
	$page = str_replace('{REGION_KR}',selected("kr",$option["region"],false),$page);
	
	$page = str_replace('{LINK_LABEL}',$option['link_label'],$page);
	$page = str_replace('{LINK_COMMENT_LABEL}',$option['link_comment_label'],$page);
	$page = str_replace('{AUTH_URL}',site_url('/?action=generateAuthUrl'),$page);
	
	$page = str_replace('{APPLICATION_ID}',$option['application_id'],$page);
	$page = str_replace('{ALERT}',($save)?'<div class="updated settings-error"><p><strong>Настройки плагина сохранены.</strong></p></div>':'',$page);
	echo $page;
}
function WGAPIA_admin_menu_add_settings_page(){
	add_submenu_page('plugins.php','Настройки','WG API Auth','edit_plugins','WGAPIA_settings','WGAPIA_admin_menu_print_settings_page');
}
if(is_admin()){
	add_action('admin_menu', 'WGAPIA_admin_menu_add_settings_page');
}

function WGAPIA_auth($account_id,$nickname,$redirectUrl=false){
	if(get_current_user_id())wp_logout();
	if(!WGAPIA_get_option('ignore_users_can_register') && !get_option('users_can_register')){
		wp_redirect( site_url('wp-login.php?registration=disabled') );
		exit();
	}
	$user=get_user_by('login', 'WG_'.$nickname);
	if(!$user){
		$new_user_id=wp_insert_user(array(
			'user_login' => 'WG_'.$nickname,
			'display_name' => $nickname,
			'nickname' => $nickname
		));
		update_user_meta($new_user_id,'region',WGAPIA_get_option('region'));
		update_user_meta($new_user_id,'wg_account_id',$account_id);
		
		$user=get_user_by('id',$new_user_id);
	}
	// wp_set_current_user($user->ID);
	wp_set_auth_cookie($user->ID, true, is_ssl());
	if($redirectUrl){
		wp_redirect(wp_validate_redirect($redirectUrl,site_url()));
		exit();
	}
	wp_redirect(site_url());
	exit();
}

/*Вывод доп. данных в профиле*/
function WGAPIA_show_meta($user){if(WGAPIA_get_option("show_game_profil")){?>
<h3>Профили в играх Wargaming.net</h3>
<table class="form-table">
	<?php $sgp=WGAPIA_get_option("show_game_profil_list");if($sgp['wot']){?><tr>
		 <th><img src="<?php echo site_url('/wp-content/plugins/wgapiauth/templates/img/wot.png');?>" alt="wot" style="float:right"></th>
		 <td><a href="http://worldoftanks.<?php echo (get_the_author_meta('region',$user->ID)=='na')?'com':get_the_author_meta('region',$user->ID);?>/community/accounts/<?php echo get_the_author_meta('wg_account_id',$user->ID);?>/" target="_blank">World of Tanks</a></td>
	</tr><?php }?>
	<?php if($sgp['wowp']){?><tr><?php ?>
		 <th><img src="<?php echo site_url('/wp-content/plugins/wgapiauth/templates/img/wowp.png');?>" alt="wot" style="float:right"></th>
		 <td><a href="http://worldofwarplanes.<?php echo (get_the_author_meta('region',$user->ID)=='na')?'com':get_the_author_meta('region',$user->ID);?>/community/players/<?php echo get_the_author_meta('wg_account_id',$user->ID);?>/" target="_blank">World of Warplanes</a></td>
	</tr><?php }?>
	<?php if($sgp['wows']){?><tr>
		 <th><img src="<?php echo site_url('/wp-content/plugins/wgapiauth/templates/img/wows.png');?>" alt="wot" style="float:right"></th>
		 <td><a href="http://worldofwarships.<?php echo (get_the_author_meta('region',$user->ID)=='na')?'com':get_the_author_meta('region',$user->ID);?>/cbt/accounts/<?php echo get_the_author_meta('wg_account_id',$user->ID);?>-/" target="_blank">World of Warships</a></td>
	</tr><?php }?>
</table>
<?php   
}}
add_action( 'show_user_profile', 'WGAPIA_show_meta' );
add_action( 'edit_user_profile', 'WGAPIA_show_meta' );

/*Вывод страницы перенаправления*/
function WGAPIA_print_init_page(){
	if($_REQUEST['action']=="showLoadPage"){
		include 'templates/load.php';
		exit();
	}
	if($_REQUEST['action']=="generateAuthUrl"){
		if(WGAPIA_get_option('auth_type')=="api"){
			$url = "https://api.worldoftanks.".WGAPIA_get_option('region_domain')."/wot/auth/login/?application_id=".WGAPIA_get_option('application_id')."&redirect_uri=".site_url('/?rt='.(isset( $_REQUEST['rt'] ) ? $_REQUEST['rt'] : $_SERVER['HTTP_REFERER']))."&nofollow=1";
			if(extension_loaded('openssl')){
				
				$data=json_decode(file_get_contents($url),true);
			}else{
				$curl = curl_init();
				curl_setopt($curl,CURLOPT_URL,$url);
				curl_setopt($curl,CURLOPT_HEADER,false);
				curl_setopt($curl,CURLOPT_AUTOREFERER,true);
				curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,false);
				curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
				curl_setopt($curl,CURLOPT_TIMEOUT,60);
				$data = json_decode(curl_exec($curl), true);
				curl_close($curl);
			}
			if($_REQUEST["nofollow"]){
				if($data["status"]=='ok'){
					exit(json_encode(array("status"=>"ok","authUrl"=>$data['data']['location'])));
				}else{
					exit(json_encode(array("status"=>"error")));
				}
			}else{
				if($data["status"]=='ok'){
					wp_redirect($data['data']['location']);
					exit();
				}else{
					exit("Произошла ошибка. Попробуйте повторить позже.");
				}
			}
		}
		if(WGAPIA_get_option('auth_type')=="openid"){
			require_once 'openid.php';
			$openid = new LightOpenID(site_url('/'));
			$openid->identity = 'http://'.WGAPIA_get_option('region').'.wargaming.net/id/';
			$openid->returnUrl = (site_url('/?rt='.(isset( $_REQUEST['rt'] ) ? $_REQUEST['rt'] : $_SERVER['HTTP_REFERER'])));
			if($_REQUEST["nofollow"]){
				exit(json_encode(array("status"=>"ok","authUrl"=>$openid->authUrl())));
			}else{
				wp_redirect($openid->authUrl());
				exit();
			}
		}
	}
	/*проверка api*/
	if($_GET['status']=="ok" && isset($_GET['access_token']) && isset($_GET['nickname']) && isset($_GET['account_id']) && isset($_GET['expires_at'])){
		$account_id=$_GET['account_id'];
		
		$url='http://api.worldoftanks.'.WGAPIA_get_option('region_domain').'/wot/account/info/?application_id='.WGAPIA_get_option('application_id').'&access_token='.$_GET['access_token'].'&account_id='.$account_id;
		
		if(extension_loaded('openssl')){
			$data=json_decode(file_get_contents($url),true);
		}else{
			$curl = curl_init();
			curl_setopt($curl,CURLOPT_URL,$url);
			curl_setopt($curl,CURLOPT_HEADER,false);
			curl_setopt($curl,CURLOPT_AUTOREFERER,true);
			curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,false);
			curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
			curl_setopt($curl,CURLOPT_TIMEOUT,60);
			$data = json_decode(curl_exec($curl), true);
			curl_close($curl);
		}
		
		if($data[status]=="ok" && $data["data"][$account_id]["private"]!=null){
			WGAPIA_auth($account_id,$data["data"][$account_id]["nickname"],$_GET['rt']);
		}else{
			exit("Данные аутентификации не подтверждены");
		}
	}
	/*Проверка openid*/
	if(isset($_GET['openid_mode'])){
		require_once 'openid.php';
		$openid = new LightOpenID(site_url('/'));
		if($openid->mode && $openid->mode!='cancel'){
			if($openid->validate()){
				preg_match('/id\/(\d+)-(\w{2,24})\/$/', $openid->identity, $matches);
				WGAPIA_auth($matches[1],$matches[2],$_GET['rt']);
			}else{
				exit("Данные аутентификации не подтверждены");
			}
		}
	}
}
add_action('parse_request', 'WGAPIA_print_init_page');



// exit(get_option('users_can_register'));


		
		
		



if(!class_exists("WGAPIAuth")){
	class WGAPIAuthPluginSettings{
		private $_WGAPIAuthOptionsName;
		private $_WGAPIAuthOptions;
		public function init(){
			$this->_WGAPIAuthOptionsName = 'WGAPIAuthPluginOptions';
			$this->_WGAPIAuthOptions = array(
				'type' => 'popup',
				'url' => 'http://'.$_SERVER[HTTP_HOST],
				'label' => 'Войти через единый аккаунт Wargaming.net ID',
				'application_id' => 'demo',
				'cyrillic' => '0'
			);
			$this->getOptions();
		}
		public function getOptions(){
			$WGAPIAuthOptions = get_option($this->_WGAPIAuthOptionsName);
			if(!empty($WGAPIAuthOptions)){
				foreach ($WGAPIAuthOptions as $key => $option){
					$this->_WGAPIAuthOptions[$key] = $option;
				}
			}
			update_option($this->_WGAPIAuthOptionsName,$this->_WGAPIAuthOptions);
			return $this->_WGAPIAuthOptions;
		}
		function printAdminPage(){
			$this->_WGAPIAuthOptionsName = 'WGAPIAuthPluginOptions';
			$WGAPIAuthOptions = $this->getOptions();
			if(isset($_POST['update_WGAPIAuthPluginSettings'])){
				if(isset($_POST['type'])){
					$WGAPIAuthOptions['type'] = $_POST['type'];
				}
				if(isset($_POST['application_id'])){
					$WGAPIAuthOptions['application_id'] = $_POST['application_id'];
				}
				if(isset($_POST['url'])){
					$WGAPIAuthOptions['url'] = $_POST['url'];
				}
				if(isset($_POST['label'])){
					$WGAPIAuthOptions['label'] = $_POST['label'];
				}
				if(isset($_POST['cyrillic']) && $_POST['cyrillic']){
					$WGAPIAuthOptions['cyrillic'] = '1';
				}else{
					$WGAPIAuthOptions['cyrillic'] = '0';
				}
				update_option($this->_WGAPIAuthOptionsName,$WGAPIAuthOptions);
				$save=1;
			}
			$form = file_get_contents('templates/settings.form.html',true);
			$form = str_replace('{URL}',$WGAPIAuthOptions['url'],$form);
			$form = str_replace('{USERS_CAN_REGISTER}',get_option('users_can_register'),$form);
			$form = str_replace('{HOME}',get_option('home'),$form);
			$form = str_replace('{LABEL}',$WGAPIAuthOptions['label'],$form);
			$form = str_replace('{APPLICATION_ID}',$WGAPIAuthOptions['application_id'],$form);
			if($save){
				$form = str_replace('{ALERT}','<div class="updated settings-error"><p><strong>Настройки плагина сохранены.</strong></p></div>',$form);
			}else{
				$form = str_replace('{ALERT}','',$form);
			}
			$form = str_replace('{'.strtoupper($WGAPIAuthOptions['type']).'_SELECTED}','selected="selected"', $form);
			$form = str_replace('{POPUP_SELECTED}','',$form);
			$form = str_replace('{REDIRECT_SELECTED}','',$form);
			if($WGAPIAuthOptions['cyrillic']){
				$form = str_replace('{CYRILLIC}','checked',$form);
			}else{
				$form = str_replace('{CYRILLIC}','',$form);
			}
			echo $form;
		}
	}
}
global $current_user;
// add_action('admin_menu', 'WGAPIAuthSettingsPage');
// add_action('comment_form', 'WGAPIAuth_comment_form');
// add_action('login_form', 'WGAPIAuth_form_panel');
// add_action('register_form','WGAPIAuth_form_panel');
// add_action('parse_request', 'WGAPIAuth_parse_request');
// add_action('login_form_login', 'WGAPIAuth_parse_request');
// add_action('register_post', 'WGAPIAuth_parse_request');
function WGAPIAuthSettingsPage(){
    $WGAPIAuthPluginSettings = new WGAPIAuthPluginSettings();
    $WGAPIAuthPluginSettings->init();
    if(!isset($WGAPIAuthPluginSettings)){
        return;
    }
    if(function_exists('add_options_page')){
        add_submenu_page('plugins.php','Настройки','WGAPIAuth',9,basename(__FILE__),array($WGAPIAuthPluginSettings,'printAdminPage'));
    }
}	
function WGAPIAuth_div($redirectUrl=""){
    $WGAPIAuthPluginSettings = new WGAPIAuthPluginSettings();
    $WGAPIAuthPluginSettings->init();
    $WGAPIAuthOptions = $WGAPIAuthPluginSettings->getOptions();
	if($redirectUrl){$redirectUrl='&redirectUrl='.$redirectUrl;};
    return '<div><a id="WGAPIAuthLink" href="'.$WGAPIAuthOptions["url"].'?action=generateAuthUrl'.$redirectUrl.'">'.$WGAPIAuthOptions["label"].'</a></div>';
}
function WGAPIAuth_comment_form(){
	exit('sssss');
	global $current_user;
    $WGAPIAuthPluginSettings = new WGAPIAuthPluginSettings();
    $WGAPIAuthPluginSettings->init();
    $WGAPIAuthOptions = $WGAPIAuthPluginSettings->getOptions();
	$redirectUrl = 'http';
	if(isset($_SERVER["HTTPS"])){
		if($_SERVER["HTTPS"]=="on"){$redirectUrl .= "s";}
	}
	$redirectUrl .= "://";
	$redirectUrl .= $_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
	$redirectUrl=urlencode($redirectUrl);
	if ($current_user->ID == 0) {
		if($WGAPIAuthOptions["type"]=="popup"){
			$popup='<script type="text/javascript">'.
				'function getXmlHttp(){try{return new ActiveXObject("Msxml2.XMLHTTP");}catch(e){try{return new ActiveXObject("Microsoft.XMLHTTP");}catch(ee){}}if(typeof XMLHttpRequest!="undefined"){return new XMLHttpRequest();}};'.
				'function getUrl(url,cd){var xmlhttp=getXmlHttp();xmlhttp.open("GET",url+"?r="+Math.random());xmlhttp.onreadystatechange=function(){if(xmlhttp.readyState==4){cd(xmlhttp.status,xmlhttp.getAllResponseHeaders(),xmlhttp.responseText);}};xmlhttp.send(null);};'.
				'document.getElementById(\'WGAPIAuthLink\').onclick = function(){'.
					'var authWindow=window.open("'.$WGAPIAuthOptions["url"].'?action=showLoadPage","authWindow","width=450,height=600,left="+(screen.width-450)/2+",top="+(screen.height-600)/2+",menubar=no,toolbar=no,location=yes,resizable=no");'.
					'function ajaxok(status,headers,text){'.
						'var data=JSON.parse(text);'.
						'if(authWindow==null){window.location=data.authUrl;}else{authWindow.location=data.authUrl;authWindow.focus();}'.
					'};'.
					'getUrl("'.$WGAPIAuthOptions["url"].'?action=generateAuthUrl&nofollow=1",ajaxok);'.
					'return(false);'.
				'}'.
			'</script>';
		}
		echo '<script type="text/javascript">'.
		'(function(){'.
			'var form = document.getElementById(\'commentform\');'.
			'if (form){'.
				'var div = document.createElement(\'div\');'.
				'div.innerHTML = \''.WGAPIAuth_div($redirectUrl).'\';'.
				'form.parentNode.insertBefore(div, form);'.
			'}'.
		'})();'.
		'</script>'.$popup;
	}
}
function WGAPIAuth_form_panel(){
	global $current_user;
	if(!$current_user->ID){
		$panel = WGAPIAuth_div();   
	}
	echo $panel;
}
function WGAPIAuth_parse_request(){
	$WGAPIAuthPluginSettings = new WGAPIAuthPluginSettings();
	$WGAPIAuthPluginSettings->init();
	$WGAPIAuthOptions = $WGAPIAuthPluginSettings->getOptions();

	if($_GET['status']=="ok" && isset($_GET['access_token']) && isset($_GET['nickname']) && isset($_GET['account_id']) && isset($_GET['expires_at'])){
		$access_token=$_GET['access_token'];
		$account_id=$_GET['account_id'];
		$data=json_decode(@file_get_contents('http://api.worldoftanks.ru/wot/account/info/?application_id='.$WGAPIAuthOptions["application_id"].'&access_token='.$access_token.'&account_id='.$account_id),true);
		if($data[status]=="ok" and $data["data"][$account_id]["private"]!=null){
			$user_id = get_user_by('login', 'WG_'.$data["data"][$account_id]["nickname"]);
			if(isset($user_id->ID)){
				$user_id = $user_id->ID;
			}else{
				$user_id = wp_insert_user(array('user_pass' => wp_generate_password(),
				'user_login' => 'WG_'.$data["data"][$account_id]["nickname"],
				'display_name' => $data['data'][$account_id]["nickname"],
                'nickname' => $data['data'][$account_id]["nickname"],
				'user_url' => 'http://worldoftanks.ru/community/accounts/'.$account_id));
			}
			wp_set_current_user($user_id);
			wp_set_auth_cookie($user_id);
			if($_GET["popup"]){exit('<script type="text/javascript">if(window.opener==null){window.location="'.$WGAPIAuthOptions["url"].'";}else{window.opener.location.reload();window.close();}</script>');}
			if($_GET["redirectUrl"] and $_GET["redirectHash"]==substr(md5($_GET["redirectUrl"].$WGAPIAuthOptions["application_id"]),0,10)){
				$parse_url=parse_url($_GET["redirectUrl"]);
				$redirectUrl=$_GET["redirectUrl"];
			}else{
				$redirectUrl=$WGAPIAuthOptions["url"];
			}
			wp_redirect($redirectUrl);
		}
	}
}