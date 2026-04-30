@extends('public.layouts.public')

@section('content')

<div class="
    grid
    @if(($settings->public_theme ?? 'default') === 'elegant')
        elegant-layout
    @endif
">

    <div class="card">
        <h2 class="section-title">{{ __('messages.book_appointment') }}</h2>

        <form method="POST" action="/b/{{ $business->slug }}/book">
            @csrf

            <div class="form-group">
                <label>{{ __('messages.select_service') }}</label>

                @if(($settings->group_services_on_public_page ?? false) && isset($serviceGroups) && $serviceGroups->count())

                    <div class="service-group-tabs">
                        @foreach($serviceGroups as $group)
                            @if($group->services->count())
                                <button type="button"
                                        class="service-group-tab"
                                        data-group-id="{{ $group->id }}">
                                    {{ $group->name }}
                                </button>
                            @endif
                        @endforeach
                    </div>

                    <select name="service_id" id="service_id" required>
                        <option value="">{{ __('messages.choose_service') }}</option>

                        @foreach($serviceGroups as $group)
                            @foreach($group->services as $service)
                                <option value="{{ $service->id }}"
                                        data-group-id="{{ $group->id }}">
                                    {{ $service->name }} - {{ $service->duration }} {{ __('messages.minutes') }}
                                </option>
                            @endforeach
                        @endforeach
                    </select>

                @else

                    <select name="service_id" id="service_id" required>
                        <option value="">{{ __('messages.choose_service') }}</option>

                        @foreach($services as $service)
                            <option value="{{ $service->id }}">
                                {{ $service->name }} - {{ $service->duration }} {{ __('messages.minutes') }}
                            </option>
                        @endforeach
                    </select>

                @endif
            </div>

            <div class="form-group">
                <label>{{ __('messages.select_date') }}</label>
                <input
                    type="date"
                    name="booking_date"
                    id="booking_date"
                    min="{{ now()->toDateString() }}"
                    required
                >
            </div>

            <div class="form-group">
                <label>{{ __('messages.available_time_slots') }}</label>

                <div id="slots" class="slots"></div>

                <input type="hidden" name="start_time" id="start_time">
            </div>

            <div class="form-group">
                <label>{{ __('messages.your_name') }}</label>
                <input type="text" name="name" required>
            </div>

            <div class="form-group">
                <label>{{ __('messages.phone_number') }}</label>
                <input type="text" name="phone" required>
            </div>

            <button type="submit">{{ __('messages.confirm_booking') }}</button>
        </form>
    </div>

    <div class="card">
        <h2 class="section-title">{{ __('messages.available_services') }}</h2>

        @if(($settings->group_services_on_public_page ?? false) && isset($serviceGroups) && $serviceGroups->count())

            @foreach($serviceGroups as $group)
                @if($group->services->count())
                    <h4>{{ $group->name }}</h4>

                    @foreach($group->services as $service)
                        <div class="service-box">
                            <strong>{{ $service->name }}</strong>

                            <div class="muted">
                                {{ $service->duration }} {{ __('messages.minutes') }}
                            </div>

                            @if(!is_null($service->price))
                                <div class="muted">
                                    {{ __('messages.price') }}: ${{ number_format($service->price,2) }}
                                </div>
                            @endif
                        </div>
                    @endforeach

                @endif
            @endforeach

        @else

            @foreach($services as $service)
                <div class="service-box">
                    <strong>{{ $service->name }}</strong>

                    <div class="muted">
                        {{ $service->duration }} {{ __('messages.minutes') }}
                    </div>

                    @if(!is_null($service->price))
                        <div class="muted">
                            {{ __('messages.price') }}: ${{ number_format($service->price,2) }}
                        </div>
                    @endif
                </div>
            @endforeach

        @endif

    </div>

</div>

@endsection