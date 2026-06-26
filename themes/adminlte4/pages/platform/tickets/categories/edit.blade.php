@extends('theme::layouts.app')

@section('title', __('menu.edit'))
@section('page-title', __('menu.ticket_category_edit'))

@section('content')
@include('theme::partials.platform.ticket-nav')

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('platform.ticket-categories.update', $category) }}">
            @csrf @method('PUT')
            @include('theme::partials.platform.ticket-category-form', ['category' => $category])
            <button class="btn btn-primary">{{ __('menu.update') }}</button>
            <a href="{{ route('platform.ticket-categories.index') }}" class="btn btn-secondary">{{ __('menu.cancel') }}</a>
        </form>
    </div>
</div>
@endsection
