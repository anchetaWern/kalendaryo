<?php
namespace App\Http\Controllers;

use App\Googl;
use App\User;
use App\Calendar;
use App\Event;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminController extends Controller
{
   private $client;

   public function __construct(Googl $googl)
   {
   		$this->client = $googl->client();
		$this->client->setAccessToken(session('user.token'));
   }


   public function index(Request $request)
   {
        return view('admin.dashboard');
   }


   public function createCalendar(Request $request)
   {
   		return view('admin.create_calendar');
   }


   public function doCreateCalendar(Request $request, Calendar $calendar)
   {
		$this->validate($request, [
			'title' => 'required|min:4'
		]);

		$title = $request->input('title');
		$timezone = env('APP_TIMEZONE');

		$cal = new \Google_Service_Calendar($this->client);

		$google_calendar = new \Google_Service_Calendar_Calendar($this->client);
		$google_calendar->setSummary($title);
		$google_calendar->setTimeZone($timezone);

		$created_calendar = $cal->calendars->insert($google_calendar);

		$calendar_id = $created_calendar->getId();

		$calendar->user_id = session('user.id');
		$calendar->title = $title;
		$calendar->calendar_id = $calendar_id;
		$calendar->save();

		return redirect('/calendar/create')
			->with('message', [
				'type' => 'success', 'text' => 'Calendar was created!'
			]);
   }


   public function createEvent(Calendar $calendar, Request $request)
   {
   		$user_id = session('user.id');
   		$calendars = $calendar
   			->where('user_id', '=', $user_id)->get();
   		$page_data = [
   			'calendars' => $calendars
   		];
   	 	return view('admin.create_event', $page_data);
   }


   public function doCreateEvent(Event $evt, Request $request)
   {
		$this->validate($request, [
			'title' => 'required',
			'calendar_id' => 'required',
			'datetime_start' => 'required|date',
			'datetime_end' => 'required|date'
		]);

		$title = $request->input('title');
		$calendar_id = $request->input('calendar_id');
		$start = $request->input('datetime_start');
		$end = $request->input('datetime_end');

		$start_datetime = Carbon::createFromFormat('Y/m/d H:i', $start);
		$end_datetime = Carbon::createFromFormat('Y/m/d H:i', $end);

		$cal = new \Google_Service_Calendar($this->client);
		$event = new \Google_Service_Calendar_Event();
		$event->setSummary($title);

		$start = new \Google_Service_Calendar_EventDateTime();
		$start->setDateTime($start_datetime->toAtomString());
		$event->setStart($start);
		$end = new \Google_Service_Calendar_EventDateTime();
		$end->setDateTime($end_datetime->toAtomString());
		$event->setEnd($end);

		//attendee
		if ($request->has('attendee_name')) {
			$attendees = [];
			$attendee_names = $request->input('attendee_name');
			$attendee_emails = $request->input('attendee_email');

			foreach ($attendee_names as $index => $attendee_name) {
				$attendee_email = $attendee_emails[$index];
				if (!empty($attendee_name) && !empty($attendee_email)) {
					$attendee = new \Google_Service_Calendar_EventAttendee();
					$attendee->setEmail($attendee_email);
					$attendee->setDisplayName($attendee_name);
					$attendees[] = $attendee;
				}
			}

			$event->attendees = $attendees;
		}

		$created_event = $cal->events->insert($calendar_id, $event);

		$evt->title = $title;
		$evt->calendar_id = $calendar_id;
		$evt->event_id = $created_event->id;
		$evt->datetime_start = $start_datetime->toDateTimeString();
		$evt->datetime_end = $end_datetime->toDateTimeString();
		$evt->save();

		return redirect('/event/create')
					->with('message', [
						'type' => 'success',
						'text' => 'Event was created!'
					]);
   }


   public function syncCalendar(Calendar $calendar)
   {
   		$user_id = session('user.id');
   		$calendars = $calendar->where('user_id', '=', $user_id)
   			->get();

   		$page_data = [
   			'calendars' => $calendars
   		];
   		return view('admin.sync_calendar', $page_data);
   }


   public function doSyncCalendar(Request $request)
   {
   		$this->validate($request, [
   			'calendar_id' => 'required'
   		]);

		$user_id = session('user.id');
		$calendar_id = $request->input('calendar_id');

		$base_timezone = env('APP_TIMEZONE');

		$calendar = Calendar::find($calendar_id);
		$sync_token = $calendar->sync_token;
		$g_calendar_id = $calendar->calendar_id;

		$g_cal = new \Google_Service_Calendar($this->client);

		$g_calendar = $g_cal->calendars->get($g_calendar_id);
		$calendar_timezone = $g_calendar->getTimeZone();

		$events = Event::where('id', '=', $calendar_id)
			->lists('event_id')
			->toArray();

		$params = [
			'showDeleted' => true,
			'timeMin' => Carbon::now()
				->setTimezone($calendar_timezone)
				->toAtomString()
		];

		if (!empty($sync_token)) {
		    $params = [
		    	'syncToken' => $sync_token
		    ];
		}

		$googlecalendar_events = $g_cal->events->listEvents($g_calendar_id, $params);


		while (true) {

			foreach ($googlecalendar_events->getItems() as $g_event) {

				$g_event_id = $g_event->id;
				$g_event_title = $g_event->getSummary();
				$g_status = $g_event->status;

				if ($g_status != 'cancelled') {

					$g_datetime_start = Carbon::parse($g_event->getStart()->getDateTime())
						->tz($calendar_timezone)
						->setTimezone($base_timezone)
						->format('Y-m-d H:i:s');

	                $g_datetime_end = Carbon::parse($g_event->getEnd()->getDateTime())
		                ->tz($calendar_timezone)
		                ->setTimezone($base_timezone)
		                ->format('Y-m-d H:i:s');

					//check if event id is already in the events table
					if (in_array($g_event_id, $events)) {
						//update event
						$event = Event::where('event_id', '=', $g_event_id)->first();
						$event->title = $g_event_title;
						$event->calendar_id = $g_calendar_id;
						$event->event_id = $g_event_id;
						$event->datetime_start = $g_datetime_start;
						$event->datetime_end = $g_datetime_end;
						$event->save();
					} else {
						//add event
						$event = new Event;
						$event->title = $g_event_title;
						$event->calendar_id = $g_calendar_id;
						$event->event_id = $g_event_id;
						$event->datetime_start = $g_datetime_start;
						$event->datetime_end = $g_datetime_end;
						$event->save();
					}

				} else {
					//delete event
					if (in_array($g_event_id, $events)) {
						Event::where('event_id', '=', $g_event_id)->delete();
					}
				}

			}

			$page_token = $googlecalendar_events->getNextPageToken();
			if ($page_token) {
			    $params['pageToken'] = $page_token;
			    $googlecalendar_events = $g_cal->events->listEvents('primary', $params);
			} else {
			    $next_synctoken = str_replace('=ok', '', $googlecalendar_events->getNextSyncToken());

			    //update next sync token
				$calendar = Calendar::find($calendar_id);
				$calendar->sync_token = $next_synctoken;
				$calendar->save();

			    break;
			}

		}

        return redirect('/calendar/sync')
        	->with('message',
        		[
        			'type' => 'success',
        			'text' => 'Calendar was synced.'
        		]);

   }


   public function listEvents()
   {
   		$user_id = session('user.id');
   		$calendar_ids = Calendar::where('user_id', '=', $user_id)
   			->lists('calendar_id')
   			->toArray();

   		$events = Event::whereIn('calendar_id', $calendar_ids)->get();

   		$page_data = [
   			'events' => $events
   		];

   		return view('admin.events', $page_data);
   }


   public function logout(Request $request)
   {
   		$request->session()->flush();
   		return redirect('/')
   			->with('message', ['type' => 'success', 'text' => 'You are now logged out']);
   }

}
