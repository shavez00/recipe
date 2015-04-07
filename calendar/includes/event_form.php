<?php
/*
 * Copyright 2012 Sean Proctor
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

if(!defined('IN_PHPC')) {
	die("Hacking attempt");
}

require_once("$phpc_includes_path/form.php");

function event_form() {
	global $vars;

	if(empty($vars["submit_form"]))
		return display_form();

	// else
	try {
		return process_form();
	} catch(Exception $e) {
		message($e->getMessage());
		return display_form();
	}
}

function display_form() {
	global $phpc_script, $phpc_year, $phpc_month, $phpc_day, $vars, $phpcdb,
	       $phpc_cal, $phpc_user, $phpc_token, $phpcid;

	$hour24 = $phpc_cal->hours_24;
	$date_format = $phpc_cal->date_format;
	$form = new Form($phpc_script, __('Event Form'));
	$cid_select = new FormDropdownQuestion('cid', __('Calendar'));
	foreach($phpcdb->get_calendars() as $calendar) {
		if($calendar->can_write())
			$cid_select->add_option($calendar->cid,
					$calendar->title);
	}
	$form->add_part($cid_select);
	$subject_part = new FormFreeQuestion('subject', __('Subject'),
			false, $phpc_cal->subject_max, true);
	$subject_part->setAutocomplete("off");
	$form->add_part($subject_part);
	$form->add_part(new FormLongFreeQuestion('description',
				tag('', __('Description'), tag('br'),
					tag('a', attrs('href="http://daringfireball.net/projects/markdown/syntax"', 'target="_new"'), __('syntax')))));

	$when_group = new FormGroup(__('When'), 'phpc-when');
	if(isset($vars['eid'])) {
		$when_group->add_part(new FormCheckBoxQuestion('phpc-modify',
					false,
					__('Change the event date and time')));
	}
	$when_group->add_part(new FormDateTimeQuestion('start',
				__('From'), $hour24, $date_format));
	$when_group->add_part(new FormDateTimeQuestion('end', __('To'),
				$hour24, $date_format));

	$time_type = new FormDropDownQuestion('time-type', __('Time Type'));
	$time_type->add_option('normal', __('Normal'));
	$time_type->add_option('full', __('Full Day'));
	$time_type->add_option('tba', __('To Be Announced'));

	$when_group->add_part($time_type);

	$form->add_part($when_group);

	$repeat_type = new FormDropdownQuestion('repeats', __('Repeats'),
			array(), true, 'never');
	$repeat_type->add_option('never', __('Never'));
	$daily_group = new FormGroup();
	$repeat_type->add_option('daily', __('Daily'), NULL, $daily_group);
	$weekly_group = new FormGroup();
	$repeat_type->add_option('weekly', __('Weekly'), NULL, $weekly_group);
	$monthly_group = new FormGroup();
	$repeat_type->add_option('monthly', __('Monthly'), NULL, $monthly_group);
	$yearly_group = new FormGroup();
	$repeat_type->add_option('yearly', __('Yearly'), NULL, $yearly_group);

	$every_day = new FormDropdownQuestion('every-day', __('Every'),
			__('Repeat every how many days?'));
	$every_day->add_options(create_sequence(1, 30));
	$daily_group->add_part($every_day);
	$daily_group->add_part(new FormDateQuestion('daily-until', __('Until'),
				$date_format));

	$every_week = new FormDropdownQuestion('every-week', __('Every'),
			__('Repeat every how many weeks?'));
	$every_week->add_options(create_sequence(1, 30));
	$weekly_group->add_part($every_week);
	$weekly_group->add_part(new FormDateQuestion('weekly-until',
				__('Until'), $date_format));

	$every_month = new FormDropdownQuestion('every-month', __('Every'),
			__('Repeat every how many months?'));
	$every_month->add_options(create_sequence(1, 30));
	$monthly_group->add_part($every_month);
	$monthly_group->add_part(new FormDateQuestion('monthly-until',
				__('Until'), $date_format));

	$every_year = new FormDropdownQuestion('every-year', __('Every'),
			__('Repeat every how many years?'));
	$every_year->add_options(create_sequence(1, 30));
	$yearly_group->add_part($every_year);
	$yearly_group->add_part(new FormDateQuestion('yearly-until',
				__('Until'), $date_format));

	$when_group->add_part($repeat_type);

	if($phpc_cal->can_create_readonly())
		$form->add_part(new FormCheckBoxQuestion('readonly', false,
					__('Read-only')));

	$categories = new FormDropdownQuestion('catid', __('Category'));
	$categories->add_option('', __('None'));
	$have_categories = false;
	foreach($phpc_cal->get_visible_categories($phpc_user->get_uid()) as $category) {
		$categories->add_option($category['catid'], $category['name']);
		$have_categories = true;
	}
	if($have_categories)
		$form->add_part($categories);

	if(isset($vars['phpcid']))
		$form->add_hidden('phpcid', $vars['phpcid']);

	foreach($phpc_cal->get_fields() as $field) {
		$form->add_part(new FormFreeQuestion('phpc-field-'.$field['fid'], $field['name']));
	}

	$form->add_hidden('phpc_token', $phpc_token);
	$form->add_hidden('action', 'event_form');
	$form->add_hidden('submit_form', 'submit_form');

	$form->add_part(new FormSubmitButton(__("Submit Event")));

	if(isset($vars['eid'])) {
		$form->add_hidden('eid', $vars['eid']);
		$occs = $phpcdb->get_occurrences_by_eid($vars['eid']);
		$event = $occs[0];

		$defaults = array(
				'cid' => $event->get_cid(),
				'subject' => $event->get_raw_subject(),
				'description' => $event->get_raw_desc(),
				'start-date' => $event->get_short_start_date(),
				'end-date' => $event->get_short_end_date(),
				'start-time' => $event->get_start_time(),
				'end-time' => $event->get_end_time(),
				'readonly' => $event->is_readonly(),
				);

		foreach($event->get_fields() as $field) {
			$defaults["phpc-field-{$field['fid']}"] = $field['value'];
		}

		if(!empty($event->catid))
			$defaults['catid'] = $event->catid;

		switch($event->get_time_type()) {
			case 0:
				$defaults['time-type'] = 'normal';
				break;
			case 1:
				$defaults['time-type'] = 'full';
				break;
			case 2:
				$defaults['time-type'] = 'tba';
				break;
		}

		add_repeat_defaults($occs, $defaults);

	} else {
		$hour24 = $phpc_cal->hours_24;
		$datefmt = $phpc_cal->date_format;
		$date_string = format_short_date_string($phpc_year, $phpc_month,
				$phpc_day, $datefmt);
		$defaults = array(
				'cid' => $phpcid,
				'start-date' => $date_string,
				'end-date' => $date_string,
				'start-time' => format_time_string(17, 0, $hour24),
				'end-time' => format_time_string(18, 0, $hour24),
				'daily-until-date' => $date_string,
				'weekly-until-date' => $date_string,
				'monthly-until-date' => $date_string,
				'yearly-until-date' => $date_string,
				);
	}
	return $form->get_form($defaults);
}

function add_repeat_defaults($occs, &$defaults) {
	// TODO: Handle unevenly spaced occurrences

	$defaults['repeats'] = 'never';

	if(sizeof($occs) < 2)
		return;

	$event = $occs[0];
	$day = $event->get_start_day();
	$month = $event->get_start_month();
	$year = $event->get_start_year();

	// Test if they repeat every N years
	$nyears = $occs[1]->get_start_year() - $event->get_start_year();
	$repeats_yearly = true;
	$nmonths = ($occs[1]->get_start_year() - $year) * 12
		+ $occs[1]->get_start_month() - $month;
	$repeats_monthly = true;
	$ndays = days_between($event->get_start_ts(), $occs[1]->get_start_ts());
	$repeats_daily = true;

	for($i = 1; $i < sizeof($occs); $i++) {
		$cur_occ = $occs[$i];
		$cur_year = $cur_occ->get_start_year();
		$cur_month = $cur_occ->get_start_month();
		$cur_day = $cur_occ->get_start_day();

		// Check year
		$cur_nyears = $cur_year - $occs[$i - 1]->get_start_year();
		if($cur_day != $day || $cur_month != $month
				|| $cur_nyears != $nyears) {
			$repeats_yearly = false;
		}

		// Check month
		$cur_nmonths = ($cur_year - $occs[$i - 1]->get_start_year())
			* 12 + $cur_month - $occs[$i - 1]->get_start_month();
		if($cur_day != $day || $cur_nmonths != $nmonths) {
			$repeats_monthly = false;
		}

		// Check day
		$cur_ndays = days_between($occs[$i - 1]->get_start_ts(),
				$occs[$i]->get_start_ts());
		if($cur_ndays != $ndays) {
			$repeats_daily = false;
		}
	}

	$defaults['yearly-until-date'] = "$cur_month/$cur_day/$cur_year";
	$defaults['monthly-until-date'] = "$cur_month/$cur_day/$cur_year";
	$defaults['weekly-until-date'] = "$cur_month/$cur_day/$cur_year";
	$defaults['daily-until-date'] = "$cur_month/$cur_day/$cur_year";

	if($repeats_daily) {
		// repeats weekly
		if($ndays % 7 == 0) {
			$defaults['repeats'] = 'weekly';
			$defaults['every-week'] = $ndays / 7;
		} else {
			$defaults['every-week'] = 1;

			// repeats daily
			$defaults['repeats'] = 'daily';
			$defaults['every-day'] = $ndays;
		}

	} else {
		$defaults['every-day'] = 1;
		$defaults['every-week'] = 1;
	}

	if($repeats_monthly) {
		$defaults['repeats'] = 'monthly';
		$defaults['every-month'] = $nmonths;
	} else {
		$defaults['every-month'] = 1;
	}

	if($repeats_yearly) {
		$defaults['repeats'] = 'yearly';
		$defaults['every-year'] = $nyears;
	} else {
		$defaults['every-year'] = 1;
	}
}

function process_form()
{
	global $vars, $phpcdb, $phpc_script, $phpc_user, $phpc_cal;

	// When modifying events, this is the value of the checkbox that
	//   determines if the date should change
	$modify_occur = !isset($vars['eid']) || !empty($vars['phpc-modify']);

	if($modify_occur) {
		$start_ts = get_timestamp("start");
		$end_ts = get_timestamp("end");

		switch($vars["time-type"]) {
			case 'normal':
				$time_type = 0;
				break;

			case 'full':
				$time_type = 1;
				break;

			case 'tba':
				$time_type = 2;
				break;

			default:
				soft_error(__("Unrecognized Time Type."));
		}

		$duration = $end_ts - $start_ts;
		if($duration < 0) {
			throw new Exception(__("An event cannot have an end earlier than its start."));
		}
	}

	verify_token();

	if(!isset($vars['cid'])) {
		throw new Exception(__("Calendar ID is not set."));
	}

	$cid = $vars['cid'];
	$calendar = $phpcdb->get_calendar($cid);

	if(!$calendar->can_write())
		permission_error(__('You do not have permission to write to this calendar.'));

	if($calendar->can_create_readonly() && !empty($vars['readonly']))
		$readonly = true;
	else
		$readonly = false;

	$catid = empty($vars['catid']) ? false : $vars['catid'];

	if(!isset($vars['eid'])) {
		$modify = false;
		$eid = $phpcdb->create_event($cid, $phpc_user->get_uid(),
				$vars["subject"], $vars["description"],
				$readonly, $catid);
	} else {
		$modify = true;
		$eid = $vars['eid'];
		$phpcdb->modify_event($eid, $vars['subject'],
				$vars['description'], $readonly, $catid);
		if($modify_occur)
			$phpcdb->delete_occurrences($eid);
	}

	foreach($phpc_cal->get_fields() as $field) {
		$fid = $field['fid'];
		if(empty($vars["phpc-field-$fid"])) {
			if($field['required'])
				throw new Exception(sprintf(__('Field "%s" is required but was not set.'), $field['name']));
			continue;
		}
		$phpcdb->add_event_field($eid, $fid, $vars["phpc-field-$fid"]);
	}

	if($modify_occur) {
		$occurrences = 0;
		$n = 1;
		$until = $start_ts;
		switch($vars['repeats']) {
		case 'daily':
			check_input("every-day");
			$n = $vars["every-day"];
			$until = get_timestamp("daily-until");
			break;

		case 'weekly':
			check_input("every-week");
			$n = $vars["every-week"] * 7;
			$until = get_timestamp("weekly-until");
			break;

		case 'monthly':
			check_input("every-month");
			$n = $vars["every-month"];
			$until = get_timestamp("monthly-until");
			break;

		case 'yearly':
			check_input("every-year");
			$n = $vars["every-year"];
			$until = get_timestamp("yearly-until");
			break;
		}
		if($n < 1)
			soft_error(__('Increment must be 1 or greater.'));

		while($occurrences <= 730 && days_between($start_ts, $until) >= 0) {
			$oid = $phpcdb->create_occurrence($eid, $time_type, $start_ts, $end_ts);
			$occurrences++;

			switch($vars["repeats"]) {
			case 'daily':
			case 'weekly':
				$start_ts = add_days($start_ts, $n);
				$end_ts = add_days($end_ts, $n);
				break;

			case 'monthly':
				$start_ts = add_months($start_ts, $n);
				$end_ts = add_months($end_ts, $n);
				break;

			case 'yearly':
				$start_ts = add_years($start_ts, $n);
				$end_ts = add_years($end_ts, $n);
				break;

			default:
				break 2;
			}
		}
	}

	if($eid != 0) {
		if($modify)
			$message = __("Modified event: ");
		else
			$message = __("Created event: ");

		return message_redirect(tag('', $message,
					create_event_link($eid, 'display_event',
						$eid)),
				"$phpc_script?action=display_event&eid=$eid");
	} else {
		return message_redirect(__('Error submitting event.'),
				"$phpc_script?action=display_month&phpcid=$cid");
	}
}

?>
