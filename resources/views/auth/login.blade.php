@extends ('app')

@section ('content')

<div class="flex-1 flex item-center">

    <div class="flex-1"></div>
    <div class="flex-2 p-8 flex items-center justify-center">

        <div class="w-full max-w-md flex flex-col items-center">

            <h1>Login</h1>

            <div class="border border-gray-200 mt-8 p-4 w-full bg-white form">
                <form method="POST" action="/login">
                    @csrf

                    <div class="input">
                        <input type="email" autofocus name="email" value="" placeholder="Email" />
                    </div>
                    <div class="input">
                        <input type="password" name="password" value="" placeholder="Password" />
                    </div>

                    <button>Login</button>

                </form>
            </div>

        </div>

    </div>

</div>

@endsection
