<div class="d-flex gap-2">
    <a href="{{ route('unit-led.show', ['periode' => $row->periode_id, 'id' => $row->id]) }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="Detail">
        <i class="bx bx-show"></i>
    </a>
    @if ($row->version > 0)
      <a href="{{ route('unit-led.edit', ['periode' => $row->periode_id, 'id' => $row->id]) }}" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Edit">
          <i class="bx bx-edit"></i>
      </a>
    @endif
    <button type="button" class="btn btn-sm btn-danger" onclick="deleteData({{ $row->id }})" data-bs-toggle="tooltip" title="Hapus">
        <i class="bx bx-trash"></i>
    </button>
</div>