<?php
namespace App;

class Googl {
	
	public function client(){

		$client = new \Google_Client();
		$client->setClientId(env('GOOGLE_CLIENT_ID'));
		$client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
		$client->setRedirectUri(env('GOOGLE_REDIRECT_URL'));
		$client->setScopes(explode(',', env('GOOGLE_SCOPES')));
		$client->setApprovalPrompt(env('GOOGLE_APPROVAL_PROMPT'));
		$client->setAccessType(env('GOOGLE_ACCESS_TYPE'));

		return $client;
	}

}