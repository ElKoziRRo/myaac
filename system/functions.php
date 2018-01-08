<?php
/**
 * Useful functions
 *
 * @package   MyAAC
 * @author    Slawkens <slawkens@gmail.com>
 * @copyright 2017 MyAAC
 * @link      http://my-aac.org
 */
defined('MYAAC') or die('Direct access not allowed!');

function message($message, $type, $return)
{
	if($return)
		return '<p class="' . $type . '">' . $message . '</p>';
	
	echo '<p class="' . $type . '">' . $message . '</p>';
	return true;
}
function success($message, $return = false) {
	return message($message, 'success', $return);
}
function warning($message, $return = false) {
	return message($message, 'warning', $return);
}
function note($message, $return = false) {
	return message($message, 'note', $return);
}
function error($message, $return = false) {
	return message($message, 'error', $return);
}

function longToIp($ip)
{
	$exp = explode(".", long2ip($ip));
	return $exp[3].".".$exp[2].".".$exp[1].".".$exp[0];
}

function generateLink($url, $name, $blank = false) {
	return '<a href="' . $url . '"' . ($blank ? ' target="_blank"' : '') . '>' . $name . '</a>';
}

function getFullLink($page, $name, $blank = false) {
	return generateLink(getLink($page), $name, $blank);
}

function getLink($page, $action = null)
{
	global $config;
	return BASE_URL . ($config['friendly_urls'] ? '' : '?') . $page . ($action ? '/' . $action : '');
}
function internalLayoutLink($page, $action = null) {return getLink($page, $action);}

function getForumThreadLink($thread_id, $page = NULL)
{
	global $config;
	return BASE_URL . ($config['friendly_urls'] ? '' : '?') . 'forum/thread/' . (int)$thread_id . (isset($page) ? '/' . $page : '');
}

function getForumBoardLink($board_id, $page = NULL)
{
	global $config;
	return BASE_URL . ($config['friendly_urls'] ? '' : '?') . 'forum/board/' . (int)$board_id . (isset($page) ? '/' . $page : '');
}

function getPlayerLink($name, $generate = true)
{
	global $ots, $config;

	if(is_numeric($name))
	{
		$player = new OTS_Player();
		$player->load(intval($name));
		if($player->isLoaded())
			$name = $player->getName();
	}
	
	$url = BASE_URL . ($config['friendly_urls'] ? '' : '?') . 'characters/' . urlencode($name);

	if(!$generate) return $url;
	return generateLink($url, $name);
}

function getHouseLink($name, $generate = true)
{
	global $db, $config;

	if(is_numeric($name))
	{
		$house = $db->query(
			'SELECT ' . $db->fieldName('name') .
			' FROM ' . $db->tableName('houses') .
			' WHERE ' . $db->fieldName('id') . ' = ' . (int)$name);
		if($house->rowCount() > 0)
			$name = $house->fetchColumn();
	}
	
	$url = BASE_URL . ($config['friendly_urls'] ? '' : '?') . 'houses/' . urlencode($name);
	
	if(!$generate) return $url;
	return generateLink($url, $name);
}

function getGuildLink($name, $generate = true)
{
	global $db, $config;

	if(is_numeric($name))
	{
		$guild = $db->query(
			'SELECT `name` FROM `guilds` WHERE `id` = ' . (int)$name);
		if($guild->rowCount() > 0)
			$name = $guild->fetchColumn();
	}

	$url = BASE_URL . ($config['friendly_urls'] ? '' : '?') . 'guilds/' . urlencode($name);

	if(!$generate) return $url;
	return generateLink($url, $name);
}

function getItemNameById($id) {
	global $db;
	$query = $db->query('SELECT `name` FROM `' . TABLE_PREFIX . 'items` WHERE `id` = ' . $db->quote($id) . ' LIMIT 1;');
	if($query->rowCount() == 1) {
		$item = $query->fetch();
		return $item['name'];
	}
	
	return '';
}

function getItemImage($id, $count = 1)
{
	$tooltip = '';
	
	$name = getItemNameById($id);
	if(!empty($name)) {
		$tooltip = ' class="tooltip" title="' . $name . '"';
	}

	$file_name = $id;
	if($count > 1)
		$file_name .= '-' . $count;

	global $config;
	return '<img src="' . $config['item_images_url'] . $file_name . '.gif"' . $tooltip . ' width="32" height="32" border="0" alt=" ' .$id . '" />';
}

function getFlagImage($country)
{
	if(!isset($country[0]))
		return '';

	global $config;
	if(!isset($config['countries']))
		require(SYSTEM . 'countries.conf.php');

	return '<img src="images/flags/' . $country . '.gif" title="' . $config['countries'][$country]. '"/>';
}

/**
 * Performs a boolean check on the value.
 *
 * @param mixed $v Variable to check.
 * @return bool Value boolean status.
 */
function getBoolean($v)
{
	if(is_bool($v)) {
		return $v;
	}

	if(is_numeric($v))
		return intval($v) > 0;

	$v = strtolower($v);
	return $v == 'yes' || $v == 'true';
}

/**
 * Generates random string.
 *
 * @param int $length Length of the generated string.
 * @param bool $numeric Should numbers by used too?
 * @param bool $special Should special characters by used?
 * @return string Generated string.
 */
function generateRandomString($length, $lowCase = true, $upCase = false, $numeric = false, $special = false)
{
	$characters = '';
	if($lowCase)
		$characters .= 'abcdefghijklmnopqrstuxyvwz';

	if($upCase)
		$characters .= 'ABCDEFGHIJKLMNPQRSTUXYVWZ';

	if($numeric)
		$characters .= '123456789';

	if($special)
		$characters .= '+-*#&@!?';

	$characters_length = strlen($characters) - 1;
	if($characters_length <= 0) return '';

	$ret = '';
	for($i = 0; $i < $length; $i++)
		$ret .= $characters[mt_rand(0, $characters_length)];

    return $ret;
}

/**
 * Get forum sections
 *
 * @return array Forum sections.
 */
function getForumBoards()
{
	global $db, $canEdit;
	$sections = $db->query('SELECT `id`, `name`, `description`, `closed`, `guild`, `access`' . ($canEdit ? ', `hidden`, `ordering`' : '') . ' FROM `' . TABLE_PREFIX . 'forum_boards` ' . (!$canEdit ? ' WHERE `hidden` != 1' : '') .
		' ORDER BY `ordering`;');
	if($sections)
		return $sections->fetchAll();
	
	return array();
}

/**
 * Retrieves data from myaac database config.
 *
 * @param string $name Key.
 * @param string &$value Reference where requested data will be set to.
 * @return bool False if value was not found in table, otherwise true.
 */
function fetchDatabaseConfig($name, &$value)
{
	global $db;

	$query = $db->query('SELECT ' . $db->fieldName('value') . ' FROM ' . $db->tableName(TABLE_PREFIX . 'config') . ' WHERE ' . $db->fieldName('name') . ' = ' . $db->quote($name));
	if($query->rowCount() <= 0)
		return false;

	$value = $query->fetchColumn();
	return true;
}

/**
 * Retrieves data from database config.
 *
 * $param string $name Key.
 * @return string Requested data.
 */
function getDatabaseConfig($name)
{
	$value = null;
	fetchDatabaseConfig($name, $value);
	return $value;
}

/**
 * Register a new key pair in myaac database config.
 *
 * @param string $name Key name.
 * @param string $value Data to be associated with key.
 */
function registerDatabaseConfig($name, $value)
{
	global $db;
	$db->insert(TABLE_PREFIX . 'config', array('name' => $name, 'value' => $value));
}

/**
 * Updates a value in myaac database config.
 *
 * @param string $name Key name.
 * @param string $value New data.
 */
function updateDatabaseConfig($name, $value)
{
	global $db;
	$db->update(TABLE_PREFIX . 'config', array('value' => $value), array('name' => $name));
}

/**
 * Encrypt text using method specified in config.lua (encryptionType or passwordType)
 */
function encrypt($str)
{
	global $config;
	if(isset($config['database_salt'])) // otserv
		$str .= $config['database_salt'];

	$encryptionType = $config['database_encryption'];
	if(isset($encryptionType) && strtolower($encryptionType) != 'plain')
	{
		if($encryptionType == 'vahash')
			return base64_encode(hash('sha256', $str));

		return hash($encryptionType, $str);
	}

	return $str;
}

//delete player with name
function delete_player($name)
{
	global $db;
	$player = new OTS_Player();
	$player->find($name);
	if($player->isLoaded()) {
		try { $db->query("DELETE FROM player_skills WHERE player_id = '".$player->getId()."';"); } catch(PDOException $error) {}
		try { $db->query("DELETE FROM guild_invites WHERE player_id = '".$player->getId()."';"); } catch(PDOException $error) {}
		try { $db->query("DELETE FROM player_items WHERE player_id = '".$player->getId()."';"); } catch(PDOException $error) {}
		try { $db->query("DELETE FROM player_depotitems WHERE player_id = '".$player->getId()."';"); } catch(PDOException $error) {}
		try { $db->query("DELETE FROM player_spells WHERE player_id = '".$player->getId()."';"); } catch(PDOException $error) {}
		try { $db->query("DELETE FROM player_storage WHERE player_id = '".$player->getId()."';"); } catch(PDOException $error) {}
		try { $db->query("DELETE FROM player_viplist WHERE player_id = '".$player->getId()."';"); } catch(PDOException $error) {}
		try { $db->query("DELETE FROM player_deaths WHERE player_id = '".$player->getId()."';"); } catch(PDOException $error) {}
		try { $db->query("DELETE FROM player_deaths WHERE killed_by = '".$player->getId()."';"); } catch(PDOException $error) {}
		$rank = $player->getRank();
		if($rank->isLoaded()) {
			$guild = $rank->getGuild();
			if($guild->getOwner()->getId() == $player->getId()) {
				$rank_list = $guild->getGuildRanksList();
				if(count($rank_list) > 0) {
					$rank_list->orderBy('level');
					foreach($rank_list as $rank_in_guild) {
						$players_with_rank = $rank_in_guild->getPlayersList();
						$players_with_rank->orderBy('name');
						$players_with_rank_number = count($players_with_rank);
						if($players_with_rank_number > 0) {
							foreach($players_with_rank as $player_in_guild) {
								$player_in_guild->setRank();
								$player_in_guild->save();
							}
						}
						$rank_in_guild->delete();
					}
					$guild->delete();
				}
			}
		}
		$player->delete();
		return true;
	}
}

//delete guild with id
function delete_guild($id)
{
	$guild = new OTS_Guild();
	$guild->load($id);
	if(!$guild->isLoaded())
		return false;

	$rank_list = $guild->getGuildRanksList();
	if(count($rank_list) > 0) {
		$rank_list->orderBy('level');
		
		global $db, $ots;
		foreach($rank_list as $rank_in_guild) {
			if($db->hasTable('guild_members'))
				$players_with_rank = $db->query('SELECT `players`.`id` as `id`, `guild_members`.`rank_id` as `rank_id` FROM `players`, `guild_members` WHERE `guild_members`.`rank_id` = ' . $rank_in_guild->getId() . ' AND `players`.`id` = `guild_members`.`player_id` ORDER BY `name`;');
			else if($db->hasTable('guild_membership'))
				$players_with_rank = $db->query('SELECT `players`.`id` as `id`, `guild_membership`.`rank_id` as `rank_id` FROM `players`, `guild_membership` WHERE `guild_membership`.`rank_id` = ' . $rank_in_guild->getId() . ' AND `players`.`id` = `guild_membership`.`player_id` ORDER BY `name`;');
			else
				$players_with_rank = $db->query('SELECT `id`, `rank_id` FROM `players` WHERE `rank_id` = ' . $rank_in_guild->getId() . ' AND `deleted` = 0;');

			$players_with_rank_number = $players_with_rank->rowCount();
			if($players_with_rank_number > 0) {
				foreach($players_with_rank as $result) {
					$player = new OTS_Player();
					$player->load($result['id']);
					if(!$player->isLoaded())
						continue;
						
					$player->setRank();
					$player->save();
				}
			}
			$rank_in_guild->delete();
		}
	}

	$guild->delete();
	return true;
}

//################### DISPLAY FUNCTIONS #####################
//return shorter text (news ticker)
function short_text($text, $limit)
{
	if(strlen($text) > $limit)
		return substr($text, 0, strrpos(substr($text, 0, $limit), " ")).'...';

	return $text;
}

function tickers()
{
	global $tickers_content, $featured_article;
	
	if(PAGE == 'news') {
		if(isset($tickers_content))
			return $tickers_content . $featured_article;
	}

	return '';
}

/**
 * Template place holder
 *
 * Types: head_start, head_end, body_start, body_end, center_top
 *
 */
function template_place_holder($type)
{
	global $template_place_holders;
	$ret = '';

	if(array_key_exists($type, $template_place_holders) && is_array($template_place_holders[$type]))
		$ret = implode($template_place_holders[$type]);

	if($type == 'head_start')
		$ret .= template_header();
	elseif($type == 'body_end')
		$ret .= template_ga_code();

	return $ret;
}

/**
 * Returns <head> content to be used by templates.
 */
function template_header($is_admin = false)
{
	global $title_full, $config;
	$charset = isset($config['charset']) ? $config['charset'] : 'utf-8';

	$ret = '
	<meta charset="' . $charset . '">
	<meta http-equiv="content-language" content="' . $config['language'] . '" />
	<meta http-equiv="content-type" content="text/html; charset=' . $charset . '" />';
	if(!$is_admin)
		$ret .= '
	<base href="' . BASE_URL . '" />
	<title>' . $title_full . '</title>';

	$ret .= '
	<meta name="description" content="' . $config['meta_description'] . '" />
	<meta name="keywords" content="' . $config['meta_keywords'] . ', myaac, wodzaac" />
	<meta name="generator" content="MyAAC ' . MYAAC_VERSION . '" />
	<link rel="stylesheet" type="text/css" href="' . BASE_URL . 'tools/messages.css" />
	<script type="text/javascript" src="' . BASE_URL . 'tools/jquery.js"></script>
	<noscript>
		<div class="warning" style="text-align: center; font-size: 14px;">Your browser does not support JavaScript or its disabled!<br/>
			Please turn it on, or be aware that some features on this website will not work correctly.</div>
	</noscript>
';
	
	if($config['recaptcha_enabled'])
		$ret .= "<script src='https://www.google.com/recaptcha/api.js'></script>";
	return $ret;
}

/**
 * Returns footer content to be used by templates.
 */
function template_footer()
{
	global $config, $views_counter;
	$ret = '';
	if(admin())
		$ret .= generateLink(ADMIN_URL, 'Admin Panel', true);

	if($config['visitors_counter'])
	{
		global $visitors;
		$amount = $visitors->getAmountVisitors();
		$ret .= '<br/>Currently there ' . ($amount > 1 ? 'are' : 'is') . ' ' . $amount . ' visitor' . ($amount > 1 ? 's' : '') . '.';
	}

	if($config['views_counter'])
		$ret .= '<br/>Page has been viewed ' . $views_counter . ' times.';

	if(admin())
		$ret .= '<br/>Load time: ' . round(microtime(true) - START_TIME, 4) . ' seconds.';

	if(isset($config['footer'][0]))
		$ret .= '<br/>' . $config['footer'];

	// please respect my work and help spreading the word, thanks!
	return $ret . '<br/>' . base64_decode('UG93ZXJlZCBieSA8YSBocmVmPSJodHRwOi8vbXktYWFjLm9yZyIgdGFyZ2V0PSJfYmxhbmsiPk15QUFDLjwvYT4=');
}

function template_ga_code()
{
	global $config, $twig;
	if(!isset($config['google_analytics_id'][0]))
		return '';

	return $twig->render('google_analytics.html.twig');
}

function template_form()
{
	global $cache, $template_name;
	if($cache->enabled())
	{
		$tmp = '';
		if($cache->fetch('templates', $tmp)) {
			$templates = unserialize($tmp);
		}
		else
		{
			$templates = get_templates();
			$cache->set('templates', serialize($templates), 30);
		}
	}
	else
		$templates = get_templates();

	$options = '';
	foreach($templates as $key => $value)
		$options .= '<option ' . ($template_name == $value ? 'SELECTED' : '') . '>' . $value . '</option>';

	return 	'<form method="get" action="' . BASE_URL . '">
				<hidden name="subtopic" value="' . PAGE . '"/>
				<select name="template" onchange="this.form.submit()">' . $options . '</select>
			</form>';
}

function getStyle($i)
{
	global $config;
	return is_int($i / 2) ? $config['darkborder'] : $config['lightborder'];
}

$vowels = array("e", "y", "u", "i", "o", "a");
function getCreatureName($killer, $showStatus = false, $extendedInfo = false)
{
	global $vowels, $ots, $config;
	$str = "";
	$players_rows = '';
	
	if(is_numeric($killer))
	{
		$player = new OTS_Player();
		$player->load($killer);
		if($player->isLoaded())
		{
			$str .= '<a href="' . getPlayerLink($player->getName(), false) . '">';
			if(!$showStatus)
				return $str.'<b>'.$player->getName().'</b></a>';

			$str .= '<font color="'.($player->isOnline() ? 'green' : 'red').'">' . $player->getName() . '</font></b></a>';
			if($extendedInfo) {
				$str .= '<br><small>'.$player->getLevel().' '.$player->getVocationName().'</small>';
			}
			return $str;
		}
	}
	else
	{
		if($killer == "-1")
			$players_rows .= "item or field";
		else
		{
			if(in_array(substr(strtolower($killer), 0, 1), $vowels))
				$players_rows .= "an ";
			else
				$players_rows .= "a ";
			$players_rows .= $killer;
		}
	}

	return $players_rows;
}

/**
 * Find skill name using skill id.
 *
 * @param int $skillId Skill id.
 * @param bool $suffix Should suffix also be added?
 * @return string Skill name or 'unknown' if not found.
 */
function getSkillName($skillId, $suffix = true)
{
	switch($skillId)
	{
		case POT::SKILL_FIST:
		{
			$tmp = 'fist';
			if($suffix)
				$tmp .= ' fighting';

			return $tmp;
		}
		case POT::SKILL_CLUB:
		{
			$tmp = 'club';
			if($suffix)
				$tmp .= ' fighting';

			return $tmp;
		}
		case POT::SKILL_SWORD:
		{
			$tmp = 'sword';
			if($suffix)
				$tmp .= ' fighting';

			return $tmp;
		}
		case POT::SKILL_AXE:
		{
			$tmp = 'axe';
			if($suffix)
				$tmp .= ' fighting';

			return $tmp;
		}
		case POT::SKILL_DIST:
		{
			$tmp = 'distance';
			if($suffix)
				$tmp .= ' fighting';

			return $tmp;
		}
		case POT::SKILL_SHIELD:
			return 'shielding';
		case POT::SKILL_FISH:
			return 'fishing';
		case POT::SKILL__MAGLEVEL:
			return 'magic level';
		case POT::SKILL__LEVEL:
			return 'level';
		default:
			break;
	}

	return 'unknown';
}

/**
 * Performs flag check on the current logged in user.
 * Table in database: accounts, field: website_flags
 *
 * @param int @flag Flag to be verified.
 * @return bool If user got flag.
 */
function hasFlag($flag) {
	global $logged, $logged_flags;
	return ($logged && ($logged_flags & $flag) == $flag);
}
/**
 * Check if current logged user have got admin flag set.
 */
function admin() {
	return hasFlag(FLAG_ADMIN) || superAdmin();
}
/**
 * Check if current logged user have got super admin flag set.
 */
function superAdmin() {
	return hasFlag(FLAG_SUPER_ADMIN);
}

/**
 * Format experience according to its amount (natural/negative number).
 *
 * @param int $exp Experience amount.
 * @param bool $color Should result be colorized?
 * @return string Resulted message attached in <font> tag.
 */
function formatExperience($exp, $color = true)
{
	$ret = '';
	if($color)
	{
		$ret .= '<font';
		if($exp > 0)
			$ret .= ' color="green">';
		elseif($exp < 0)
			$ret .= ' color="red">';
		else
			$ret .= '>';
	}

	$ret .= '<b>' . ($exp > 0 ? '+' : '') . number_format($exp) . '</b>';
	if($color)
		$ret .= '</font>';

	return $ret;
}

function get_locales()
{
	$ret = array();

	$path = LOCALE;
	foreach(scandir($path) as $file)
	{
		if($file[0] != '.' && $file != '..' && is_dir($path . $file))
			$ret[] = $file;
	}

	return $ret;
}

function get_browser_languages()
{
	$ret = array();

	$acceptLang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
	if(!isset($acceptLang[0]))
		return $ret;

	$languages = strtolower($acceptLang);
	// $languages = 'pl,en-us;q=0.7,en;q=0.3 ';
	// need to remove spaces from strings to avoid error
	$languages = str_replace(' ', '', $languages);

	foreach(explode(',', $languages) as $language_list)
		$ret[] .= substr($language_list, 0, 2);

	return $ret;
}

/**
 * Generates list of templates, according to templates/ dir.
 */
function get_templates()
{
	$ret = array();

	$path = TEMPLATES;
	foreach(scandir($path) as $file)
	{
		if($file[0] != '.' && $file != '..' && is_dir($path . $file))
			$ret[] = $file;
	}

	return $ret;
}

/**
 * Generates list of installed plugins
 * @return array $plugins
 */
function get_plugins()
{
	$ret = array();
	
	$path = PLUGINS;
	foreach(scandir($path) as $file) {
		$file_ext = pathinfo($file, PATHINFO_EXTENSION);
		$file_name = pathinfo($file, PATHINFO_FILENAME);
		if ($file == '.' || $file == '..' || $file == 'disabled' || $file == 'example.json' || is_dir($path . $file) || $file_ext != 'json')
			continue;
		
		$ret[] = str_replace('.json', '', $file_name);
	}
	
	return $ret;
}
function getWorldName($id)
{
	global $config;
	if(isset($config['worlds'][$id]))
		return $config['worlds'][$id];
	
	return $config['lua']['serverName'];
}

/**
 * Mailing users.
 * $config['mail_enabled'] have to be enabled.
 *
 * @param string $to Recipient email address.
 * @param string $subject Subject of the message.
 * @param string $body Message body in html format.
 * @param string $altBody Alternative message body, plain text.
 * @return bool PHPMailer status returned (success/failure).
 */
function _mail($to, $subject, $body, $altBody = '', $add_html_tags = true)
{
	global $mailer, $config;
	if(!$mailer)
	{
		require(SYSTEM . 'libs/phpmailer/PHPMailerAutoload.php');
		$mailer = new PHPMailer();
	}

	$signature_html = '';
	if(isset($config['mail_signature']['html']))
		$signature_html = $config['mail_signature']['html'];

	if($add_html_tags && isset($body[0]))
		$tmp_body = '<html><head></head><body>' . $body . '<br/><br/>' . $signature_html . '</body></html>';
	else
		$tmp_body .= '<br/><br/>' . $signature_html;

	if($config['smtp_enabled'])
	{
		$mailer->IsSMTP();
		$mailer->Host = $config['smtp_host'];
		$mailer->Port = (int)$config['smtp_port'];
		$mailer->SMTPAuth = $config['smtp_auth'];
		$mailer->Username = $config['smtp_user'];
		$mailer->Password = $config['smtp_pass'];
	}
	else
		$mailer->IsMail();

	$mailer->IsHTML(isset($body[0]) > 0);
	$mailer->From = $config['mail_address'];
	$mailer->Sender = $config['mail_address'];
	$mailer->CharSet = 'utf-8';
	$mailer->FromName = $config['lua']['serverName'];
	$mailer->Subject = $subject;
	$mailer->AddAddress($to);
	$mailer->Body = $tmp_body;

	$signature_plain = '';
	if(isset($config['mail_signature']['plain']))
		$signature_plain = $config['mail_signature']['plain'];

	if(isset($altBody[0]))
		$mailer->AltBody = $altBody . $signature_plain;
	else { // automatically generate plain html
		$mailer->AltBody = strip_tags(preg_replace('/<a(.*)href="([^"]*)"(.*)>/','$2', $body)) . "\n" . $signature_plain;
	}

	return $mailer->Send();
}

function convert_bytes($size)
{
	$unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');
	return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
}

function log_append($file, $str)
{
	$f = fopen(LOGS . $file, 'a');
	fwrite($f, '[' . date(DateTime::RFC1123) . '] ' . $str . PHP_EOL);
	fclose($f);
}

function load_config_lua($filename)
{
	global $config;
	
	$config_file = $filename;
	if(!@file_exists($config_file))
	{
		log_append('error.log', '[load_config_file] Fatal error: Cannot load config.lua (' . $filename . '). Error: ' . print_r(error_get_last(), true));
		die('ERROR: Cannot find ' . $filename . ' file. More info in system/logs/error.log');
	}

	$result = array();
	$config_string = file_get_contents($filename);
	$config_string = str_replace("\r\n", "\n", $config_string);
	$config_string = str_replace("\r", "\n", $config_string);
	$lines = explode("\n", $config_string);
	if(count($lines) > 0)
		foreach($lines as $ln => $line)
		{
			$tmp_exp = explode('=', $line, 2);
			if(strpos($line, 'dofile') !== false)
			{
				$delimiter = '"';
				if(strpos($line, $delimiter) === false)
					$delimiter = "'";
				
				$tmp = explode($delimiter, $line);
				$result = array_merge($result, load_config_lua($config['server_path'] . $tmp[1]));
			}
			else if(count($tmp_exp) >= 2)
			{
				$key = trim($tmp_exp[0]);
				if(substr($key, 0, 2) != '--')
				{
					$value = trim($tmp_exp[1]);
					if(strpos($value, '--') !== false) {// found some deep comment
						$value = preg_replace('/--.*$/i', '', $value);
					}
					
					if(is_numeric($value))
						$result[$key] = (float) $value;
					elseif(in_array(substr($value, 0 , 1), array("'", '"')) && in_array(substr($value, -1 , 1), array("'", '"')))
						$result[$key] = (string) substr(substr($value, 1), 0, -1);
					elseif(in_array($value, array('true', 'false')))
						$result[$key] = ($value == 'true') ? true : false;
					else
					{
						foreach($result as $tmp_key => $tmp_value) // load values definied by other keys, like: dailyFragsToBlackSkull = dailyFragsToRedSkull
							$value = str_replace($tmp_key, $tmp_value, $value);
						$ret = @eval("return $value;");
						if((string) $ret == '') // = parser error
						{
							die('ERROR: Loading config.lua file. Line <b>' . ($ln + 1) . '</b> of LUA config file is not valid [key: <b>' . $key . '</b>]');
						}
						$result[$key] = $ret;
					}
				}
			}
		}
	

	$result = array_merge($result, isset($config['lua']) ? $config['lua'] : array());
	return $result;
}

function str_replace_first($search, $replace, $subject) {
	$pos = strpos($subject, $search);
	if ($pos !== false) {
		return substr_replace($subject, $replace, $pos, strlen($search));
	}
	
	return $subject;
}

function setSession($key, $data) {
	global $config;
	$_SESSION[$config['session_prefix'] . $key] = $data;
}
function getSession($key) {
	global $config;
	return (isset($_SESSION[$config['session_prefix'] . $key])) ? $_SESSION[$config['session_prefix'] . $key] : false;
}
function unsetSession($key) {
	global $config;
	unset($_SESSION[$config['session_prefix'] . $key]);
}

function getTopPlayers($limit = 5) {
	global $cache, $config, $db;
	
	$fetch_from_db = true;
	if($cache->enabled())
	{
		$tmp = '';
		if($cache->fetch('top_' . $limit . '_level', $tmp))
		{
			$players = unserialize($tmp);
			$fetch_from_db = false;
		}
	}
	
	if($fetch_from_db)
	{
		$deleted = 'deleted';
		if($db->hasColumn('players', 'deletion'))
			$deleted = 'deletion';

		$is_tfs10 = $db->hasTable('players_online');
		$players = $db->query('SELECT `id`, `name`, `level`, `experience`' . ($is_tfs10 ? '' : ', `online`') . ' FROM `players` WHERE `group_id` < ' . $config['highscores_groups_hidden'] . ' AND `id` NOT IN (' . implode(', ', $config['highscores_ids_hidden']) . ') AND `' . $deleted . '` = 0 AND `account_id` != 1 ORDER BY `experience` DESC LIMIT ' . (int)$limit)->fetchAll();

		if($is_tfs10) {
			foreach($players as &$player) {
				$query = $db->query('SELECT `player_id` FROM `players_online` WHERE `player_id` = ' . $player['id']);
				$player['online'] = ($query->rowCount() > 0 ? 1 : 0);
			}
		}

		$i = 0;
		foreach($players as &$player) {
			$player['rank'] = ++$i;
		}
		
		if($cache->enabled())
			$cache->set('top_' . $limit . '_level', serialize($players), 120);
	}
	
	return $players;
}

function deleteDirectory($dir) {
	if(!file_exists($dir)) {
		return true;
	}
	
	if(!is_dir($dir)) {
		return unlink($dir);
	}
	
	foreach(scandir($dir) as $item) {
		if($item == '.' || $item == '..') {
			continue;
		}
		
		if(!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
			return false;
		}
	}
	
	return rmdir($dir);
}

// validator functions
require_once(LIBS . 'validator.php');
require_once(SYSTEM . 'compat.php');
?>
