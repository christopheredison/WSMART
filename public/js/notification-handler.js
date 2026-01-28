$(document).ready(function() {
    // Load notifications on page load
    loadNotifications();

    // Refresh notifications every 10 seconds
    setInterval(loadNotifications, 10000);

    // Mark all as read
    $(document).on('click', '#markAllAsRead', function(e) {
        e.preventDefault();
        markAllAsRead();
    });

    // Show notification detail modal
    $(document).on('click', '.notification-item', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        const title = $(this).data('title');
        const message = $(this).data('message');
        const time = $(this).data('time');
        const read = $(this).data('read');
        const link = $(this).data('link');

        console.log('lin nya', link);

        // Jika link valid, lakukan proses redirect
        if (link && link !== '#') {
            if (!read) {
                // Jika belum dibaca, tandai dulu lalu redirect
                markAsRead(id, function() {
                    window.location.href = link;
                });
            } else {
                // Jika sudah dibaca, langsung redirect
                window.location.href = link;
            }
        }

        // $('#notification-title').text(title);
        // $('#notification-message').text(message);
        // $('#notification-time').text(time);

        // if (link && link !== '#') {
        //     $('#notification-link').attr('href', link).show();
        // } else {
        //     $('#notification-link').hide();
        // }

        // if (read) {
        //     $('#toggleReadStatus').text('Tandai Belum Dibaca');
        //     $('#toggleReadStatus').data('action', 'unread');
        // } else {
        //     $('#toggleReadStatus').text('Tandai Sudah Dibaca');
        //     $('#toggleReadStatus').data('action', 'read');
        // }

        // $('#toggleReadStatus').data('id', id);
        // // Menggunakan Bootstrap 5 modal options
        // const modalElement = document.getElementById('notificationDetailModal');
        // const modalOptions = {
        //     backdrop: false,
        //     keyboard: true
        // };
        // const modal = new bootstrap.Modal(modalElement, modalOptions);
        // modal.show();

        // if (!read) {
        //     markAsRead(id);
        // }
    });

    // Toggle read status
    $(document).on('click', '#toggleReadStatus', function() {
        const id = $(this).data('id');
        const action = $(this).data('action');

        if (action === 'read') {
            markAsRead(id);
            $(this).text('Tandai Belum Dibaca');
            $(this).data('action', 'unread');
        } else {
            markAsUnread(id);
            $(this).text('Tandai Sudah Dibaca');
            $(this).data('action', 'read');
        }
    });
});

/**
 * Helper: Format Tanggal (DD MMM YYYY HH:mm)
 * Mengonversi UTC dari Database ke Waktu Lokal (WIB)
 */
function formatCustomDate(dateString) {
    if (!dateString) return '';

    // 1. Parsing Tanggal
    // Laravel biasanya mengirim string "YYYY-MM-DD HH:mm:ss" (tanpa Z)
    // Kita tambahkan " UTC" agar browser tahu ini bukan waktu lokal
    let date;

    // Cek apakah string mengandung "T" atau "Z" (ISO format), jika tidak, asumsikan UTC string biasa
    if (dateString.indexOf('Z') === -1 && dateString.indexOf('+') === -1) {
         // Safari/Firefox kadang butuh format "YYYY/MM/DD" atau ISO 8601 lengkap
         // Cara paling aman memaksa UTC adalah mengganti spasi dengan T dan tambah Z
        date = new Date(dateString.replace(' ', 'T') + 'Z');
    } else {
        date = new Date(dateString);
    }

    // 2. Formatting ke Bahasa Indonesia
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    const day = String(date.getDate()).padStart(2, '0');
    const month = months[date.getMonth()];
    const year = date.getFullYear();
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');

    return `${day} ${month} ${year} ${hours}:${minutes}`;
}

/**
 * Fungsi untuk memuat notifikasi
 */
function loadNotifications() {
    $.ajax({
        url: '/notifications/unread',
        type: 'GET',
        success: function(response) {
            updateNotificationBadge(response.count);
            updateNotificationList(response.notifications);

            // // Jika dalam mode development atau tidak ada notifikasi, gunakan contoh notifikasi
            // if (response.notifications.length === 0) {
            //     const exampleNotifications = [
            //         {
            //             id: 1,
            //             title: 'Risiko Baru',
            //             message: 'Risiko baru telah ditambahkan pada proyek Pembangunan Gedung A',
            //             time_ago: '5 menit yang lalu',
            //             read_at: null,
            //             icon: 'bx bx-error-circle',
            //             link: '/risks/view/1'
            //         },
            //         {
            //             id: 2,
            //             title: 'KRI Melewati Threshold',
            //             message: 'Indikator KRI "Keterlambatan Proyek" telah melewati batas threshold pada proyek Jembatan B',
            //             time_ago: '1 jam yang lalu',
            //             read_at: null,
            //             icon: 'bx bx-error',
            //             link: '/kri/view/2'
            //         },
            //         {
            //             id: 3,
            //             title: 'Laporan Diperbarui',
            //             message: 'Laporan bulanan untuk proyek Infrastruktur C telah diperbarui dan memerlukan persetujuan Anda',
            //             time_ago: '3 jam yang lalu',
            //             read_at: null,
            //             icon: 'bx bx-file',
            //             link: '/reports/view/3'
            //         },
            //         {
            //             id: 4,
            //             title: 'Mitigasi Risiko',
            //             message: 'Tindakan mitigasi untuk risiko "Keterlambatan Material" telah selesai dilaksanakan',
            //             time_ago: '1 hari yang lalu',
            //             read_at: '2023-06-15 10:30:00',
            //             icon: 'bx bx-check-circle',
            //             link: '/mitigation/view/4'
            //         },
            //         {
            //             id: 5,
            //             title: 'Evaluasi Risiko',
            //             message: 'Evaluasi risiko triwulan untuk Unit Bisnis D telah selesai dan tersedia untuk ditinjau',
            //             time_ago: '2 hari yang lalu',
            //             read_at: '2023-06-14 15:45:00',
            //             icon: 'bx bx-analyse',
            //             link: '/evaluation/view/5'
            //         }
            //     ];
            //     updateNotificationList(exampleNotifications);
            // } else {
            //     updateNotificationList(response.notifications);
            // }
        },
        error: function(xhr) {
            console.error('Error loading notifications:', xhr.responseText);

            // Tampilkan contoh notifikasi jika terjadi error
            const exampleNotifications = [
                {
                    id: 1,
                    title: 'Risiko Baru',
                    message: 'Risiko baru telah ditambahkan pada proyek Pembangunan Gedung A',
                    time_ago: '5 menit yang lalu',
                    read_at: null,
                    icon: 'bx bx-error-circle',
                    link: '/risks/view/1'
                },
                {
                    id: 2,
                    title: 'KRI Melewati Threshold',
                    message: 'Indikator KRI "Keterlambatan Proyek" telah melewati batas threshold pada proyek Jembatan B',
                    time_ago: '1 jam yang lalu',
                    read_at: null,
                    icon: 'bx bx-error',
                    link: '/kri/view/2'
                },
                {
                    id: 3,
                    title: 'Laporan Diperbarui',
                    message: 'Laporan bulanan untuk proyek Infrastruktur C telah diperbarui dan memerlukan persetujuan Anda',
                    time_ago: '3 jam yang lalu',
                    read_at: null,
                    icon: 'bx bx-file',
                    link: '/reports/view/3'
                },
                {
                    id: 4,
                    title: 'Mitigasi Risiko',
                    message: 'Tindakan mitigasi untuk risiko "Keterlambatan Material" telah selesai dilaksanakan',
                    time_ago: '1 hari yang lalu',
                    read_at: '2023-06-15 10:30:00',
                    icon: 'bx bx-check-circle',
                    link: '/mitigation/view/4'
                },
                {
                    id: 5,
                    title: 'Evaluasi Risiko',
                    message: 'Evaluasi risiko triwulan untuk Unit Bisnis D telah selesai dan tersedia untuk ditinjau',
                    time_ago: '2 hari yang lalu',
                    read_at: '2023-06-14 15:45:00',
                    icon: 'bx bx-analyse',
                    link: '/evaluation/view/5'
                }
            ];
            updateNotificationList(exampleNotifications);
        }
    });
}

/**
 * Fungsi untuk memperbarui badge counter notifikasi
 */
function updateNotificationBadge(count) {
    const badge = $('.notification-indicator-number');
    badge.text(count);
    badge.show();
    // if (count > 0) {
    //     badge.text(count).show();
    // } else {
    //     badge.text('0').hide();
    // }
}

/**
 * Fungsi untuk memperbarui daftar notifikasi
 */
function updateNotificationList(notifications) {
    const notificationList = $('#notification-list');
    notificationList.empty();

    // Filter notifikasi yang belum dibaca
    const unreadNotifications = notifications.filter(notification => !notification.read_at);

    if (!unreadNotifications || unreadNotifications.length === 0) {
        notificationList.append(`
            <div class="list-group-item">
                <div class="notification notification-flush">
                    <div class="notification-body text-center">
                        <p class="mb-0">Tidak ada notifikasi baru</p>
                    </div>
                </div>
            </div>
        `);
        return;
    }

    unreadNotifications.forEach(function(notification) {
        const readClass = 'notification-unread';
        const icon = notification.icon || 'bx bx-bell';

        // Pastikan semua nilai ada sebelum digunakan
        const title = notification.title || 'Notifikasi';
        const message = notification.message || '';
        const timeAgo = notification.time_ago || '';

        // Menentukan ikon yang sesuai berdasarkan jenis notifikasi
        let iconClass = icon;
        let iconColor = 'text-primary';
        let iconEmoji = '🔔';

        // Menyesuaikan ikon dan warna berdasarkan type dan severity dari database
        if (notification.type && notification.color) {
            // Gunakan data dari database jika tersedia
            switch(notification.type) {
                case 'risiko':
                    iconClass = 'bx bx-error-circle';
                    iconEmoji = '⚠️';
                    break;
                case 'kri':
                    iconClass = 'bx bx-line-chart';
                    iconEmoji = '📊';
                    break;
                case 'laporan':
                    iconClass = 'bx bx-file';
                    iconEmoji = '📄';
                    break;
                case 'mitigasi':
                    iconClass = 'bx bx-check-shield';
                    iconEmoji = '🛡️';
                    break;
                case 'evaluasi':
                    iconClass = 'bx bx-analyse';
                    iconEmoji = '📈';
                    break;
            }

            // Gunakan warna dari database
            iconColor = `text-${notification.color}`;
        } else {
            // Fallback ke pengecekan berdasarkan judul jika data tidak tersedia
            if (title.includes('Risiko')) {
                iconClass = 'bx bx-error-circle';
                iconColor = 'text-danger';
                iconEmoji = '⚠️';
            } else if (title.includes('KRI')) {
                iconClass = 'bx bx-line-chart';
                iconColor = 'text-warning';
                iconEmoji = '📊';
            } else if (title.includes('Laporan')) {
                iconClass = 'bx bx-file';
                iconColor = 'text-info';
                iconEmoji = '📄';
            } else if (title.includes('Mitigasi')) {
                iconClass = 'bx bx-check-shield';
                iconColor = 'text-success';
                iconEmoji = '🛡️';
            } else if (title.includes('Evaluasi')) {
                iconClass = 'bx bx-analyse';
                iconColor = 'text-primary';
                iconEmoji = '📈';
            }
        }

        // Menentukan badge severity jika ada
        let severityBadge = '';
        if (notification.severity) {
            let badgeClass = 'bg-secondary';
            switch(notification.severity) {
                case 'high':
                    badgeClass = 'bg-danger';
                    break;
                case 'medium':
                    badgeClass = 'bg-warning';
                    break;
                case 'low':
                    badgeClass = 'bg-info';
                    break;
            }
            severityBadge = `<span class="badge ${badgeClass} ms-2">${notification.severity}</span>`;
        }

        // Escape nilai untuk atribut data agar kutip tidak memotong nilai
        const safeTitleAttr = String(title).replace(/'/g, '&#39;');
        const safeMessageAttr = String(message).replace(/'/g, '&#39;');
        const safeTimeAttr = String(timeAgo || '').replace(/'/g, '&#39;');
        const safeLinkAttr = String(notification.link || '#').replace(/'/g, '&#39;');
        const readFlag = notification.read_at !== null ? 1 : 0;

        notificationList.append(`
            <div class="list-group-item bg-light">
                <a class="notification notification-flush ${readClass} notification-item py-2"
                  href="#"
                  data-id="${notification.id}"
                  data-title='${safeTitleAttr}'
                  data-message='${safeMessageAttr}'
                  data-time='${safeTimeAttr}'
                  data-read='${readFlag}'
                  data-link='${safeLinkAttr}'>
                    <div class="notification-avatar">
                        <div class="me-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 50%; background-color: ${iconColor.replace('text-', 'bg-')}15;">
                            <i class="${iconClass}" style="color: #333; font-size: 16px;"></i>
                        </div>
                    </div>
                    <div class="notification-body">
                        <p class="mb-1 fw-semibold fs-6" style="color: #333;">${title} ${severityBadge}</p>
                        <p class="mb-1 text-dark small" style="font-weight: 400;">${message.length > 120 ? message.substring(0, 120) + '...' : message}</p>
                        <span class="notification-time small" style="color: #666; font-size: 11px;">
                            ${formatCustomDate(notification.created_at)} WIB
                        </span>
                    </div>
                </a>
            </div>
        `);
    });
}

/**
 * Fungsi untuk menandai notifikasi sebagai dibaca
 */
function markAsRead(id, callback = null) {
    // Gunakan CSRF token yang diambil dari form lain di halaman
    var token = $('input[name="_token"]').val();
    console.log("Using token for markAsRead:", token);
    $.ajax({
        url: `/notifications/${id}/read`,
        type: 'POST',
        data: {
            _token: token
        },
        success: function(response) {
            if (response.success) {
                if (callback) {
                    console.log('Notification marked as read:', id);
                    callback();
                } else {
                    console.log('Notification marked as read:', id);
                    // Perbarui status notifikasi di UI
                    $(`.notification-item[data-id="${id}"]`).attr('data-read', '1');
                    // Hapus dari daftar karena kita hanya menampilkan yang belum dibaca
                    $(`.notification-item[data-id="${id}"]`).closest('.list-group-item').fadeOut(300, function() {
                        $(this).remove();
                        // Reload notifikasi untuk memperbarui counter
                        loadNotifications();
                    });
                }
            }
        },
        error: function(xhr) {
            console.error('Error marking notification as read:', xhr.responseText);
            if(callback) callback();
        }
    });
}

/**
 * Fungsi untuk menandai notifikasi sebagai belum dibaca
 */
function markAsUnread(id) {
    // Gunakan CSRF token yang diambil dari form lain di halaman
    var token = $('input[name="_token"]').val();
    console.log("Using token for markAsUnread:", token);
    $.ajax({
        url: `/notifications/${id}/unread`,
        type: 'POST',
        data: {
            _token: token
        },
        success: function(response) {
            if (response.success) {
                console.log('Notification marked as unread:', id);
                loadNotifications();
            }
        },
        error: function(xhr) {
            console.error('Error marking notification as unread:', xhr.responseText);
        }
    });
}

/**
 * Fungsi untuk menandai semua notifikasi sebagai dibaca
 */
function markAllAsRead() {
    // Gunakan CSRF token yang diambil dari form lain di halaman
    var token = $('input[name="_token"]').val();
    console.log("Using token for markAllAsRead:", token);
    $.ajax({
        url: '/notifications/read-all',
        type: 'POST',
        data: {
            _token: token
        },
        success: function(response) {
            if (response.success) {
                console.log('All notifications marked as read');
                // Hapus semua notifikasi dari daftar
                $('.notification-list .list-group-item').fadeOut(300, function() {
                    $(this).remove();
                    // Reload notifikasi untuk memperbarui counter
                    loadNotifications();
                });
            }
        },
        error: function(xhr) {
            console.error('Error marking all notifications as read:', xhr.responseText);
        }
    });
}
