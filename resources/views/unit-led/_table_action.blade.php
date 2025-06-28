<div class="d-flex gap-2">
    <a href="{{ route('unit-led.show', $row->id) }}" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Detail">
        <i class="bx bx-show"></i>
    </a>
    <a href="{{ route('unit-led.edit', $row->id) }}" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Edit">
        <i class="bx bx-edit"></i>
    </a>
    <button type="button" class="btn btn-sm btn-danger" onclick="deleteData({{ $row->id }})" data-bs-toggle="tooltip" title="Hapus">
        <i class="bx bx-trash"></i>
    </button>
</div>