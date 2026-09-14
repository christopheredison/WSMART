<script>
window.RiskNoteHelpers = window.RiskNoteHelpers || {
    statusLabels: @json(\App\Models\RiskNote::statusLabels()),
    formatDateWib: function(dateStr) {
        if (!dateStr) return '-';
        const date = new Date(dateStr);
        if (Number.isNaN(date.getTime())) return '-';

        const parts = new Intl.DateTimeFormat('en-GB', {
            timeZone: 'Asia/Jakarta',
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false
        }).formatToParts(date);

        const get = (type) => (parts.find(p => p.type === type) || {}).value || '00';
        return `${get('day')}-${get('month')}-${get('year')} ${get('hour')}:${get('minute')}:${get('second')} WIB`;
    },
    statusBadge: function(status) {
        const labels = this.statusLabels || {};
        const key = Number.parseInt(status, 10);
        const lookup = Number.isNaN(key) ? status : key;
        const label = labels[lookup] || labels[String(lookup)] || 'Informasi';
        const toneMap = {
            1: 'success',
            2: 'danger',
            3: 'info',
            4: 'warning',
            5: 'success',
            6: 'danger'
        };
        const tone = toneMap[lookup] || toneMap[String(lookup)] || 'primary';
        return `<span class="badge bg-${tone}-subtle text-${tone === 'warning' ? 'dark' : tone} border border-${tone}">${label}</span>`;
    }
};
</script>
