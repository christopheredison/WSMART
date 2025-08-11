<div class="d-flex gap-2">
    <a href="{{ route('project-led.show', $row->id) }}" 
        class="btn btn-sm btn-primary" 
        data-bs-toggle="tooltip" 
        title="View Data">
        <i class="bx bx-show"></i>
    </a>
    @if ($row->version > 0) 
      <a href="{{ route('project-led.edit', $row->id) }}" 
          class="btn btn-sm btn-info" 
          data-bs-toggle="tooltip" 
          title="Edit Data">
          <i class="bx bx-edit"></i>
      </a>
    @endif
    <button class="btn btn-sm btn-danger" 
        onclick="deleteData({{ $row->id }})" 
        data-bs-toggle="tooltip" 
        title="Hapus Data">
        <i class="bx bx-trash"></i>
    </button>
</div>