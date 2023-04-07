<x-app-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-12" :status="session('status')"/>

    <table class="table table-success table-hover">
        <thead>
        <tr>
            <th>row</th>
            <th>domain</th>
            <th>members count</th>
            <th>Active traffic</th>
            <th>Expired traffic</th>
            <th>Total traffic</th>
        </tr>
        </thead>
        <tbody>
        @php
            $counter = 1;
        @endphp
        @foreach($inbounds as $inbound)
            <tr>
                <td>{{$counter}}</td>
                <td>{{$inbound['domain']}}</td>
                <td>{{$inbound['members']}}</td>
                <td>{{$inbound['activeTotalTraffic']}} GB</td>
                <td>{{$inbound['expiredTotalTraffic']}} GB</td>
                <td>{{$inbound['expiredTotalTraffic'] + $inbound['activeTotalTraffic']}} GB</td>
            </tr>
            @php
                $counter++;
            @endphp
        @endforeach
        </tbody>
    </table>
</x-app-layout>
