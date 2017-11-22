<?php
/**
 * Created by PhpStorm.
 * User: matdave
 *   ___      ___       __  ___________  ________       __  ___      ___  _______
 * |"  \    /"  |     /""\("     _   ")|"      "\     /""\|"  \    /"  |/"     "|
 *  \   \  //   |    /    \)__/  \\__/ (.  ___  :)   /    \\   \  //  /(: ______)
 *  /\\  \/.    |   /' /\  \  \\_ /    |: \   ) ||  /' /\  \\\  \/. ./  \/    |
 * |: \.        |  //  __'  \ |.  |    (| (___\ || //  __'  \\.    //   // ___)_
 * |.  \    /:  | /   /  \\  \\:  |    |:       :)/   /  \\  \\\   /   (:      "|
 * |___|\__/|___|(___/    \___)\__|    (________/(___/    \___)\__/     \_______)
 *
 * Email: mat@matdave.com
 * Twitter: @matjones
 * Date: 11/20/17
 * Time: 10:56 AM
 * Project: gcalparser
 */

class gcalparser
{
    public $config = array();

    public $calendar = '';
    public $google_calendar_array = array();
// Get the timezone from the Google Calendar feed


    function __construct(modX &$modx, array $config = array())
    {
// You must register this application using the Google Developers Console in order to authenticate:
// https://console.developers.google.com/
        $this->key = 'AIzaSyDHBzRmRP0XlwKB-Pjvxa7E-2GVyYwSQw4';
        if(empty($this->key)){
            $this->modx->log(xPDO::LOG_LEVEL_ERROR,'[gcalparser] No API Key Given!');
            return null;
        }
// Set start date
        $this->today = date('c');
        $this->cacheOptions = array(xPDO::OPT_CACHE_KEY => 'presscenter');
// Set cutoff date
        $this->next_month= date('c', strtotime("+1 month", strtotime($this->today)));
        $this->modx =& $modx;
        $corePath = $this->modx->getOption('gcalparser.core_path', $config, $this->modx->getOption('core_path') . 'components/gcalparser/');
        $this->config = array_merge(array(
            'basePath' => $this->modx->getOption('base_path'),
            'corePath' => $corePath,
            'modelPath' => $corePath . 'model/',
            'pluginPath' => $corePath . 'elements/plugin/',
        ), $config);
        $this->modx->addPackage('gcalparser', $this->config['modelPath']);

    }

    public function getCalendarList($calendar) {
        $this->calendar = $calendar;
        $today = new DateTime($this->today);
        $this->json = $this->modx->cacheManager->get($calendar, $this->cacheOptions);
        if(empty($this->json)){
            $this->json_url = 'https://www.googleapis.com/calendar/v3/calendars/'.$this->calendar.'/events/?key='.$this->key.'&singleEvents=true&timeMin='.$today->format(DateTime::ATOM);
            $this->json_file = $this->fileGetContentsCurl($this->json_url);
            $this->json = $this->modx->fromJSON($this->json_file);
            $this->modx->cacheManager->set($calendar, $this->json, 3600, $this->cacheOptions);
        }
        $this->timezone = $this->json['timeZone'];

        if(empty($this->json['items'])) return;

        foreach($this->json['items'] as $event_entry)
        {

            $start = $event_entry['start']['dateTime'];
            $end = $event_entry['end']['dateTime'];
            $title = $event_entry['summary'];
            $description = $event_entry['description'];
            $location = $event_entry['location'];
            $event_url = $event_entry['htmlLink'];
            $allday = $event_entry['endTimeUnspecified'];
            if(empty($start)){
                continue;
            }
            $startDt = new DateTime($start);
            if($startDt < $today){
                continue;
            }
            if($event_entry['status'] == 'canceled'){
                continue;
            }
            $startDt->setTimeZone ( new DateTimezone ( $this->timezone ) );
            $startDate = $startDt->format ( 'l, F j, Y' );

            $endDt = new DateTime($end);
            $endDt->setTimeZone ( new DateTimezone ( $this->timezone ) );
            $endDate = $endDt->format ( 'l, F j, Y' );

            $startHour = $startDt->format ( 'g:i a' );
            $endHour = $endDt->format ( 'g:i a' );

            $new_event = array(
                'date_start'=>$start,
                'date_start_formatted' => $startDate,
                'start_hour' => $startHour,
                'date_end'=>$end,
                'date_end_formatted' => $endDate,
                'end_hour' => $endHour,
                'all_day' => $allday,
                'summary'=>$title,
                'calendar'=>$this->json['summary'],
                'location'=>stripslashes($location),
                'description'=>$this->detectURL($this->detectEmail(nl2br(stripslashes($description)))),
                'event_url'=>$event_url);
            array_push($this->google_calendar_array,$new_event);
        }
        return;
    }

    public function dateSort ($a,$b)
    {
        return strtotime($a['date_start']) - strtotime($b['date_start']);
    }

    public function detectEmail($str)
    {
        $mail_pattern = "/([A-z0-9\._-]+\@[A-z0-9_-]+\.)([A-z0-9\_\-\.]{1,}[A-z])/";
        $str = preg_replace($mail_pattern, '<a href="mailto:$1$2">$1$2</a>', $str);
        return $str;
    }

    public function detectURL($str)
    {
        $reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
        $str = preg_replace($reg_exUrl, '<a href="$0">$0</a>', $str);

        return $str;
    }

    public function getAllEvents($calendars = array(), $limit = 0, $offset = 0, $totalVar = 'total'){
        foreach($calendars as $c){
            $this->getCalendarList($c);
        }
        usort($this->google_calendar_array, array($this,'dateSort'));
        $this->modx->log(1,'google calendar total '. count ($this->google_calendar_array));
        $this->modx->setPlaceholder($totalVar, count ($this->google_calendar_array));
        if($limit > 0){
            $this->google_calendar_array = array_slice($this->google_calendar_array, $offset, $limit);
        }
        return $this->google_calendar_array;
    }

    public function explodeAndClean($array, $delimiter = ',', $keepZero = false) {
        $array = explode($delimiter, $array);            // Explode fields to array
        $array = array_map('trim', $array);       // Trim array's values
        $array = array_keys(array_flip($array));  // Remove duplicate fields

        if ($keepZero === false) {
            $array = array_filter($array);            // Remove empty values from array
        } else {
            $array = array_filter($array, function($value) { return $value !== ''; });
        }

        return $array;
    }
    public function fileGetContentsCurl($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
}