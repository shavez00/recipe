<?php
/*
 * Copyright 2013 Sean Proctor
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/*
   this file contains all the re-usable functions for the calendar
*/

if ( !defined('IN_PHPC') ) {
       die("Hacking attempt");
}

require_once("$phpc_includes_path/Gettext_PHP.php");
require_once("$phpc_includes_path/html.php");
require_once("$phpc_includes_path/util.php");

function __($msg) {
	global $phpc_gettext;

	if (empty($phpc_gettext))
		return $msg;

	return $phpc_gettext->gettext($msg);
}

function __p($context, $msg) {
	global $phpc_gettext;

	return $phpc_gettext->pgettext($context, $msg);
}

// checks global variables to see if the user is logged in.
function is_user() {
	global $phpc_user;

	return $phpc_user->uid > 0;
}

function is_admin() {
	global $phpc_user;

	return $phpc_user->admin;
}

function login_user($username, $password)
{
        global $phpcdb;

	// Regenerate the session in case our non-logged in version was
	//   snooped
	// TODO: Verify that this is needed, and make sure it's called in setup
	// 	 so it doesn't create issues for embedded users
	// session_regenerate_id();

	$user = $phpcdb->get_user_by_name($username);
	if(!$user || $user->password != md5($password))
		return false;

	phpc_do_login($user);

	return true;
}

function phpc_do_login($user, $series_token = false) {
        global $phpcdb, $phpc_prefix;

	$uid = $user->uid;
	$login_token = phpc_get_token();
	$_SESSION["{$phpc_prefix}uid"] = $uid;
	$_SESSION["{$phpc_prefix}login"] = $login_token;

	if(!$series_token) {
		$series_token = phpc_get_token();
		$phpcdb->add_login_token($uid, $series_token, $login_token);
	} else {
		$phpcdb->update_login_token($uid, $series_token, $login_token);
	}

	// TODO: Add a remember me checkbox to the login form, and have the
	//	cookies expire at the end of the session if it's not checked

	// expire credentials in 30 days.
	$expiration_time = time() + 30 * 24 * 60 * 60;
	setcookie("{$phpc_prefix}uid", $uid, $expiration_time);
	setcookie("{$phpc_prefix}login", $login_token, $expiration_time);
	setcookie("{$phpc_prefix}login_series", $series_token, $expiration_time);

	return true;
}

function phpc_do_logout() {
	global $phpc_prefix;
   	session_destroy();
	setcookie("{$phpc_prefix}uid", "", time() - 3600);
	setcookie("{$phpc_prefix}login", "", time() - 3600);
	setcookie("{$phpc_prefix}login_series", "", time() - 3600);
}

// returns tag data for the links at the bottom of the calendar
function footer()
{
	global $phpc_tz, $phpc_lang;

	$tag = tag('div', attributes('class="phpc-bar ui-widget-content"'),
			"[" . __('Language') . ": $phpc_lang]" .
			" [" . __('Timezone') . ": $phpc_tz]");

	if(defined('PHPC_DEBUG')) {
		$tag->add(tag('a', attrs('href="http://validator.w3.org/check/referer"'), 'Validate HTML'));
		$tag->add(tag('a', attrs('href="http://jigsaw.w3.org/css-validator/check/referer"'),
					'Validate CSS'));
		$tag->add(tag('span', "Internal Encoding: " . mb_internal_encoding() . " Output Encoding: " . mb_http_output()));
	}

	return $tag;
}

function get_languages() {
	global $phpc_locale_path;

	static $langs = NULL;

	if(!empty($langs))
		return $langs;

	// create links for each existing language translation
	$handle = opendir($phpc_locale_path);

	if(!$handle)
		soft_error("Error reading locale directory.");

	$langs = array('en');
	while(($filename = readdir($handle)) !== false) {
		$pathname = "$phpc_locale_path/$filename";
		if(strncmp($filename, ".", 1) == 0 || !is_dir($pathname))
			continue;
		if(file_exists("$pathname/LC_MESSAGES/messages.mo"))
			$langs[] = $filename;
	}

	closedir($handle);

	return $langs;
}

function day_of_week_start()
{
	global $phpc_cal;

	return $phpc_cal->week_start;
}

// returns the number of days in the week before the 
//  taking into account whether we start on sunday or monday
function day_of_week($month, $day, $year)
{
	return day_of_week_ts(mktime(0, 0, 0, $month, $day, $year));
}

// returns the number of days in the week before the 
//  taking into account whether we start on sunday or monday
function day_of_week_ts($timestamp)
{
	$days = date('w', $timestamp);

	return ($days + 7 - day_of_week_start()) % 7;
}

// returns the number of days in $month
function days_in_month($month, $year)
{
	return date('t', mktime(0, 0, 0, $month, 1, $year));
}

//returns the number of weeks in $month
function weeks_in_month($month, $year)
{
	$days = days_in_month($month, $year);

	// days not in this month in the partial weeks
	$days_before_month = day_of_week($month, 1, $year);
	$days_after_month = 6 - day_of_week($month, $days, $year);

	// add up the days in the month and the outliers in the partial weeks
	// divide by 7 for the weeks in the month
	return ($days_before_month + $days + $days_after_month) / 7;
}

function weeks_in_year($year) {
	// This is true for ISO, not US
	if(day_of_week_start() == 1)
		return date("W", mktime(0, 0, 0, 12, 28, $year));
	// else
	return ceil((day_of_week(1, 1, $year) + days_in_year($year)) / 7.0);
}

// return the week number corresponding to the $day.
function week_of_year($month, $day, $year)
{
	$timestamp = mktime(0, 0, 0, $month, $day, $year);

	// week_start = 1 uses ISO 8601 and contains the Jan 4th,
	//   Most other places the first week contains Jan 1st
	//   There are a few outliers that start weeks on Monday and use
	//   Jan 1st for the first week. We'll ignore them for now.
	if(day_of_week_start() == 1) {
		$year_contains = 4;
		// if the week is in December and contains Jan 4th, it's a week
		// from next year
		if($month == 12 && $day - 24 >= $year_contains) {
			$year++;
			$month = 1;
			$day -= 31;
		}
	} else {
		$year_contains = 1;
	}
	
	// $day is the first day of the week relative to the current month,
	// so it can be negative. If it's in the previous year, we want to use
	// that negative value, unless the week is also in the previous year,
	// then we want to switch to using that year.
	if($day < 1 && $month == 1 && $day > $year_contains - 7) {
		$day_of_year = $day - 1;
	} else {
		$day_of_year = date('z', $timestamp);
		$year = date('Y', $timestamp);
	}

	/* Days in the week before Jan 1. */
	$days_before_year = day_of_week(1, $year_contains, $year);

	// Days left in the week
	$days_left = 8 - day_of_week_ts($timestamp) - $year_contains;

	/* find the number of weeks by adding the days in the week before
	 * the start of the year, days up to $day, and the days left in
	 * this week, then divide by 7 */
	return ($days_before_year + $day_of_year + $days_left) / 7;
}

function create_event_link($text, $action, $eid, $attribs = false)
{
	return create_action_link($text, $action, array('eid' => $eid), $attribs);
}

function create_occurrence_link($text, $action, $oid, $attribs = false)
{
	return create_action_link($text, $action, array('oid' => $oid), $attribs);
}

function create_action_link_with_date($text, $action, $year = false,
		$month = false, $day = false, $attribs = false)
{
	$args = array();
	if($year !== false)
		$args["year"] = $year;
	if($month !== false)
		$args["month"] = $month;
	if($day !== false)
		$args["day"] = $day;

	return create_action_link($text, $action, $args, $attribs);
}

function create_action_link($text, $action, $args = false, $attribs = false)
{
	global $phpc_script, $vars, $phpcid;

	$url = "href=\"$phpc_script?action=" . htmlentities($action);

	if (!$args) {
		$args = array();
	}
	if (!array_key_exists("phpcid", $args)) {
		$args["phpcid"] = htmlentities($phpcid);
	}

	foreach ($args as $key => $value) {
		if(empty($value))
			continue;
		if (is_array($value)) {
			foreach ($value as $v) {
				$url .= "&amp;" . htmlentities("{$key}[]=$v");
			}
		} else {
			$url .= "&amp;" . htmlentities("$key=$value");
		}
	}
	$url .= '"';

	if($attribs !== false) {
		$attribs->add($url);
	} else {
		$attribs = attrs($url);
	}
	return tag('a', $attribs, $text);
}

// takes a menu $html and appends an entry
function menu_item_append(&$html, $name, $action, $args = false,
		$attribs = false)
{
	$name=str_replace(' ','&nbsp;',$name); /*not breaking space on menus*/

	if(!is_object($html)) {
		soft_error('Html is not a valid Html class.');
	}
	$html->add(create_action_link($name, $action, $args, $attribs));
	$html->add("\n");
}

// takes a menu $html and appends an entry with the date
function menu_item_append_with_date(&$html, $name, $action, $year = false,
		$month = false, $day = false, $attribs = false)
{
	$name = str_replace(' ','&nbsp;',$name);

	if(!is_object($html)) {
		soft_error('Html is not a valid Html class.');
	}
	$html->add(create_action_link_with_date($name, $action, $year, $month,
			$day, $attribs));
	$html->add("\n");
}

// same as above, but prepends the entry
function menu_item_prepend(&$html, $name, $action, $args = false,
		$attribs = false)
{
	if(!is_object($html)) {
		soft_error('Html is not a valid Html class.');
	}
	$html->prepend("\n");
	$html->prepend(create_action_link($name, $action, $args, $attribs));
}

// creates a hidden input for a form
// returns tag data for the input
function create_hidden($name, $value)
{
	return tag('input', attrs("name=\"$name\"", "value=\"$value\"", 'type="hidden"'));
}

// creates a submit button for a form
// return tag data for the button
function create_submit($value)
{
	return tag('input', attrs("value=\"$value\"", 'type="submit"'));
}

// creates a text entry for a form
// returns tag data for the entry
function create_text($name, $value = false)
{
	$attributes = attributes("name=\"$name\"", 'type="text"');
	if($value !== false) {
		$attributes->add("value=\"$value\"");
	}
	return tag('input', $attributes);
}

// creates a password entry for a form
// returns tag data for the entry
function create_password($name)
{
	return tag('input', attributes("name=\"$name\"", 'type="password"'));
}

// creates a checkbox for a form
// returns tag data for the checkbox
function create_checkbox($name, $value, $checked = false, $label = false)
{
	$attrs = attrs("id=\"$name\"", "name=\"$name\"", 'type="checkbox"', "value=\"$value\"");
	if(!empty($checked))
		$attrs->add('checked="checked"');
	$input = tag('input', $attrs);
	if($label !== false)
		return array($input, tag('label', attrs("for=\"$name\""), $label));
	else
		return $input;
}

// $title - string or html element displayed by default
// $values - Array of URL => title
// returns an html structure for a dropdown box that will change the page
//		to the URL from $values when an element is selected
function create_dropdown_list($title, $values, $attrs = false) {
	$list = tag('ul');
	foreach($values as $key => $value) {
		$list->add(tag('li', tag('a', attrs("href=\"$key\""), $value)));
	}
	return tag('div', attrs('class="phpc-dropdown-list"'),
			tag('span', attrs('class="phpc-dropdown-list-header"'),
				tag('span', attrs('class="phpc-dropdown-list-title"'),
					$title)),
			$list);
}

// creates the user menu
// returns tag data for the menu
function userMenu()
{
	global $action, $phpc_user;

	if(is_user()) {
		$welcome = __('Welcome') . '&nbsp;' . $phpc_user->username;
	} else {
		$welcome = "";
	}

	$span = tag('span');
	
	$html = tag('div', attrs('class="phpc-logged ui-widget-content"'), $welcome, $span);

	if($action != 'user_settings')
		menu_item_append($span, __('Settings'), 'user_settings');
		
	if(is_user()) {
		menu_item_append($span, __('Log out'), 'logout',
				array('lasturl' => escape_entities(urlencode($_SERVER['QUERY_STRING']))));
	} else {
		menu_item_append($span, __('Log in'), 'login',
				array('lasturl' => escape_entities(urlencode($_SERVER['QUERY_STRING']))));
	}
	return $html;
}

// creates the navbar for the top of the calendar
// returns tag data for the navbar
function navbar()
{
	global $vars, $action, $phpc_year, $phpc_month, $phpc_day, $phpc_cal;

	$html = tag('div', attrs('class="phpc-bar ui-widget-header"'));

	$args = array('year' => $phpc_year,
			'month' => $phpc_month,
			'day' => $phpc_day);

	// TODO There needs to be a better way to decide what to show
	if(isset($phpc_cal) && $phpc_cal->can_write() && $action != 'add') { 
		menu_item_append($html, __('Add Event'), 'event_form', $args);
	}

	if($action != 'search') {
		menu_item_append($html, __('Search'), 'search', $args);
	}

	if($action != 'display_month') {
		menu_item_append($html, __('View Month'), 'display_month',
			$args);
	}

	if($action == 'display_event') {
		menu_item_append($html, __('View date'), 'display_day', $args);
	}

	if(isset($phpc_cal) && $phpc_cal->can_admin() && $action != 'cadmin') {
		menu_item_append($html, __('Calendar Admin'), 'cadmin');
	}

	if(is_admin() && $action != 'admin') {
		menu_item_append($html, __('Admin'), 'admin');
	}

	return $html;
}

// creates an array from $start to $end, with an $interval
function create_sequence($start, $end, $interval = 1, $display = NULL)
{
	$arr = array();
	for ($i = $start; $i <= $end; $i += $interval){
		if($display) {
			$arr[$i] = call_user_func($display, $i);
		} else {
			$arr[$i] = $i;
		}
	}
	return $arr;
}

function get_config_options()
{
	static $options = NULL;

	if($options === NULL) {
		$options = init_config_options();
	}
	return $options;
}

function init_config_options() {
	$languages = array("" => __("Default"));
	foreach(get_languages() as $language) {
		$languages[$language] = $language;
	}
	// name, text, type, value(s)
	return array( 
			array('week_start', __('Week Start'), PHPC_DROPDOWN,
				array(
					0 => __('Sunday'),
					1 => __('Monday'),
					6 => __('Saturday')
				     ), 0),
			array('hours_24', __('24 Hour Time'), PHPC_CHECK),
			array('title', __('Calendar Title'), PHPC_TEXT),
			array('subject_max', __('Maximum Subject Length'), PHPC_TEXT, 50),
			array('events_max', __('Events Display Daily Maximum'), PHPC_TEXT, 8),
			array('anon_permission', __('Public Permissions'), PHPC_DROPDOWN,
				array(
					__('Cannot read nor write events'),
					__('Can read but not write events'),
					__('Can create but not modify events'),
					__('Can create and modify events')
				     )
			     ),
			array('timezone', __('Default Timezone'), PHPC_MULTI_DROPDOWN, get_timezone_list()),
			array('language', __('Default Language'), PHPC_DROPDOWN,
				$languages),
			array('date_format', __('Date Format'), PHPC_DROPDOWN,
					get_date_format_list()),
			array('theme', __('Theme'), PHPC_DROPDOWN,
					get_theme_list()),
	);
}

function get_theme_list() {
	$themes = array(
			'black-tie',
			'blitzer',
			'cupertino',
			'dark-hive',
			'dot-luv',
			'eggplant',
			'excite-bike',
			'flick',
			'hot-sneaks',
			'humanity',
			'le-frog',
			'mint-choc',
			'overcast',
			'pepper-grinder',
			'redmond',
			'smoothness',
			'south-street',
			'start',
			'sunny',
			'swanky-purse',
			'trontastic',
			'ui-darkness',
			'ui-lightness',
			'vader');

	$theme_list = array(NULL => __('Default'));
	foreach($themes as $theme) {
		$theme_list[$theme] = $theme;
	}
	return $theme_list;	
}

function get_timezone_list() {
	$timezones = array();
	$timezones[__("Default")] = "";
	foreach(timezone_identifiers_list() as $timezone) {
		$sp = explode("/", $timezone, 2);
		$continent = $sp[0];
		if(empty($sp[1])) {
			$timezones[$continent] = $timezone;
		} else {
			$area = $sp[1];
			if(empty($timezones[$continent]))
				$timezones[$continent] = array();
			$timezones[$continent][$timezone] = $area;
		}
	}
	return $timezones;
}

function get_date_format_list()
{
	return array(	__("Month Day Year"),
			__("Year Month Day"),
			__("Day Month Year"));
}

function display_phpc() {
	global $phpc_messages, $phpc_redirect, $phpc_script, $phpc_prefix,
	       $phpc_title, $phpcdb, $phpc_cal, $phpc_home_url;

	$navbar = false;

	try {
		$calendars = $phpcdb->get_calendars();
		$list = array();
		if(isset($phpc_cal)) {
			$phpc_title = $phpc_cal->get_title();
			$title_link = tag('a', attrs("href='$phpc_home_url?phpcid={$phpc_cal->get_cid()}'",
						'class="phpc-dropdown-list-title"'), $phpc_title);
		} else {
			$phpc_title = __("(No calendars)");
			$title_link = $phpc_title;
		}
		foreach($calendars as $calendar) {
			$list["$phpc_home_url?phpcid={$calendar->get_cid()}"] = 
				$calendar->get_title();
		}
		if (sizeof($calendars) > 1) {
			$title_tag = create_dropdown_list($title_link, $list);
		} else {
			$title_tag = $title_link;
		}
		$content = do_action();
		if(sizeof($phpc_messages) > 0) {
			$messages = tag('div');
			foreach($phpc_messages as $message) {
				$messages->add($message);
			}
			// If we're redirecting, the messages might not get
			//   seen, so don't clear them
			if(empty($phpc_redirect))
				$_SESSION["{$phpc_prefix}messages"] = NULL;
		} else {
			$messages = '';
		}

		return tag('div', attrs('class="php-calendar ui-widget"'),
				userMenu(),
				tag('br', attrs('style="clear:both;"')),
				tag('div', attrs('class="phpc-title ui-widget-header"'), $title_tag),
				navbar(), $messages, $content, footer());

	} catch(PermissionException $e) {
		$msg = __('You do not have permission to do that: ') . $e->getMessage();
		if(is_user())
			return error_message_redirect($msg, $phpc_script);
		else
			return error_message_redirect($msg,
					"$phpc_script?action=login");
	} catch(InvalidInputException $e) {
		return error_message_redirect($e->getMessage(), $e->target);
	} catch(Exception $e) {
		return display_exception($e, $navbar);
	}
}

function display_exception($e, $navbar = false) {
	global $phpc_title;

	$phpc_title = $e->getMessage();
	$results = tag('');
	if($navbar !== false)
		$results->add($navbar);
	$backtrace = tag("ol");
	foreach($e->getTrace() as $bt) {
		$filename = basename($bt["file"]);
		$args = array();
		if(isset($bt["args"])) { 
			foreach($bt["args"] as $arg) {
				if(is_string($arg)) {
					$args[] = "'$arg'";
				} else {
					$args[] = $arg;
				}
			}
			$args_string = implode(", ", $args);
		} else {
			$args_string = "...";
		}
		$backtrace->add(tag("li", "$filename({$bt["line"]}): {$bt["function"]}($args_string)"));
	}
	$results->add(tag('div', attrs('class="php-calendar"'),
				tag('h2', __('Error')),
				tag('p', $e->getMessage()),
				tag('h3', __('Backtrace')),
				$backtrace));
	return $results;
}

function do_action()
{
	global $action, $phpc_includes_path, $vars;

	$action_file = "$phpc_includes_path/$action.php";
	if(!preg_match('/^\w+$/', $action) || !file_exists($action_file))
		soft_error(__('Invalid action'));

	require_once($action_file);

	eval("\$action_output = $action();");

	return $action_output;
}

// takes a number of the month, returns the name
function month_name($month)
{
        global $month_names;

	$month = ($month - 1) % 12 + 1;
        return $month_names[$month];
}

//takes a day number of the week, returns a name (0 for the beginning)
function day_name($day)
{
	global $day_names;

	$day = $day % 7;

        return $day_names[$day];
}

function short_month_name($month)
{
        global $short_month_names;

	$month = ($month - 1) % 12 + 1;
        return $short_month_names[$month];
}

function verify_token() {
	global $phpc_prefix, $vars, $phpc_token;

	if(!is_user())
		return true;

	if(empty($vars["phpc_token"]) || $vars["phpc_token"] != $phpc_token) {
		//echo "<pre>real token: $phpc_token\n";
		//echo "form token: {$vars["phpc_token"]}</pre>";
		soft_error(__("Secret token mismatch. Possible request forgery attempt."));
	}
}

// $element: { name, text, type, value(s) }
function create_config_input($element, $default = false)
{
	$name = $element[0];
	$text = $element[1];
	$type = $element[2];
	$value = false;
	if(isset($element[3]))
		$value = $element[3];

	switch($type) {
		case PHPC_CHECK:
			if($default == false)
				$default = $value;
			$input = create_checkbox($name, '1', $default, $text);
			break;
		case PHPC_TEXT:
			if($default == false)
				$default = $value;
			$input = create_text($name, $default);
			break;
		case PHPC_DROPDOWN:
			$input = create_select($name, $value, $default);
			break;
		case PHPC_MULTI_DROPDOWN:
			$input = create_multi_select($name, $value, $default);
			break;
		default:
			soft_error(__('Unsupported config type') . ": $type");
	}
	return $input;
}

/* Make a timestamp from the input fields $prefix-time and $prefix-date
   uses $phpc_cal->date_format to determine the format of the date
   if there's no $prefix-time, uses values passed as parameters
*/
function get_timestamp($prefix, $hour = 0, $minute = 0, $second = 0)
{
	global $vars, $phpc_cal;

	check_input("$prefix-date");

	if(!empty($vars["$prefix-time"])) {
		if(!preg_match('/(\d+)[:\.](\d+)\s?(\w+)?/', $vars["$prefix-time"],
					$time_matches)) {
			throw new Exception(sprintf(__("Malformed \"%s\" time: \"%s\""), $prefix, $vars["$prefix-time"]));
		}
		$hour = $time_matches[1];
		$minute = $time_matches[2];
		if(isset($time_matches[3])) {
			$period = $time_matches[3];
			if($hour == 12)
				$hour = 0;
			if(strcasecmp("am", $period) == 0) {
				// AM
			} else if(strcasecmp("pm", $period) == 0) {
				$hour += 12;
			} else {
				throw new Exception(__("Unrecognized period: ") . $period);
			}
		}
	}

	if(!preg_match('/(\d+)[\.\/\-\ ](\d+)[\.\/\-\ ](\d+)/', $vars["$prefix-date"], $date_matches)) {
		throw new Exception(sprintf(__("Malformed \"%s\" date: \"%s\""), $prefix, $vars["$prefix-date"]));
	}
	
	switch($phpc_cal->date_format) {
		case 0: // Month Day Year
			$month = $date_matches[1];
			$day = $date_matches[2];
			$year = $date_matches[3];
			break;
		case 1: // Year Month Day
			$year = $date_matches[1];
			$month = $date_matches[2];
			$day = $date_matches[3];
			break;
		case 2: // Day Month Year
			$day = $date_matches[1];
			$month = $date_matches[2];
			$year = $date_matches[3];
			break;
		default:
			throw new Exception(__("Invalid date_format."));
	}

	return mktime($hour, $minute, $second, $month, $day, $year);
}

function print_update_form() {
	global $phpc_script;

	echo "<!DOCTYPE html>
<html>
  <head>
    <title>PHP-Calendar Update</title>
  </head>
  <body>
    <h2>PHP-Calendar Updater</h2>
    <p>Your PHP-Calendar database needs to be updated. You should make a backup of your existing database before running the updater.
    <p><a href=\"$phpc_script?update=1\">Update now</a>
  </body>
</html>";
}
?>
