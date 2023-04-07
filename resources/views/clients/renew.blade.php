<x-app-layout>
@php
    $remaining = ($client->up + $client->down) / $client->total;
    $convertToGB = (1024 * 1024 * 1024);
    $expireDateTime = \Carbon\Carbon::createFromTimestamp(($client->expiry_time / 1000));
@endphp
<!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')"/>
    <form method="POST" action="{{ route('client.renew-client.update',['clientId' => $client->id]) }}">
        @method('PUT')
        @csrf
        <div class="container-fluid mb-3">
            <div class="row bg-white">
                <p>
                    {{__('Total traffic:')}} {{number_format($client->total / $convertToGB,2)}}GB
                </p>
                <p>
                    {{__('Remaining:')}} {{$remaining / $convertToGB}} GB
                </p>
                <p>
                    {{__('Email:')}} {{$client->email}}
                </p>
                <p>
                    {{__('Expire:')}} {{$expireDateTime->toDateTimeString()}}
                </p>
                <p>
                    {{__('Next expire time:')}} {{\Carbon\Carbon::today()->addMonth()->toDateTimeString()}}
                </p>

                <select name="traffic" class="form-select" aria-label="Default select example">
                    <option selected>Choose a traffic</option>
                    <option value="50">50GB</option>
                    <option value="100">100GB</option>
                    <option value="150">150GB</option>
                    <option value="200">200GB</option>
                </select>
                <x-input-error :messages="$errors->get('traffic')" class="mt-2" />
            </div>
        </div>
        <x-primary-button class="ml-3" style="color: #000 !important;">
            {{ __('Submit') }}
        </x-primary-button>
        <a href="{{route('client.list')}}" style="color:#000 !important; border-radius: 0.375rem;padding: 10px 20px;background-color: rgb(229 231 235 / var(--tw-bg-opacity));">
            {{ __('Reject') }}
        </a>
    </form>


</x-app-layout>
