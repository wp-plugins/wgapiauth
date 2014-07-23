<?php
/*
Plugin Name: WGAPIAuth
Plugin URI: http://samber.ru/
Description: Плагин который позволяет пользователям авторизироваться при помощи Wargaming.net Public API. Cсылка для авторизации будет добавлена в форму добавления комментариев а так же на страницы авторизации и регистрации
Version: 0.1
Author: STREJlA
Author URI: http://forum.worldoftanks.ru/index.php?/user/strejla-423825/
*/

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
				'application_id' => 'demo'
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
				update_option($this->_WGAPIAuthOptionsName,$WGAPIAuthOptions);
				$save=1;
			}
			$form = file_get_contents('templates/settings.form.html',true);
			$form = str_replace('{URL}',$WGAPIAuthOptions['url'],$form);
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
			echo $form;
		}
	}
}
global $current_user;
add_action('admin_menu', 'WGAPIAuthSettingsPage');
add_action('comment_form', 'WGAPIAuth_comment_form');
add_action('login_form', 'WGAPIAuth_form_panel');
add_action('register_form','WGAPIAuth_form_panel');
add_action('parse_request', 'WGAPIAuth_parse_request');
add_action('login_form_login', 'WGAPIAuth_parse_request');
function WGAPIAuthSettingsPage(){
    $WGAPIAuthPluginSettings = new WGAPIAuthPluginSettings();
    $WGAPIAuthPluginSettings->init();
    if(!isset($WGAPIAuthPluginSettings)){
        return;
    }
    if(function_exists('add_options_page')){
        add_submenu_page('plugins.php','Настройки плагина WGAPIAuth','WGAPIAuth',9,basename(__FILE__),array($WGAPIAuthPluginSettings,'printAdminPage'));
    }
}	
function WGAPIAuth_div($redirectUrl=""){
    $WGAPIAuthPluginSettings = new WGAPIAuthPluginSettings();
    $WGAPIAuthPluginSettings->init();
    $WGAPIAuthOptions = $WGAPIAuthPluginSettings->getOptions();
	if($redirectUrl){$redirectUrl='&redirectUrl='.$redirectUrl;}
    return '<div><a id="WGAPIAuthLink" href="'.$WGAPIAuthOptions["url"].'?action=generateAuthUrl'.$redirectUrl.'">'.$WGAPIAuthOptions["label"].'</a></div>';
}
function WGAPIAuth_comment_form(){
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
	if($_REQUEST['action']=="showLoadPage"){
		include 'templates/load.html';
		exit();
	}
	if($_REQUEST['action']=="generateAuthUrl"){
		$redirect_uri=$WGAPIAuthOptions["url"];
		if($_REQUEST["nofollow"]){
			$redirect_uri.="?popup=1";
		}
		if($_REQUEST["redirectUrl"]){
			$redirect_uri.="?redirectUrl=".$_REQUEST["redirectUrl"]."&redirectHash=".substr(md5($_REQUEST["redirectUrl"].$WGAPIAuthOptions["application_id"]),0,10);
		}
		$url = "https://api.worldoftanks.ru/wot/auth/login/?application_id=".$WGAPIAuthOptions["application_id"]."&redirect_uri=".urlencode($redirect_uri)."&display=popup&nofollow=1";
		if(0 and extension_loaded('openssl')){
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
				exit("Произошла ошибка. Обновите попробуйте снова.");
			}
		}
	}
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