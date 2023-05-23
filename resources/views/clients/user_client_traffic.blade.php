<x-app-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')"/>
    <div class="mt-5">
        <form class="row g-3" method="POST" action="{{ route('traffic-client-user.store') }}">
            @csrf

            <div class="col-6">
                <label for="reseller" class="form-label">{{__('Resellers:')}}</label>
                <select id="reseller" name="reseller" class="form-select">
                    <option selected>{{__('Choose an reseller')}}</option>
                    @foreach($admins as $admin)
                        <option value="{{$admin->id}}">{{$admin->email}}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('resellers')" class="alert alert-danger"/>
            </div>
            <div class="col-6">
                <label for="user" class="form-label">{{__('Users:')}}</label>
                <select id="user" name="user" class="form-select">
                    <option selected>{{__('Choose a user')}}</option>
                    @foreach($remainingUsers as $remainingUser)
                        <option value="{{$remainingUser->id}}">{{$remainingUser->email}}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('user')" class="alert alert-danger"/>
            </div>


            <div class="col-12">
                <button class="btn btn-primary" style="color: #000 !important;">
                    {{ __('Submit') }}
                </button>
                <a class="btn btn-secondary" href="{{route('client.list')}}">
                    {{ __('Reject') }}
                </a>
            </div>
        </form>
    </div>


</x-app-layout>
