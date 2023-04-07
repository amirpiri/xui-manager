<x-app-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')"/>
    <form method="POST" action="{{ route('client.store') }}">
        @csrf
        <div class="container-fluid mb-3">
            <div class="row bg-white">
                <div class="input-group flex-nowrap">
                    <span class="input-group-text" id="addon-wrapping">@</span>
                    <input name="username" type="text" class="form-control" placeholder="Username" aria-label="Username"
                           aria-describedby="addon-wrapping">
                    <x-input-error :messages="$errors->get('username')" class="mt-2"/>
                </div>
                <select name="inbound" class="form-select" aria-label="Default select example">
                    <option selected>Choose an inbound</option>
                    @foreach($inbounds as $inbound)
                        <option value="{{$inbound->id}}">{{$inbound->remark}}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('inbound')" class="mt-2"/>
                <select name="user" class="form-select" aria-label="Default select example">
                    <option selected>Choose an inbound</option>
                    @foreach($users as $user)
                        <option value="{{$user->id}}">{{$user->email}}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('user')" class="mt-2"/>
                <hr/>
                <select name="total" class="form-select" aria-label="Default select example">
                    <option selected>Choose a traffic</option>
                    <option value="50">50GB</option>
                    <option value="100">100GB</option>
                    <option value="150">150GB</option>
                    <option value="200">200GB</option>
                </select>
                <x-input-error :messages="$errors->get('total')" class="mt-2"/>
            </div>
        </div>
        <x-primary-button class="ml-3" style="color: #000 !important;">
            {{ __('Submit') }}
        </x-primary-button>
        <a href="{{route('client.list')}}"
           style="color:#000 !important; border-radius: 0.375rem;padding: 10px 20px;background-color: rgb(229 231 235 / var(--tw-bg-opacity));">
            {{ __('Reject') }}
        </a>
    </form>


</x-app-layout>
