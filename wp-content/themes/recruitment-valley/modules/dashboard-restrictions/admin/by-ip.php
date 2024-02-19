<?php

/**
 * Restrict dashboard by ip
 *
 * @package MadeIndonesia
 */

defined('ABSPATH') || die("Can't access directly");

$allowed  = false;
$total_ip = (int) get_option('options_allowed_ips');

if (!$total_ip) {
	return;
}

$user_ip = $this->get_user_ip();

// start of looping allowed (ip)s.
for ($i = 0; $i < $total_ip; $i++) {
	$allowed_ip = get_option('options_allowed_ips_' . $i . '_ip');

	// if actually the same.
	if ($user_ip === $allowed_ip) {
		$allowed = true;
		break;
	} else {
		// if containing dynamic ip. eg: 182.253.142.xx .
		if (strpos($allowed_ip, '.x') !== false) {
			$explode    = explode('.x', $allowed_ip);
			$dynamic_ip = $explode[0] . '.';

			if (strpos($user_ip, $dynamic_ip) !== false) {
				$allowed = true;
				break;
			}
		} else {
			// if containing cidr/ ip range. eg: 167.202.201.0/27 .
			if (false !== strpos($allowed_ip, '/')) {
				$ip_limits  = $this->cidr_to_range($allowed_ip);
				$lowest_ip  = $ip_limits[0];
				$highest_ip = $ip_limits[1];

				$explode_lowest  = explode('.', $lowest_ip);
				$explode_highest = explode('.', $highest_ip);

				$count_start = absint(end($explode_lowest));
				$count_end   = absint(end($explode_highest));
				$ip_prefix   = rtrim($lowest_ip, $count_start);

				for (; $count_start <= $count_end; $count_start++) {
					if ($user_ip === $ip_prefix . $count_start) {
						$allowed = true;
						break;
					}
				}
			}
		}
	}
} // end of looping allowed ip(s).

if (!$allowed) {
	$this->redirect();
}
