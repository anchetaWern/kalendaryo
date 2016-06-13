<?php
namespace App\Http\Controllers;

use App\Googl;
use App\User;
use App\Calendar;
use Illuminate\Http\Request;

class HomeController extends Controller
{
   public function index()
   {
        return view('login');
   }

   public function login(Googl $googl, User $user, Request $request)
   {
		$client = $googl->client();
		if ($request->has('code')) {

			$client->authenticate($request->get('code'));
			$token = $client->getAccessToken();

			$plus = new \Google_Service_Plus($client);

			$google_user = $plus->people->get('me');

			$id = $google_user['id'];

			$email = $google_user['emails'][0]['value'];
			$first_name = $google_user['name']['givenName'];
			$last_name = $google_user['name']['familyName'];

			$has_user = $user->where('email', '=', $email)->first();

			if (!$has_user) {
				//not yet registered
				$user->email = $email;
				$user->first_name = $first_name;
				$user->last_name = $last_name;
				$user->token = json_encode($token);
				$user->save();
				$user_id = $user->id;

				//create primary calendar
				$calendar = new Calendar;
				$calendar->user_id = $user_id;
				$calendar->title = 'Primary Calendar';
				$calendar->calendar_id = 'primary';
				$calendar->sync_token = '';
				$calendar->save();
			} else {
				$user_id = $has_user->id;
			}

			session([
				'user' => [
					'id' => $user_id,
					'email' => $email,
					'first_name' => $first_name,
					'last_name' => $last_name,
					'token' => $token
				]
			]);

			return redirect('/dashboard')
				->with('message', ['type' => 'success', 'text' => 'You are now logged in.']);

		} else {
			$auth_url = $client->createAuthUrl();
		    return redirect($auth_url);
		}
   }
}
