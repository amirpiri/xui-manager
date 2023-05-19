<x-app-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')"/>
    <div class="mt-5">
        <form class="row g-3" method="POST"
              action="{{ route('client.transfer-client.store',['clientId' => $clientId]) }}">
            @csrf


            <input type="hidden" name="uuid" value="{{$uuid}}">
            <div class="col-12">
                <label for="inbound" class="form-label">{{__('Inbound:')}}</label>
                <select id="inbound" name="inbound" class="form-select form-select-lg"
                        aria-label="Default select example">
                    <option selected>{{__('Choose an inbound')}}</option>
                    @foreach($inbounds as $inbound)
                        <option value="{{$inbound->id}}">{{$inbound->remark}}</option>
                    @endforeach

                </select>
                <x-input-error :messages="$errors->get('inbound')" class="mt-2"/>
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
