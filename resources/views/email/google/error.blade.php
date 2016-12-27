@extends('email.master')
@section('content')
An error occured while retrieving the information, with the following parameters:<br />
<ul>
	<li>LATITUDE: {{$lat}}</li>
	<li>LONGITUDE: {{$long}}</li>
	<li>URL: {{$api_url}}</li>
</ul>
<br />
@endsection