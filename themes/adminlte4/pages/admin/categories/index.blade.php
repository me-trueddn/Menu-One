@extends('theme::layouts.app')
@section('title', __('menu.categories'))
@section('page-title', __('menu.categories'))
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between"><h5 class="mb-0">{{ __('menu.categories') }}</h5><a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm">{{ __('menu.add') }}</a></div>
    <div class="card-body table-responsive p-0">
        <table class="table mb-0"><thead><tr><th>{{ __('menu.name') }}</th><th>{{ __('menu.sort_order') }}</th><th>{{ __('menu.category_image') }}</th><th class="text-end">{{ __('menu.actions') }}</th></tr></thead>
        <tbody>@foreach($categories as $cat)<tr><td>{{ $cat->name }}</td><td>{{ $cat->sort_order }}</td>
        <td>@if($cat->imageUrl())<img src="{{ $cat->imageUrl() }}" alt="" style="height:36px;width:36px;object-fit:cover;border-radius:.25rem">@else—@endif</td>
        <td class="text-end"><a href="{{ route('admin.categories.edit', $cat) }}" class="btn btn-sm btn-outline-primary">{{ __('menu.edit') }}</a>
        <form action="{{ route('admin.categories.destroy', $cat) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('menu.confirm_delete') }}')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">{{ __('menu.delete') }}</button></form></td></tr>@endforeach</tbody></table>
    </div>
    @if($categories->hasPages())<div class="card-footer">{{ $categories->links() }}</div>@endif
</div>
@endsection
