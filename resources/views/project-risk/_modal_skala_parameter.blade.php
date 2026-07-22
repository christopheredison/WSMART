<div class="modal fade" id="modalSkalaInfo" tabindex="-1" aria-labelledby="modalSkalaInfoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modalSkalaInfoLabel">Panduan Parameter Skala Probabilitas</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-center">
                        <thead class="table-light">
                            <tr>
                                <th rowspan="2" class="align-middle" style="width: 15%;">Parameter</th>
                                <th colspan="5">Skala</th>
                            </tr>
                            <tr>
                                @php
                                    $sampleScales = $groupedSkalaParameters?->first()?->sortBy('tingkat') ?? [];
                                @endphp
                                @foreach($sampleScales as $scale)
                                    <th style="width: 17%;">{{ $scale->tingkat }}<br>{{ $scale->skala }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($groupedSkalaParameters as $type => $parameters)
                                <tr>
                                    <td class="text-start fw-bold">{{ $type }}</td>
                                    @foreach($parameters->sortBy('tingkat') as $param)
                                        <td class="text-start selectable-cell" style="cursor: pointer;"
                                            data-parameter-type="{{ $param->type_parameter }}" 
                                            data-skala-id="{{ $param->id }}">
                                            {{ $param->deskripsi }}
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>