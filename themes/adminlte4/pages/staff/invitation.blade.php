@extends('theme::layouts.app')

@section('title', __('menu.staff_invitation'))
@section('page-title', __('menu.staff_invitation'))

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">{{ __('menu.staff_invitation') }}</h5>
            </div>
            <div class="card-body">
                <p class="mb-2">{{ __('menu.staff_invitation_intro', ['cafe' => $invitation->tenant->name]) }}</p>
                <ul class="list-unstyled mb-4">
                    <li><strong>{{ __('menu.role') }}:</strong> {{ __('menu.role_'.$invitation->role) }}</li>
                    <li><strong>{{ __('menu.invited_by') }}:</strong> {{ $invitation->invitedBy?->name ?? '—' }}</li>
                    <li><strong>{{ __('menu.expires_at') }}:</strong> {{ $invitation->expires_at->timezone(config('app.timezone'))->format('d.m.Y H:i') }}</li>
                </ul>
                <p class="text-muted small">{{ __('menu.staff_invitation_confirm_hint') }}</p>
                <div class="d-flex flex-wrap gap-2">
                    <form method="POST" action="{{ route('staff.invitation.accept', $invitation->token) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary">{{ __('menu.accept_invitation') }}</button>
                    </form>
                    <form method="POST" action="{{ route('staff.invitation.decline', $invitation->token) }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary">{{ __('menu.decline_invitation') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
