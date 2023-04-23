<x-app-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')"/>
    <div class="container bg-white">
        <div class="row">
            <span class="col-12 p-5" style="overflow-wrap: break-word">{{$connection}}</span>
        </div>
    </div>


</x-app-layout>
