@extends('layouts.profile')

@section('profile_content')
    <div class="mb-4">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
            {{ __('Hồ sơ cá nhân') }}
        </h2>
    </div>

    <div class="bg-white shadow-sm rounded-lg border-l-4" style="border-left-color: #990000;">
        <div class="p-6 sm:p-8">
            @include('profile.partials.update-profile-information-form')
        </div>
    </div>
@endsection
