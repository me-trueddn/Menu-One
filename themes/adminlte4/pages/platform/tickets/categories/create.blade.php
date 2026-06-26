@extends('theme::layouts.app')

@section('title', __('menu.add'))
@section('page-title', __('menu.ticket_category_add'))

@section('content')
@include('theme::partials.platform.ticket-nav')

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('platform.ticket-categories.store') }}">
            @csrf
            @include('theme::partials.platform.ticket-category-form')
            <button class="btn btn-primary">{{ __('menu.save') }}</button>
            <a href="{{ route('platform.ticket-categories.index') }}" class="btn btn-secondary">{{ __('menu.cancel') }}</a>
        </form>
    </div>
</div>
@endsection
