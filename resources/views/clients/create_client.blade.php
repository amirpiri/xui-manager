<x-app-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')"/>
    <div class="mt-5">
        <form class="row g-3" method="POST" action="{{ route('client.store') }}">
            @csrf
            <div class="col-6">
                <label class="form-label" for="username">{{__('Username:')}}</label>
                <input id="username" name="username" type="text" class="form-control" placeholder="Username">
                <x-input-error :messages="$errors->get('username')" class="alert alert-danger"/>
            </div>
            <div class="col-6">
                <label for="inbounds" class="form-label">{{__('Inbounds:')}}</label>
                <select id="inbounds" name="inbound" class="form-select">
                    <option selected>{{__('Choose an inbound')}}</option>
                    @foreach($inbounds as $inbound)
                        <option value="{{$inbound->id}}">{{$inbound->remark}}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('inbound')" class="alert alert-danger"/>
            </div>
            <div class="col-6">
                <label for="user" class="form-label">{{__('User:')}}</label>
                <select id="user" name="user" class="form-select">
                    <option selected>{{__('Choose a user')}}</option>
                    @foreach($users as $user)
                        <option value="{{$user->id}}">{{$user->email}}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('user')" class="alert alert-danger"/>
            </div>

            <div class="col-6">
                <label for="total" class="form-label">{{__('Total:')}}</label>
                <select id="total" name="total" class="form-select">
                    <option selected>Choose a traffic</option>
                    <option value="50">50GB</option>
                    <option value="100">100GB</option>
                    <option value="150">150GB</option>
                    <option value="200">200GB</option>
                </select>
                <x-input-error :messages="$errors->get('total')" class="alert alert-danger"/>
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
