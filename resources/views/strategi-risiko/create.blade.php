@extends('layouts.default')
@section('dashboard')
<div class="row mb-7">
  <div class="col d-flex align-items-center gap-3">
    <div class="lead__icon bg-secondary-subtle">
      <div class="svg-icon svg-icon-secondary">
        @include('partials.icon-tool')
      </div>
    </div>
    <h3 class="mb-0">Manage Strategi Risiko</h3>
  </div>
</div>
@if ($data)
<form method="POST" action="{{ route('strategi-risiko.update') }}">
  @csrf
  @method('PUT')
  <div class="row g-2 g-lg-3">
    <div class="col-md-4">
      <div class="card">
        <div class="card-header bg-info text-white border-0 py-xxl-4">
          <h6>Total Anggaran Unit Kerja (Rp)</h6>
        </div>
        <div class="card-body py-xxl-4">
          <div class="form-group">
            <input class="form-control" id="rupiah" name="total_anggaran_unit" type="text"
              placeholder="Isi total anggaran unit kerja (Rp)"
              value="{{ number_format(old('total_anggaran_unit', $data->total_anggaran_unit ?? ''), 0, ',', '.') }}" />
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-4">
      <div class="card">
        <div class="card-header bg-danger text-white border-0 py-xxl-4">
          <h6>% Risk Tolerance</h6>
        </div>
        <div class="card-body py-xxl-4">
          <div class="form-group">
            <input class="form-control" step="0.1" name="persentase_risk_tolerance" type="number"
              placeholder="Isi % risk tolerance"
              value="{{ old('persentase_risk_tolerance', $data->persentase_risk_tolerance ?? '3') }}" />
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-4">
      <div class="card">
        <div class="card-header bg-warning text-white border-0 py-xxl-4">
          <h6>% Risk Appetite</h6>
        </div>
        <div class="card-body py-xxl-4">
          <div class="form-group">
            <input class="form-control" step="0.1" name="persentase_risk_appetite" type="number"
              placeholder="Isi % risk appetite"
              value="{{ old('persentase_risk_appetite', $data->persentase_risk_appetite ?? '2.5') }}" />
          </div>
        </div>
      </div>
    </div>
    <div class="col-12">
      <div class="card card-sm">
        <div class="card-body">
          <div class="table-responsive-sm">
            <table class="table table-sm table-borderless mb-md-0">
              <thead>
                <tr>
                  <th>Sikap Risiko</th>
                  <th>Batas Maksimal Level Risiko</th>
                  <th>% Risk Tolerance Sikap Risiko</th>
                  <th>% Risk Appetite Sikap Risiko</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td class="align-middle">Konservatif</td>
                  <td>
                    <div class="form-group">
                      <select class="form-select select2 js-select-hide-search" name="batas_konservatif">
                        <option value="1"
                          {{ old('batas_konservatif', $data->batas_konservatif ?? '') == 1 ? 'selected' : '' }}>Low
                        </option>
                        <option value="2"
                          {{ old('batas_konservatif', $data->batas_konservatif ?? '') == 2 ? 'selected' : '' }}>Low
                          To
                          Moderate</option>
                        <option value="3"
                          {{ old('batas_konservatif', $data->batas_konservatif ?? '') == 3 ? 'selected' : '' }}>
                          Moderate
                        </option>
                        <option value="4"
                          {{ old('batas_konservatif', $data->batas_konservatif ?? '') == 4 ? 'selected' : '' }}>
                          Moderate
                          To High</option>
                        <option value="5"
                          {{ old('batas_konservatif', $data->batas_konservatif ?? '') == 5 ? 'selected' : '' }}>High
                        </option>
                      </select>
                    </div>
                  </td>
                  <td>
                    <div class="form-group d-flex align-items-center">
                      <input class="form-control" id="persentase_risk_tolerance_konservatif"
                        name="persentase_risk_tolerance_konservatif" type="number"
                        value="{{ old('persentase_risk_tolerance_konservatif', $data->persentase_risk_tolerance_konservatif ?? '') }}" />
                    </div>
                  </td>
                  <td>
                    <div class="form-group">
                      <input class="form-control" id="persentase_risk_appetite_konservatif"
                        name="persentase_risk_appetite_konservatif" type="number"
                        value="{{ old('persentase_risk_appetite_konservatif', $data->persentase_risk_appetite_konservatif ?? '') }}" />
                    </div>
                  </td>
                </tr>
                <tr>
                  <td class="align-middle">Moderat</td>
                  <td>
                    <div class="form-group">
                      <select class="form-select select2 js-select-hide-search" name="batas_moderat">
                        <option value="1" {{ old('batas_moderat', $data->batas_moderat ?? '') == 1 ? 'selected' : '' }}>
                          Low</option>
                        <option value="2" {{ old('batas_moderat', $data->batas_moderat ?? '') == 2 ? 'selected' : '' }}>
                          Low To Moderate</option>
                        <option value="3" {{ old('batas_moderat', $data->batas_moderat ?? '') == 3 ? 'selected' : '' }}>
                          Moderate</option>
                        <option value="4" {{ old('batas_moderat', $data->batas_moderat ?? '') == 4 ? 'selected' : '' }}>
                          Moderate To High</option>
                        <option value="5" {{ old('batas_moderat', $data->batas_moderat ?? '') == 5 ? 'selected' : '' }}>
                          High</option>
                      </select>
                    </div>
                  </td>
                  <td>
                    <div class="form-group d-flex align-items-center">
                      <input class="form-control" id="persentase_risk_tolerance_moderat"
                        name="persentase_risk_tolerance_moderat" type="number"
                        value="{{ old('persentase_risk_tolerance_moderat', $data->persentase_risk_tolerance_moderat ?? '') }}" />
                    </div>
                  </td>
                  <td>
                    <div class="form-group">
                      <input class="form-control" id="persentase_risk_appetite_moderat"
                        name="persentase_risk_appetite_moderat" type="number"
                        value="{{ old('persentase_risk_appetite_moderat', $data->persentase_risk_appetite_moderat ?? '') }}" />
                    </div>
                  </td>
                </tr>
                <tr>
                  <td class="align-middle">Agresif</td>
                  <td>
                    <div class="form-group">
                      <select class="form-select select2 js-select-hide-search" name="batas_agresif">
                        <option value="1" {{ old('batas_agresif', $data->batas_agresif ?? '') == 1 ? 'selected' : '' }}>
                          Low</option>
                        <option value="2" {{ old('batas_agresif', $data->batas_agresif ?? '') == 2 ? 'selected' : '' }}>
                          Low To Moderate</option>
                        <option value="3" {{ old('batas_agresif', $data->batas_agresif ?? '') == 3 ? 'selected' : '' }}>
                          Moderate</option>
                        <option value="4" {{ old('batas_agresif', $data->batas_agresif ?? '') == 4 ? 'selected' : '' }}>
                          Moderate To High</option>
                        <option value="5" {{ old('batas_agresif', $data->batas_agresif ?? '') == 5 ? 'selected' : '' }}>
                          High</option>
                      </select>
                    </div>
                  </td>
                  <td>
                    <div class="form-group d-flex align-items-center">
                      <input class="form-control" id="persentase_risk_tolerance_agresif"
                        name="persentase_risk_tolerance_agresif" type="number"
                        value="{{ old('persentase_risk_tolerance_agresif', $data->persentase_risk_tolerance_agresif ?? '') }}" />
                    </div>
                  </td>
                  <td>
                    <div class="form-group d-flex align-items-center">
                      <input class="form-control" id="persentase_risk_appetite_agresif"
                        name="persentase_risk_appetite_agresif" type="number"
                        value="{{ old('persentase_risk_appetite_agresif', $data->persentase_risk_appetite_agresif ?? '') }}" />
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    @foreach(['Konservatif' => $jenisRisikoKonservatif, 'Moderat' => $jenisRisikoModerat, 'Agresif' =>
    $jenisRisikoAgresif] as $label => $jenisRisiko)
    <div class="col-12 col-md-4">
      <div class="card card-sm">
        <div class="card-body">
          <div class="row g-0 ff-heading fw-semibold">
            <div class="col-9">
              <p class="">Risk Limit - {{ $label }}</p>
            </div>
            <div class="col-3 text-center">
              <p>%</p>
            </div>
          </div>
          <div class="row g-2">
            @foreach($jenisRisiko as $index => $risk)
            <div class="col-9 d-flex align-items-center">
              <div>{{ $risk->title ??  $risk->jenisRisiko->title}}</div>
            </div>
            <div class="col-3">
              <div class="form-group">
                <input class="form-control text-center px-2" step="0.01" name="persentase[]" max="100" type="number"
                  placeholder="#"
                  value="{{ old('persentase.' . $index, isset($data) ? $risk->persentase_limit ?? '' : '') }}" />
                <input type="hidden" name="jenis_risiko[]" value="{{ $risk->jenisRisiko->id }}">
              </div>
            </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
    @endforeach
  </div>
  <div class="d-flex gap-1 pt-5">
    <button type="submit" class="btn btn-submit">Update</button>
    <a href="{{ route('strategi-risiko.index') }}" class="btn btn-outline-secondary">Batal</a>
  </div>
</form>

@else
<form method="POST" action="{{ route('strategi-risiko.store') }}">
  @csrf
  <div class="row g-2 g-lg-3">
    <!--==================== Total Anggaran Unit Kerja - Risk Tolerance - Risk Appetite ====================-->
    <div class="col-md-4">
      <div class="card">
        <div class="card-header bg-info text-white border-0 py-xxl-4">
          <h6>Total Anggaran Unit Kerja (Rp)</h6>
        </div>
        <div class="card-body py-xxl-4">
          <div class="form-group">
            <input class="form-control" id="rupiah" name="total_anggaran_unit" type="text"
              placeholder="Isi total anggaran unit kerja (Rp)" value="{{ old('total_anggaran_unit') }}" />
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-4">
      <div class="card">
        <div class="card-header bg-danger text-white border-0 py-xxl-4">
          <h6>% Risk Tolerance</h6>
        </div>
        <div class="card-body py-xxl-4">
          <div class="form-group">
            <input class="form-control" id="persentase_risk_tolerance" step="0.1" name="persentase_risk_tolerance"
              type="number" placeholder="Isi % risk tolerance" value="3" />
          </div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-4">
      <div class="card">
        <div class="card-header bg-warning text-white border-0 py-xxl-4">
          <h6>% Risk Appetite</h6>
        </div>
        <div class="card-body py-xxl-4">
          <div class="form-group">
            <input class="form-control" id="persentase_risk_appetite" step="0.1" name="persentase_risk_appetite"
              type="number" placeholder="Isi % risk appetite" value="2.5" />
          </div>
        </div>
      </div>
    </div>
    <!--==================== Sikap Risiko ====================-->
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <div class="table-responsive-sm">
            <table class="table table-sm table-borderless mb-md-0">
              <thead>
                <tr>
                  <th>Sikap Risiko</th>
                  <th>Batas Maksimal Level Risiko</th>
                  <th>% Risk Tolerance Sikap Risiko</th>
                  <th>% Risk Appetite Sikap Risiko</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td class="align-middle">Konservatif</td>
                  <td>
                    <div class="form-group">
                      <select class="form-select select2 js-select-hide-search" name="batas_konservatif">
                        <option value="" disabled>Pilih level risiko</option>
                        <option value="1" selected >Low</option>
                        <option value="2">Low To Moderate</option>
                        <option value="3">Moderate</option>
                        <option value="4">Moderate To High</option>
                        <option value="5">High</option>
                      </select>
                    </div>
                  </td>
                  <td>
                    <div class="form-group d-flex align-items-center">
                      <input class="form-control" id="persentase_risk_tolerance_konservatif"
                        name="persentase_risk_tolerance_konservatif" type="number" placeholder="#"
                        value="15" />
                      <span class="badge-notification">15</span>
                    </div>
                  </td>
                  <td>
                    <div class="form-group d-flex align-items-center">
                      <input class="form-control" id="persentase_risk_appetite_konservatif"
                        name="persentase_risk_appetite_konservatif" type="number" placeholder="#"
                        value="15" />
                      <span class="badge-notification">15</span>
                    </div>
                  </td>
                </tr>
                <tr>
                  <td class="align-middle">Moderat</td>
                  <td>
                    <div class="form-group">
                      <select class="form-select select2 js-select-hide-search" name="batas_moderat">
                        <option value="" disabled>Pilih level risiko</option>
                        <option value="1">Low</option>
                        <option value="2" selected>Low To Moderate</option>
                        <option value="3">Moderate</option>
                        <option value="4">Moderate To High</option>
                        <option value="5">High</option>
                      </select>
                    </div>
                  </td>
                  <td>
                    <div class="form-group d-flex align-items-center">
                      <input class="form-control" id="persentase_risk_tolerance_moderat"
                        name="persentase_risk_tolerance_moderat" type="number" placeholder="#"
                        value="25" />
                      <span class="badge-notification">25</span>
                    </div>
                  </td>
                  <td>
                    <div class="form-group d-flex align-items-center">
                      <input class="form-control" id="persentase_risk_appetite_moderat"
                        name="persentase_risk_appetite_moderat" type="number" placeholder="#"
                        value="25" />
                      <span class="badge-notification">25</span>
                    </div>
                  </td>
                </tr>
                <tr>
                  <td class="align-middle">Agresif</td>
                  <td>
                    <div class="form-group">
                      <select class="form-select select2 js-select-hide-search" name="batas_agresif">
                        <option value="" disabled>Pilih level risiko</option>
                        <option value="1">Low</option>
                        <option value="2">Low To Moderate</option>
                        <option value="3" selected>Moderate</option>
                        <option value="4">Moderate To High</option>
                        <option value="5">High</option>
                      </select>
                    </div>
                  </td>
                  <td>
                    <div class="form-group d-flex align-items-center">
                      <input class="form-control" id="persentase_risk_tolerance_agresif"
                        name="persentase_risk_tolerance_agresif" type="number" placeholder="#"
                        value="60" />
                      <span class="badge-notification">60</span>
                    </div>
                  </td>
                  <td>
                    <div class="form-group d-flex align-items-center">
                      <input class="form-control" id="persentase_risk_appetite_agresif"
                        name="persentase_risk_appetite_agresif" type="number" placeholder="#"
                        value="60" />
                      <span class="badge-notification">60</span>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <!--==================== Risk Limit - Konservatif ====================-->
    <div class="col-12 col-md-4">
      <div class="card">
        <div class="card-body">
          <div class="row g-0 ff-heading fw-semibold">
            <div class="col-9">
              <p class="">Risk Limit - Konservatif</p>
            </div>
            <div class="col-3 text-center">
              <p>%</p>
            </div>
          </div>
          <div class="row g-2">
            @foreach($jenisRisikoKonservatif as $jenisRisiko)
            <div class="col-9 d-flex align-items-center">
              <span>{{ $jenisRisiko->title }}</span>
            </div>
            <div class="col-3">
              <div class="form-group">
                <input class="form-control text-center px-2" step="0.01" name="persentase[]" type="number"
                  placeholder="#" value="16.67" />
                <input type="hidden" name="jenis_risiko[]" value="{{ $jenisRisiko->id }}">
              </div>
            </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
    <!--==================== Risk Limit - Moderat ====================-->
    <div class="col-12 col-md-4">
      <div class="card">
        <div class="card-body">
          <div class="row g-0 ff-heading fw-semibold">
            <div class="col-9">
              <p class="">Risk Limit - Moderat</p>
            </div>
            <div class="col-3 text-center">
              <p>%</p>
            </div>
          </div>
          <div class="row g-2">
            @foreach($jenisRisikoModerat as $jenisRisiko)
            <div class="col-9 d-flex align-items-center">
              <span>{{ $jenisRisiko->title }}</span>
            </div>
            <div class="col-3">
              <div class="form-group">
                <input class="form-control text-center px-2" step="0.01" name="persentase[]" type="number"
                  placeholder="#" value="14.28" />
                <input type="hidden" name="jenis_risiko[]" value="{{ $jenisRisiko->id }}">
              </div>
            </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
    <!--==================== Risk Limit - Agresif ====================-->
    <div class="col-12 col-md-4">
      <div class="card">
        <div class="card-body">
          <div class="row g-0 ff-heading fw-semibold">
            <div class="col-9">
              <p class="">Risk Limit - Agresif</p>
            </div>
            <div class="col-3 text-center">
              <p>%</p>
            </div>
          </div>
          <div class="row g-2">
            @foreach($jenisRisikoAgresif as $jenisRisiko)
            <div class="col-9 d-flex align-items-center">
              <span>{{ $jenisRisiko->title }}</span>
            </div>
            <div class="col-3">
              <div class="form-group">
                <input class="form-control text-center px-2" step="0.01" name="persentase[]" type="number"
                  placeholder="#" value="{{ old('persentase[]') }}" />
                <input type="hidden" name="jenis_risiko[]" value="{{ $jenisRisiko->id }}">
              </div>
            </div>
            @endforeach
          </div>
        </div>
      </div>
    </div>
    <div class="d-flex gap-1 pt-5">
      <button type="submit" class="btn btn-submit">Simpan</button>
      <a href="{{ route('strategi-risiko.index') }}" class="btn btn-outline-secondary">Batal</a>
    </div>
  </div>
</form>
@endif

@endsection
@section('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
  const rupiahFields = document.querySelectorAll("#rupiah");
  rupiahFields.forEach(field => {
    field.addEventListener("input", function(e) {
      let value = e.target.value;
      value = value.replace(/[^,\d]/g, "").toString();
      let split = value.split(",");
      let sisa = split[0].length % 3;
      let rupiah = split[0].substr(0, sisa);
      let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

      if (ribuan) {
        let separator = sisa ? "." : "";
        rupiah += separator + ribuan.join(".");
      }

      rupiah = split[1] != undefined ? rupiah + "," + split[1] : rupiah;
      e.target.value = rupiah;
    });
  });
});
</script>



@endsection
