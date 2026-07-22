<!-- Modal Delete Exclamation -->
<div class="modal fade" id="modalDelete{{ $itemId }}" tabindex="-1" aria-labelledby="{{ $itemId }}Label"
  aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm exclamation-alert" style="min-width: unset; max-width: 500px;">
    <div class="modal-content p-2">
      <div class="modal-body text-center">
        <div class="exc-icon">
          <div class="exc-icon-content">!</div>
        </div>
        <h5>Anda yakin akan menghapus "{{ $innerItemText }}"?</h5>
        <div class="d-flex justify-content-center mt-5">
          <form action="{{ $formAction }}" method="POST">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">Ya, hapus!</button>
          </form>
          <button type=" button" class="btn btn-muted" data-bs-dismiss="modal">Batal</button>
        </div>
      </div>
    </div>
  </div>
</div>
