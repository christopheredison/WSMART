<div class="d-flex gap-2">
    <a href="{{ route('unit-led.show', ['periode' => $row->periode_id, 'id' => $row->id]) }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="Detail">
        <i class="bx bx-show"></i>
    </a>
    @if(!$unitExpired && ($row->unit_id == auth()->user()->unit_id || Gate::check('view_all_division')))
    <a href="{{ route('unit-led.edit', ['periode' => $row->periode_id, 'id' => $row->id]) }}" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Edit">
        <i class="bx bx-edit"></i>
    </a>
    <button type="button" class="btn btn-sm btn-secondary btn-upload-doc" 
        data-id="{{ $row->id }}" 
        data-bs-toggle="tooltip" 
        title="Upload Dokumen">
        <i class="bx bx-file"></i>
    </button>
    <button type="button" class="btn btn-sm btn-danger" onclick="deleteData({{ $row->id }})" data-bs-toggle="tooltip" title="Hapus">
        <i class="bx bx-trash"></i>
    </button>
    @endif
</div>