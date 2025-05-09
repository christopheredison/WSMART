<table class="table table-bordered text-center">
    <thead>
        <tr>
            <th rowspan="2">Risiko Kualitatif</th>
            <th colspan="5">Skala</th>
        </tr>
        <tr>
            <th>1<br>Sangat Rendah</th>
            <th>2<br>Rendah</th>
            <th>3<br>Moderat</th>
            <th>4<br>Tinggi</th>
            <th>5<br>Sangat Tinggi</th>
        </tr>
    </thead>
    <tbody>
        @foreach($groupedAreas as $riskCategory => $areas)
            <tr>
                <td colspan="6" class="fw-bold">{{ $riskCategory }}</td>
            </tr>
            @foreach($areas as $area)
                <tr>
                    <td class="text-start">{{ $area->title }}</td>
                    
                    @for ($i = 1; $i <= 5; $i++) 
                        @php
                            $detail = $area->details->where('skala', $i)->first();
                        @endphp
                        <td class="text-start">
                            @if ($detail)
                                @if ($detail->skala <= $skalaDampakIn) 
                                    <a href="#" class="select-risk-res"
                                       data-area-id="{{ $area->id }}" 
                                       data-area-title="{{ $area->title }}" 
                                       data-skala="{{ $detail->skala }}" 
                                       data-skala-desc="{{ $detail->deskripsi }}">
                                        {{ $detail->deskripsi }}
                                    </a>
                                @else
                                    <span class="text-muted">{{ $detail->deskripsi }}</span>
                                @endif
                            @endif
                        </td>
                    @endfor
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>

<script>
    // Event untuk memilih risiko dari modal
    $(".select-risk-res").click(function (e) {
        e.preventDefault(); // Mencegah reload

        var skala = $(this).data("skala");
        var skalaDesc = $(this).data("skala-desc");

        $("#skala_dampak_residual").val(skala).trigger("change");

        $("#modalKualitatifRes").modal("hide");

        $('html, body').animate({
            scrollTop: $("#skala_dampak_residual").offset().top - 50 // Sesuaikan dengan padding agar tidak tertutup navbar
        }, 300);
    });
</script>
