@extends ('app')
@section ('content')

<div class="flex flex-col flex-1">
    <role-management :roles='@json($roles)'></role-management>
</div>

@endsection
