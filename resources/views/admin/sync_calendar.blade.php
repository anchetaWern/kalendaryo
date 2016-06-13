@extends('layouts.admin')

@section('content')
<form method="POST">
	<input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
	<p>
		<label for="calendar_id">Calendar</label>
		<select name="calendar_id" id="calendar_id">
			@foreach($calendars as $cal)
			<option value="{{ $cal->id }}">{{ $cal->title }}</option>
			@endforeach
		</select>
	</p>
	<button>Sync Calendar</button>
</form>
@stop