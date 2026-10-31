{{-- components/notification-dropdown.blade.php (phase 3)
     endpoint sama seperti sebelumnya: notifications.latest (5 terbaru + unread_count, polling 30 detik),
     POST /notifications/{id}/read saat item diklik (tanpa redirect), notifications.read-all.
     logika ada di Alpine.data('sgNotifications') -> resources/js/student-go/alpine.js --}}
<div class="sg-dropdown"
     x-data="sgNotifications({
        latestUrl: @js(route('notifications.latest')),
        readAllUrl: @js(route('notifications.read-all')),
        readUrl: @js(url('/notifications/__ID__/read')),
        sprite: @js(asset('assets/icons/ui/sprite.svg')),
     })"
     @keydown.escape.stop="close(true)" @click.outside="close()" @focusout="onFocusOut($event)">
    <button type="button" class="sg-icon-btn" x-ref="trigger" @click="toggle()"
            aria-expanded="false" :aria-expanded="open.toString()" aria-controls="sg-notification-panel"
            aria-label="Notifikasi" :aria-label="label">
        <x-icon name="bell" />
        <span class="sg-count" x-show="unreadCount > 0" x-text="unreadCount > 99 ? '99+' : unreadCount" aria-hidden="true" x-cloak></span>
    </button>
    {{-- diumumkan pembaca layar hanya saat jumlah berubah --}}
    <span class="sr-only" aria-live="polite" x-text="liveText"></span>

    <div id="sg-notification-panel" class="sg-overlay sg-menu sg-notif" x-ref="panel" x-show="open" x-cloak
         role="region" aria-labelledby="sg-notification-title">
        <div class="sg-notif__head">
            <h2 id="sg-notification-title">Notifikasi</h2>
            <button type="button" class="sg-btn sg-btn--tertiary" x-show="unreadCount > 0" @click="markAllAsRead()">
                Tandai semua dibaca
            </button>
        </div>

        <template x-if="loaded && notifications.length === 0">
            <div class="sg-notif__empty">
                <img src="{{ asset('assets/illustrations/empty-states/no-notifications.svg') }}" alt="" width="96" height="72">
                <p>Belum ada notifikasi.</p>
            </div>
        </template>

        <ul class="sg-notif__list" x-show="notifications.length > 0">
            <template x-for="notification in notifications" :key="notification.id">
                <li>
                    <button type="button" class="sg-notif__item" :data-unread="notification.is_read ? null : ''"
                            @click="handleNotificationClick(notification)">
                        <svg class="sg-icon sg-notif__icon" aria-hidden="true" focusable="false"><use :href="icon(notification.type)"></use></svg>
                        <span>
                            <span class="sg-notif__title" x-text="notification.title"></span>
                            <span class="sg-notif__msg" x-text="notification.message"></span>
                            <span class="sg-notif__time" x-text="formatTime(notification.created_at)"></span>
                            <span class="sr-only" x-show="! notification.is_read">Belum dibaca. Pilih untuk menandai dibaca.</span>
                        </span>
                        <span class="sg-notif__dot" x-show="! notification.is_read" aria-hidden="true"></span>
                    </button>
                </li>
            </template>
        </ul>

        <div class="sg-notif__foot">
            <a href="{{ route('notifications.index') }}" class="sg-btn sg-btn--tertiary">Lihat semua notifikasi</a>
        </div>
    </div>
</div>
