@php
$permissions = $action['permissions'] ?? $action['permission'] ?? [];
if (!is_array($permissions)) {
    $permissions = [$permissions];
}
if (!(!$permissions || \Gate::any($permissions))) {
    return '';
}
@endphp
@switch($action['action'])
@case('edit')
    @if($editType == 'modal')
    <button type="button" class="{{ ($action['btn_icon'] ?? false) ? 'btn-input-icon' : 'btn btn-link' }} btn-action" data-action="edit_data" data-id="{{ $id }}">
        {!! $action['label'] !!}
    </button>
    @else
    <a href="{{ route($baseRoute . 'edit', array_merge($baseRouteParams ?? [], [$id])) }}" class="{{ ($action['btn_icon'] ?? false) ? 'btn-input-icon' : 'btn btn-link' }}" data-bs-toggle="tooltip" title="Edit">
        {!! $action['label'] !!}
    </a>
    @endif
    @break
@case('delete')
    <button type="button" class="{{ ($action['btn_icon'] ?? false) ? 'btn-input-icon' : 'btn btn-link' }} btn-action" data-action="delete_data" data-id="{{ $id }}">
        {!! $action['label'] !!}
    </button>
    @break
@case('link')
    <a href="{{ $action['url'] }}" class="{{ ($action['btn_icon'] ?? false) ? 'btn-input-icon' : 'hover-underline px-1' }}" data-bs-toggle="tooltip" title="{{ $action['title'] ?? '' }}">
        {!! $action['label'] !!}
    </a>
    @break 
@case('script')
    <a href="javascript:void(0)" class="{{ ($action['btn_icon'] ?? false) ? 'btn-input-icon' : 'hover-underline px-1' }}" data-id="{{ $id }}" onclick="{{ $action['script'] }}">
        {!! $action['label'] !!}
    </a>
    @break
@endswitch