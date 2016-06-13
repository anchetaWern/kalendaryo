@extends('layouts.admin')

@section('jquery_datetimepicker_style')
<link rel="stylesheet" href="{{ url('assets/lib/jquery-datetimepicker/jquery.datetimepicker.min.css') }}">
@stop

@section('content')
<form method="POST">
	<input type="hidden" name="_token" value="{{{ csrf_token() }}}" />
	<p>
		<label for="title">Title</label>
		<input type="text" name="title" id="title" value="{{ old('title') }}">
	</p>
	<p>
		<label for="calendar_id">Calendar</label>
		<select name="calendar_id" id="calendar_id">
			@foreach($calendars as $cal)
			<option value="{{ $cal->calendar_id }}">{{ $cal->title }}</option>
			@endforeach
		</select>
	</p>
	<p>
		<label for="datetime_start">Datetime Start</label>
		<input type="text" name="datetime_start" id="datetime_start" class="datetimepicker" value="{{ old('datetime_start') }}">
	</p>
	<p>
		<label for="datetime_end">Datetime End</label>
		<input type="text" name="datetime_end" id="datetime_end" class="datetimepicker" value="{{ old('datetime_end') }}">
	</p>
	<div id="attendees">
		Attendees
		<div class="attendee-row">
			<input type="text" name="attendee_name[]" class="half-input name" placeholder="Name">
			<input type="text" name="attendee_email[]" class="half-input email" placeholder="Email">
		</div>
	</div>
	<button>Create Event</button>
</form>
@stop


@section('attendee_template')
<script id="attendee-template" type="text/x-handlebars-template">
	<div class="attendee-row">
		<input type="text" name="attendee_name[]" class="half-input name" placeholder="Name">
		<input type="text" name="attendee_email[]" class="half-input email" placeholder="Email">
	</div>
</script>
@stop

@section('jquery_script')
<script src="{{ url('assets/lib/jquery.min.js') }}"></script>
@stop

@section('handlebars_script')
<script src="{{ url('assets/lib/handlebars.min.js') }}"></script>
@stop

@section('jquery_datetimepicker_script')
<script src="{{ url('assets/lib/jquery-datetimepicker/jquery.datetimepicker.min.js') }}"></script>
@stop

@section('create_event_script')
<script src="{{ url('assets/js/create_event.js') }}"></script>
@stop