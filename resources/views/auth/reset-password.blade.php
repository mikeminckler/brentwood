@extends ('app')

@section ('content')

<form-reset-password :user='@json($user)' ></form-reset-password>

@endsection
