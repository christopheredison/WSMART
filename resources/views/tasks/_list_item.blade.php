<div class="list-group list-group-flush">
    @foreach($items as $item)
        @php
            // Tentukan Icon berdasarkan tipe Item (Risk Register atau Monitoring)
            $icon = 'bx-file'; // Default icon
            $iconColor = 'text-primary';

            if (\Illuminate\Support\Str::contains($item['type'], 'Risk Register')) {
                $icon = 'bx-shield-quarter';
                $iconColor = 'text-danger';
            } elseif (\Illuminate\Support\Str::contains($item['type'], 'Monitoring')) {
                $icon = 'bx-bar-chart-alt-2';
                $iconColor = 'text-info';
            }
        @endphp

        <a href="{{ $item['link'] }}" class="list-group-item list-group-item-action p-3 border-bottom">
            <div class="d-flex w-100 justify-content-between align-items-center">
                {{-- Kiri: Icon & Info --}}
                <div class="d-flex align-items-center">
                    {{-- Avatar Icon --}}
                    <div class="avatar-sm me-3 bg-light rounded-circle d-flex align-items-center justify-content-center {{ $iconColor }}"
                        style="width: 45px; height: 45px; flex-shrink: 0;">
                        <i class="bx {{ $icon }} fs-4"></i>
                    </div>

                    {{-- Text Info --}}
                    <div>
                        <h6 class="mb-0 fw-bold text-dark">{{ $item['project_name'] }}</h6>
                        <small class="text-black d-block mt-1">
                            <i class="bx bx-building"></i> {{ $item['unit_name'] }}
                            <span class="mx-1 text-light-gray">|</span>
                            <span class="fw-semibold {{ $iconColor }}" style="font-size: 0.75rem;">
                                {{ $item['type'] }}
                            </span>
                        </small>
                    </div>
                </div>

                {{-- Kanan: Badge Status & Count --}}
                <div class="text-end ms-3">
                    <span class="badge bg-warning text-dark mb-1">
                        {{ $item['description'] }}
                    </span>
                    <small class="text-danger d-block fw-bold mt-1">
                        {{ $item['count'] }}
                    </small>
                </div>
            </div>
        </a>
    @endforeach
</div>
