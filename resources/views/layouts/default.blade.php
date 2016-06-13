<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>{{ env('APP_TITLE') }}</title>
	<link rel="stylesheet" href="{{ url('assets/lib/picnic/picnic.min.css') }}">
	<link rel="stylesheet" href="{{ url('assets/lib/picnic/plugins.min.css') }}">

	<link rel="stylesheet" href="{{ url('assets/css/style.css') }}">
</head>
<body>
	<div class="container">
		<header>
			<h1>{{ env('APP_TITLE') }}</h1>
		</header>
		@include('partials.alert')
		@yield('content')
	</div>
</body>
</html>