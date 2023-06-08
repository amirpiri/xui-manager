<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link active" aria-current="page" href="{{ route('dashboard') }}">
                    <span data-feather="home"></span>
                    {{ __('Dashboard') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" aria-current="page" href="{{route('client.list')}}">
                    <span data-feather="home"></span>
                    {{ __('xui.client_list_title') }}
                </a>
            </li>
            <li class="nav-item">
                <a href="{{route('inbounds')}}" class="nav-link active" aria-current="page">
                    <span data-feather="home"></span>
                    {{ __('xui.inbound_title') }}
                </a>
            </li>
            @if(auth()->user()->role === \App\Enums\UserRoleEnum::ADMIN->value)
                <li class="nav-item">
                    <span data-feather="home"></span>
                    <a href="{{route('client.create')}}" class="nav-link active" aria-current="page">
                        {{ __('xui.create') }}
                    </a>

                </li>
                <li class="nav-item">
                    <span data-feather="home"></span>
                    <a href="{{route('traffic-client-user.show')}}" class="nav-link active" aria-current="page">
                        {{ __('Assign clients to users') }}
                    </a>

                </li>
            @endif
            <li class="nav-item">
                <a href="{{route('client.excluded-clients')}}" class="nav-link active" aria-current="page">
                    <span data-feather="home"></span>
                    {{ __('Disable User') }}
                </a>
            </li>
        </ul>
    </div>
</nav>
