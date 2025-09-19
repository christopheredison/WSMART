@forelse($divisiRisks as $risk)
<tr>
    <td>
        <div class="form-check">
            <input class="form-check-input pilih-risiko" type="checkbox" value="{{ $risk->id }}" id="risk-{{ $risk->id }}"
                   data-divisi="{{ $risk->unit->name ?? 'N/A' }}" 
                   data-peristiwa="{{ $risk->peristiwa_risiko }}" 
                   data-level="{{ optional($risk->riskAnalysis)->level_risiko ?? 'N/A' }}">
        </div>
    </td>
    <td>{{ $risk->unit->name ?? 'N/A' }}</td>
    <td>{{ $risk->peristiwa_risiko }}</td>
    <td>{{ optional($risk->riskAnalysis)->level_risiko ?? 'N/A' }}</td>
</tr>
@empty
<tr>
    <td colspan="4" class="text-center">Tidak ada risiko yang ditemukan untuk divisi ini.</td>
</tr>
@endforelse