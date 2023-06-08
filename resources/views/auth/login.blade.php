<x-guest-layout>
    <style>
        html,
        body {
            height: 100%;
        }

        body {
            display: flex;
            align-items: center;
            padding-top: 40px;
            padding-bottom: 40px;
            background-color: #f5f5f5;
        }

        .form-signin {
            width: 100%;
            max-width: 330px;
            padding: 15px;
            margin: auto;
        }

        .form-signin .checkbox {
            font-weight: 400;
        }

        .form-signin .form-floating:focus-within {
            z-index: 2;
        }

        .form-signin input[type="email"] {
            margin-bottom: -1px;
            border-bottom-right-radius: 0;
            border-bottom-left-radius: 0;
        }

        .form-signin input[type="password"] {
            margin-bottom: 10px;
            border-top-left-radius: 0;
            border-top-right-radius: 0;
        }

        .bd-placeholder-img {
            font-size: 1.125rem;
            text-anchor: middle;
            -webkit-user-select: none;
            -moz-user-select: none;
            user-select: none;
        }

        @media (min-width: 768px) {
            .bd-placeholder-img-lg {
                font-size: 3.5rem;
            }
        }
    </style>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')"/>
    <div class="form-signin">
        <form class="g-3 mt-5" method="POST" action="{{ route('login') }}">
            @csrf
            <h1 class="h3 mb-3 fw-normal">{{__('Please sign in')}}</h1>
            <!-- Email Address -->

            <div class="form-floating">
                <input type="email" class="form-control" id="floatingInput" placeholder="name@example.com" name="email"
                       value="{{old('email')}}" required autofocus autocomplete="username">
                <label for="floatingInput">Email address</label>
                <x-input-error :messages="$errors->get('email')" class="alert alert-warning"/>
            </div>

            <!-- Password -->
            <div class="form-floating">
                <input type="password" class="form-control" id="floatingPassword" placeholder="Password" name="password"
                       required autocomplete="current-password">
                <label for="floatingPassword">{{__('Password')}}</label>
                <x-input-error :messages="$errors->get('password')" class="alert alert-warning"/>
            </div>
            <!-- Remember Me -->
            <div class="checkbox mb-3">
                <label>
                    <input name="remember" id="remember_me" type="checkbox">{{__('Remember me')}}
                </label>
            </div>
            <button class="w-100 btn btn-lg btn-primary" type="submit">{{ __('Log in') }}</button>
        </form>
    </div>
</x-guest-layout>
