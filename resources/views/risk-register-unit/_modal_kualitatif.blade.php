<!-- Modal -->
<div class="modal fade" id="modalKualitatif" tabindex="-1" role="dialog" aria-labelledby="modalKualitatifLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-80" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalRisikoKualitatifLabel">Risiko Kualitatif</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
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
                                    @foreach($area->details as $detail)
                                        <td class="text-start">
                                        <a href="#" class="select-risk"
                                        data-area-id="{{ $area->id }}" 
                                        data-area-title="{{ $area->title }}" 
                                        data-skala="{{ $detail->skala }}" 
                                        data-skala-desc="{{ $detail->deskripsi }}">
                                            {{ $detail->deskripsi }}
                                        </a>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>