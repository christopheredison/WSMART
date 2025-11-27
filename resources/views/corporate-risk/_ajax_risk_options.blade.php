@forelse($divisiRisks as $risk)
    @php
        $penyebabs = $risk->penyebabRisiko;
        $count = $penyebabs->count() > 0 ? $penyebabs->count() : 1;
        
        // Data untuk JS
        $penyebabList = $penyebabs->pluck('penyebab_risiko')->toArray();
        if(empty($penyebabList)) $penyebabList = ['-'];
        
        $nilaiRisiko = $risk->riskAnalysis->skala_risiko ?? '-';
        $levelRisiko = $risk->riskAnalysis->level_risiko ?? '-';
    @endphp

    <tr class="risk-row">
        <td rowspan="{{ $count }}" class="text-center align-middle bg-white">
            <div class="form-check d-flex justify-content-center">
                <input class="form-check-input pilih-risiko" type="checkbox" 
                    value="{{ $risk->id }}" 
                    id="risk-{{ $risk->id }}"
                    data-divisi="{{ $risk->unit->name ?? '-' }}"
                    data-peristiwa="{{ $risk->peristiwa_risiko }}"
                    data-level="{{ $levelRisiko }}"
                    data-nilai="{{ $nilaiRisiko }}"
                    data-penyebab='{{ json_encode($penyebabList) }}'>
            </div>
        </td>
        <td rowspan="{{ $count }}" class="align-middle bg-white">{{ $risk->unit->name ?? '-' }}</td>
        <td rowspan="{{ $count }}" class="align-middle bg-white">{{ $risk->peristiwa_risiko }}</td>
        
        <td class="align-middle">{{ $penyebabs->first()->penyebab_risiko ?? '-' }}</td>
        
        <td rowspan="{{ $count }}" class="text-center align-middle bg-white">{{ $levelRisiko }}</td>
        <td rowspan="{{ $count }}" class="text-center align-middle bg-white">{{ $nilaiRisiko }}</td>
    </tr>

    @foreach($penyebabs->slice(1) as $p)
        <tr class="risk-row-child">
            <td class="align-middle">{{ $p->penyebab_risiko }}</td>
        </tr>
    @endforeach

@empty
    <tr>
        <td colspan="6" class="text-center">Tidak ada risiko utama yang tersedia untuk divisi ini.</td>
    </tr>
@endforelse