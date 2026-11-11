@extends('layouts.sidebar', ['area' => 'user'])

{{-- Apps that identify users by a username or email instead swap the label here --}}
@section('topbar-end')
    <x-topbar.locale route="user.profile.locale" />

    <x-topbar.dropdown icon="fa-circle-user" :label="Auth::user()->name">
        <x-topbar.link route="user.profile.user.show" icon="fa-user-gear">{{ __('Account Profile') }}</x-topbar.link>
        <x-topbar.link route="user.profile.password.show" icon="fa-key">{{ __('Change Password') }}</x-topbar.link>
        <x-topbar.divider />
        <x-topbar.logout route="user.logout" />
    </x-topbar.dropdown>
@endsection

@section('sidebar-menu')
    <x-sidebar.link route="user.main" :active="['user.main', 'user.issues.*']" icon="fa-gauge-high">{{ __('Dashboard') }}</x-sidebar.link>
    <x-sidebar.link route="user.my_issues" icon="fa-user-check">{{ __('My Issues') }}</x-sidebar.link>
    <x-sidebar.link route="user.status_guide" icon="fa-circle-question">{{ __('Status Guide') }}</x-sidebar.link>
@endsection
