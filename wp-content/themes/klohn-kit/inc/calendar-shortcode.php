<?php
declare(strict_types=1);

/**
 * Shortcode: [kk_calendar src="/fetch-calendar.php" start="2026-01-01" end="2026-12-31" max="100" group_by_month="1"]
 */


require_once __DIR__ . '/CalFileParser.php';


add_shortcode('kk_calendar', 'kk_calendar_shortcode');

function kk_calendar_shortcode(array $atts): string
{
	$atts = shortcode_atts([
		'ics_url'        => '', // NEW
		'start'          => '',         // YYYY-MM-DD, default: today
		'end'            => '',         // YYYY-MM-DD, default: +1 year
		'max'            => '999',
		'group_by_month' => '1',        // 1/0
		'cache_minutes'  => '0',
		'link_text'      => '» Details',
	], $atts, 'kk_calendar');


	$max = max(1, (int)$atts['max']);
	$groupByMonth = ((string)$atts['group_by_month'] !== '0');

	$tz = wp_timezone();

	$start = kk_calendar_parse_ymd((string)$atts['start'], $tz) ?? new DateTimeImmutable('today', $tz);

	$end = kk_calendar_parse_ymd((string)$atts['end'], $tz); // optional
	$endInclusive = $end ? $end->setTime(23, 59, 59) : null; // null = no upper bound



	$icsUrl = trim((string) $atts['ics_url']);


	if ($icsUrl !== '') {
		$data = kk_calendar_fetch_from_ics($icsUrl, (int)$atts['cache_minutes']);
	}


	if (!is_array($data)) {
		return '<div class="kk-calendar kk-calendar--error">Calendar data unavailable.</div>';
	}

	$events = kk_calendar_prepare_events($data, $tz);

	// Filter by range (overlap logic)
	$events = array_values(array_filter($events, function(array $e) use ($start, $endInclusive) {
		$s  = $e['_start'];
		$en = $e['_end_effective'];

		if ($en < $start) {
			return false;
		}
		if ($endInclusive instanceof DateTimeImmutable && $s > $endInclusive) {
			return false;
		}
		return true;
	}));

	// Sort by start date/time
	usort($events, fn($a, $b) => $a['_start'] <=> $b['_start']);

	// Limit
	if (count($events) > $max) {
		$events = array_slice($events, 0, $max);
	}

	if (!$events) {
		return '<div class="kk-calendar kk-calendar--empty">No events in this period.</div>';
	}

	$dateFormat = (string)get_option('date_format');
	$timeFormat = (string)get_option('time_format');

	$out = '<div class="kk-calendar">';

	$currentMonthKey = null;

	foreach ($events as $event) {
		/** @var DateTimeImmutable $s */
		/** @var DateTimeImmutable $en */
		$s  = $event['_start'];
		$en = $event['_end_effective'];
		$isAllDay = (bool)$event['_all_day'];

		if ($groupByMonth) {
			$monthKey = $s->format('Y-m');
			if ($monthKey !== $currentMonthKey) {
				$currentMonthKey = $monthKey;
				$out .= '<h2 class="kk-calendar__month">' . esc_html(wp_date('F Y', $s->getTimestamp(), $tz)) . '</h2>';
			}
		}

		$summary = (string)($event['SUMMARY'] ?? '');
		$description = (string)($event['DESCRIPTION'] ?? '');

		$out .= '<section class="kk-calendar__entry">';

		// Date/time line
		$out .= '<div class="kk-calendar__when">';
		$out .= kk_calendar_format_when($s, $en, $isAllDay, $dateFormat, $timeFormat, $tz);
		$out .= '</div>';

		// Title
		if ($summary !== '') {
			$out .= '<div class="kk-calendar__title">' . esc_html($summary) . '</div>';
		}

		// Description
		if (trim($description) !== '') {
			$linkText = (string) $atts['link_text'];
			$out .= '<div class="kk-calendar__desc">' . kk_calendar_render_description($description, $linkText) . '</div>';

		}

		$out .= '</section>';
	}

	$out .= '</div>';

	return $out;
}

function kk_calendar_fetch_from_ics(string $icsUrl, int $cacheMinutes): mixed
{

	if (!class_exists('CalFileParser')) {
		error_log('[kk_calendar] CalFileParser class not found.');
		return null;
	}

	$icsUrl = esc_url_raw($icsUrl);
	if ($icsUrl === '') {
		return null;
	}

	$cacheKey = 'kk_calendar_ics_' . md5($icsUrl);
	if ($cacheMinutes > 0) {
		$cached = get_transient($cacheKey);
		if ($cached !== false) {
			return $cached;
		}
	}

	$cal = new CalFileParser();
	$cal->set_output('json');

	// Parser returns JSON string
	$json = $cal->parse($icsUrl);

	if (!is_string($json) || trim($json) === '') {
		return null;
	}

	// Decode (with your “strip junk before JSON” fallback if needed)
	try {
		$data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
	} catch (JsonException $e) {
		$clean = kk_calendar_extract_json_substring($json);
		try {
			$data = json_decode($clean, true, 512, JSON_THROW_ON_ERROR);
		} catch (JsonException $e2) {
			error_log('[kk_calendar] ICS JSON decode failed. First 200 chars: ' . substr($json, 0, 200));
			return null;
		}
	}

	if ($cacheMinutes > 0 && $data !== null) {
		set_transient($cacheKey, $data, $cacheMinutes * MINUTE_IN_SECONDS);
	}

	return $data;
}


function kk_calendar_normalize_url(string $src): string
{
	// Absolute URL?
	if (preg_match('~^https?://~i', $src)) {
		return $src;
	}
	// Ensure it starts with /
	if ($src[0] !== '/') {
		$src = '/' . $src;
	}
	return home_url($src);
}

function kk_calendar_fetch_json(string $url, int $cacheMinutes): mixed
{
	$cacheKey = 'kk_calendar_' . md5($url);
	if ($cacheMinutes > 0) {
		$cached = get_transient($cacheKey);
		if ($cached !== false) {
			return $cached;
		}
	}

	$resp = wp_remote_get($url, [
		'timeout' => 10,
		'headers' => ['Accept' => 'application/json'],
	]);

	if (is_wp_error($resp)) {
		error_log('[kk_calendar] HTTP error: ' . $resp->get_error_message());
		return null;
	}

	$code = (int) wp_remote_retrieve_response_code($resp);
	if ($code < 200 || $code >= 300) {
		error_log('[kk_calendar] HTTP status ' . $code . ' for ' . $url);
		return null;
	}

	$body = (string) wp_remote_retrieve_body($resp);

	// Try strict decode first
	try {
		$data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
	} catch (JsonException $e) {
		// Fallback: response contains HTML/notices before JSON
		$json = kk_calendar_extract_json_substring($body);

		try {
			$data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
		} catch (JsonException $e2) {
			error_log('[kk_calendar] JSON decode failed. First 200 chars: ' . substr($body, 0, 200));
			return null;
		}
	}

	if ($cacheMinutes > 0 && $data !== null) {
		set_transient($cacheKey, $data, $cacheMinutes * MINUTE_IN_SECONDS);
	}

	return $data;
}

function kk_calendar_extract_json_substring(string $body): string
{
	$body = trim($body);

	$posArr = strpos($body, '[');
	$posObj = strpos($body, '{');

	if ($posArr === false && $posObj === false) {
		return $body; // nothing we can do
	}

	$starts = [];
	if ($posArr !== false) $starts[] = $posArr;
	if ($posObj !== false) $starts[] = $posObj;

	$start = min($starts);
	$s = substr($body, $start);

	// Trim trailing junk after the last closing bracket/brace
	if (isset($s[0]) && $s[0] === '[') {
		$end = strrpos($s, ']');
		if ($end !== false) $s = substr($s, 0, $end + 1);
	} else {
		$end = strrpos($s, '}');
		if ($end !== false) $s = substr($s, 0, $end + 1);
	}

	return trim($s);
}

function kk_calendar_prepare_events(array $data, DateTimeZone $fallbackTz): array
{
	$events = [];

	foreach ($data as $entry) {
		if (!is_array($entry)) {
			continue;
		}

		// Ignore RRULE "master" events (no expansion)
		if (isset($entry['RRULE']) && !isset($entry['RECURRENCE-ID'])) {
			continue;
		}

		$start = kk_calendar_parse_entry_dt($entry['DTSTART'] ?? null, $fallbackTz);
		$end   = kk_calendar_parse_entry_dt($entry['DTEND'] ?? null, $fallbackTz);

		if (!$start || !$end) {
			continue;
		}

		$allDay = ($start->format('H:i:s') === '00:00:00' && $end->format('H:i:s') === '00:00:00');

		// All-day events: DTEND is exclusive -> subtract 1 day
		$endEffective = $end;
		if ($allDay) {
			$endEffective = $end->modify('-1 day');
		}

		$entry['_start'] = $start;
		$entry['_end'] = $end;
		$entry['_end_effective'] = $endEffective;
		$entry['_all_day'] = $allDay;

		$events[] = $entry;
	}

	return $events;
}

function kk_calendar_parse_entry_dt(mixed $dt, DateTimeZone $fallbackTz): ?DateTimeImmutable
{
	if (!is_array($dt) || !isset($dt['date'])) {
		return null;
	}

	$dateStr = (string)$dt['date'];
	$tzStr   = isset($dt['timezone']) ? (string)$dt['timezone'] : '';

	$tz = $fallbackTz;
	if ($tzStr !== '') {
		try {
			$tz = new DateTimeZone($tzStr);
		} catch (Exception $e) {
			$tz = $fallbackTz;
		}
	}

	$fmt = str_contains($dateStr, '.') ? 'Y-m-d H:i:s.u' : 'Y-m-d H:i:s';
	$d = DateTimeImmutable::createFromFormat($fmt, $dateStr, $tz);
	if (!$d) {
		return null;
	}

	return $d->setTimezone(wp_timezone());
}

function kk_calendar_parse_ymd(string $ymd, DateTimeZone $tz): ?DateTimeImmutable
{
	$ymd = trim($ymd);
	if ($ymd === '') {
		return null;
	}
	$d = DateTimeImmutable::createFromFormat('Y-m-d', $ymd, $tz);
	return $d ?: null;
}

function kk_calendar_format_when(
	DateTimeImmutable $start,
	DateTimeImmutable $endEffective,
	bool $allDay,
	string $dateFormat,
	string $timeFormat,
	DateTimeZone $tz
): string {
	$sameDay = ($start->format('Y-m-d') === $endEffective->format('Y-m-d'));
	$dateFmt = 'D, ' . $dateFormat; // use 'l, ' for long weekday

	$d1 = esc_html(wp_date($dateFmt, $start->getTimestamp(), $tz));
	$d2 = esc_html(wp_date($dateFmt, $endEffective->getTimestamp(), $tz));

	// Helper spans
	$line = static fn(string $s): string => '<span class="kk-when__line">' . $s . '</span>';
	$sep  = static fn(): string => '<span class="kk-when__sep">-</span>';

	if ($allDay) {
		// one date
		if ($sameDay) {
			return $line($d1);
		}
		// two dates
		return $line($d1) . $sep() . $line($d2);
	}

	$t1 = esc_html(wp_date($timeFormat, $start->getTimestamp(), $tz));
	$t2 = esc_html(wp_date($timeFormat, $endEffective->getTimestamp(), $tz));

	if ($sameDay) {
		// date + time
		return $line($d1) . $line($t1 . '–' . $t2);
	}

	// timed, multi-day: date+time on both sides
	return $line($d1 . ' ' . $t1) . $sep() . $line($d2 . ' ' . $t2);
}




function replace_links_with_urls(string $html): string
{
	if ($html === '') return '';

	libxml_use_internal_errors(true);

	$dom = new DOMDocument('1.0', 'UTF-8');

	// Force UTF-8 without mb_convert_encoding()
	$dom->loadHTML(
		'<?xml encoding="UTF-8"?>' . $html,
		LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
	);

	$links = $dom->getElementsByTagName('a');

	// NodeList is live -> iterate backwards
	for ($i = $links->length - 1; $i >= 0; $i--) {
		$a = $links->item($i);
		$href = trim((string) $a->getAttribute('href'));

		$replacement = $href !== '' ? $href : $a->textContent;
		$textNode = $dom->createTextNode($replacement);

		$a->parentNode->replaceChild($textNode, $a);
	}

	$out = $dom->saveHTML();

	$out = preg_replace('~^\s*(?:<\?xml[^>]+\?>|<!--\?xml[^>]+-->)\s*~', '', $out) ?? $out;

	return $out;

}

function unwrap_google_calendar_urls(string $text): string
{
	return preg_replace_callback('~https?://www\.google\.com/url\?[^\s<>"\']+~i', static function ($m) {
		$full = $m[0];

		// Parse query string
		$parts = parse_url($full);
		if (!isset($parts['query'])) {
			return $full;
		}

		parse_str($parts['query'], $q);
		if (empty($q['q'])) {
			return $full;
		}

		// q is usually already decoded by parse_str, but decode once more safely
		$real = urldecode((string)$q['q']);

		// basic sanity: must start with http(s)
		if (!preg_match('~^https?://~i', $real)) {
			return $full;
		}

		return $real;
	}, $text) ?? $text;
}


function kk_calendar_render_description(string $desc, string $linkText = '» Details'): string
{
	$desc = replace_links_with_urls($desc);
	$desc = unwrap_google_calendar_urls($desc);
	$out = preg_replace_callback('~https?://[^\s]+~u', function ($m) use ( $linkText ) {
		$url = $m[0];
		$trail = '';

		// common: URL followed by punctuation in plain text
		if (preg_match('~^(.*?)([)\].,;:!?]+)$~', $url, $p)) {
			$url = $p[1];
			$trail = $p[2];
		}

		$href = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
		return '<a href="' . $href . '">' . $linkText . '</a>' . $trail;
	}, $desc);


	return $out ?? $desc;
}
