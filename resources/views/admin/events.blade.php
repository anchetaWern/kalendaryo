@extends('layouts.admin')

@section('content')
@if(count($events) > 0)
<table>
	<thead>
		<tr>
			<th>Title</th>
			<th>Datetime Start</th>
			<th>Datetime End</th>
		</tr>
	</thead>
	<tbody>
		@foreach($events as $event)
		<tr>
			<td>{{ $event->title }}</td>
			<td>{{ $event->datetime_start }}</td>
			<td>{{ $event->datetime_end }}</td>
		</tr>
		@endforeach
	</tbody>
</table>
@endif
@stop