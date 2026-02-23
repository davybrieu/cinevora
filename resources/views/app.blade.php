<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">

	<link rel="canonical" href="{{ url()->current() }}">
	<meta name="csrf-token" content="{!! csrf_token() !!}">

	@vite(['resources/js/app.js', 'resources/css/app.css'])

	@inertiaHead

	@routes
</head>

<body>
	@inertia
</body>

</html>
