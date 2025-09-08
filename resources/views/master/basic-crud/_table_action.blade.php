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
    <button type="button" class="{{ ($action['btn_icon'] ?? false) ? 'btn-input-icon' : 'btn btn-link' }} btn-action" data-action="delete_data" data-id="{{ $id }}" data-bg-toggle="tooltip" title="{{ $action['title'] ?? 'Hapus' }}">
        {!! $action['label'] !!}
    </button>
    @break
@case('link')
    <a href="{{ $action['url'] }}" class="{{ ($action['btn_icon'] ?? false) ? 'btn-input-icon' : 'hover-underline px-1' }}" data-bs-toggle="tooltip" title="{{ $action['title'] ?? '' }}">
        {!! $action['label'] !!}
    </a>
    @break 
@case('script')
    <a href="javascript:void(0)" class="{{ ($action['btn_icon'] ?? false) ? 'btn-input-icon' : 'hover-underline px-1' }}" data-id="{{ $id }}" onclick="{{ $action['script'] }}" data-bs-toggle="tooltip" title="{{ $action['title'] ?? '' }}">
        {!! $action['label'] !!}
    </a>
    @break
@case('verifikasi')
    <button type="button" class="{{ ($action['btn_icon'] ?? false) ? 'btn-input-icon' : 'btn btn-link' }}" onclick="showVerifikasiModal({{ $id }}, '{{ addslashes($item->peristiwa_risiko ?? '') }}', '{{ addslashes($item->deskripsi_peristiwa_risiko ?? '') }}')" data-bs-toggle="tooltip" title="{{ $action['title'] ?? 'Verifikasi Risiko' }}">
        {!! $action['label'] !!}
    </button>
    @break    
@case('change_to_led')
    <button type="button" class="btn btn-link btn-muted-primary  btn-action px-1 py-0" data-action="change_to_led" data-id="{{ $id }}">
        <span>{!! $action['label'] !!}</span>
    </button>
    @break
@case('change_to_led_unit')
    <button type="button" class="btn btn-link btn-muted-primary  btn-action px-1 py-0" data-action="change_to_led_unit" data-id="{{ $id }}">
        <span>{!! $action['label'] !!}</span>
    </button>
    @break
@case('change_to_led_ap')
    <button type="button" class="btn btn-link btn-muted-primary  btn-action px-1 py-0" data-action="change_to_led_ap" data-id="{{ $id }}">
        <span>{!! $action['label'] !!}</span>
    </button>
    @break
@endswitch