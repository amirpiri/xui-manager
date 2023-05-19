<x-app-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-12" :status="session('status')"/>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
            <tr>
                <th>{{__('xui.id')}}</th>
                <th>{{__('xui.email')}}</th>
                <th>{{__('xui.domain')}}</th>
                <th>{{__('xui.downloaded')}}</th>
                <th>{{__('xui.uploaded')}}</th>
                <th>{{__('xui.total')}}</th>
                <th>{{__('xui.remaining')}}</th>
                <th>{{__('xui.status')}}</th>
                <th>{{__('xui.jalali_expire_date')}}</th>
                <th>{{__('xui.expire_date')}}</th>
                <th>{{__('xui.remaining_expire_date')}}</th>
                <th>{{__('xui.actions')}}</th>
            </tr>
            </thead>
            <tbody>
            @foreach($clients as $client)
                <tr>
                    <td>{{$client->id}}</td>
                    <td>{{$client->email}}</td>
                    <td>{{$client->remark}}</td>
                    <td>{{number_format(($client->down / 1024 / 1024 /1024),2)}} GB</td>
                    <td>{{number_format(($client->up/1024/1024/1024),2)}} GB</td>
                    <td>{{number_format(($client->total/1024/1024/1024),2)}} GB</td>
                    <td>{{number_format(($client->remaining/1024/1024/1024),2)}} GB</td>
                    <td>{{$client->enable}}</td>
                    <td>{{ Morilog\Jalali\Jalalian::forge(($client->expire_date / 1000))->toDateTimeString()}}</td>
                    <td>{{\Carbon\Carbon::createFromTimestamp(($client->expire_date / 1000))->toDateTimeString()}} </td>
                    <td>{{\Carbon\Carbon::createFromTimestamp(($client->expire_date / 1000))->diffForHumans()}} </td>
                    <td>
                        <a href="{{route('client.renew',['clientId' => $client->id])}}" title="{{__('xui.renew')}}">
                            <i class="fa-sharp fa-solid fa-rotate"></i>
                        </a>
                        <a href="{{route('client.get-client-connection',['clientId' => $client->id])}}"
                           title="{{__('xui.generate')}}">
                            <i class="fa-sharp fa-solid fa-download"></i>
                        </a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{$clients->links('vendor.pagination.bootstrap-5')}}
    </div>

</x-app-layout>
