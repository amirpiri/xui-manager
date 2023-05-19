<x-app-layout>
@php
    $remaining = $client->total - ($client->up + $client->down);
    $convertToGB = (1024 * 1024 * 1024);
    $expireDateTime = \Carbon\Carbon::createFromTimestamp(($client->expiry_time / 1000));
@endphp
<!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')"/>
    <div class="mt-5">
        <form class="row g-3" method="POST"
              action="{{ route('client.renew-client.update',['clientId' => $client->id]) }}">
            @method('PUT')
            @csrf

            <div class="col-6">
                {{__('Total traffic:')}}
                <span class="fw-bold">{{number_format($client->total / $convertToGB,2)}}GB</span>
            </div>
            <div class="col-6">
                {{__('Remaining:')}} <span class="fw-bold">{{$remaining / $convertToGB}} GB</span>
            </div>
            <div class="col-6">
                {{__('Email:')}} <span class="fw-bold">{{$client->email}}</span>
            </div>
            <div class="col-6">
                {{__('Expire:')}} <span class="fw-bold">{{$expireDateTime->toDateTimeString()}}</span>
            </div>
            <div class="col-6">
                {{__('Next expire time:')}} <span
                    class="fw-bold">{{\Carbon\Carbon::today()->addMonth()->toDateTimeString()}}</span>
            </div>
            <div class="col-6">
                {{__('Next expire time jalali:')}} <span
                    class="fw-bold">{{Morilog\Jalali\Jalalian::forge((\Carbon\Carbon::today()->addMonth()->toDateTimeString()))->toDateTimeString()}}</span>
            </div>
            <div class="col-12">
                <label for="traffic" class="form-label">{{__('Traffic:')}}</label>
                <select id="traffic" name="traffic" class="form-select form-select-lg"
                        aria-label="Default select example">
                    <option selected>{{__('Choose a traffic')}}</option>
                    <option value="50">50GB</option>
                    <option value="100">100GB</option>
                    <option value="150">150GB</option>
                    <option value="200">200GB</option>
                </select>
                <x-input-error :messages="$errors->get('traffic')" class="mt-2"/>
            </div>
            <div class="col-12">
                <button class="btn btn-primary">
                    {{ __('Submit') }}
                </button>
                <a class="btn btn-secondary" href="{{route('client.list')}}">{{ __('Reject') }}</a>
            </div>
        </form>
    </div>


</x-app-layout>
