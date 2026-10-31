{{-- phase 16: kolom kiri SAGO = riwayat percakapan aktif milik pengguna (ChatbotService::getUserConversations, 10 per halaman).
     $conversations (paginator), $activeId (null di halaman daftar). di bawah 1024px daftar dilipat di balik tombol "Riwayat percakapan"
     (terbuka di halaman daftar, tertutup di halaman percakapan). percakapan baru hanya lewat POST student.chatbot.store (D-25). --}}
@php $activeId = $activeId ?? null; @endphp
<aside class="sg-chat__history" aria-labelledby="chat-history-title" x-data="{ open: {{ $activeId ? 'false' : 'true' }} }">
    <div class="sg-chat__history-head">
        <h2 id="chat-history-title">Riwayat percakapan</h2>
        <button type="button" class="sg-btn sg-btn--tertiary sg-chat__history-toggle" @click="open = ! open"
                aria-controls="chat-history-list" aria-expanded="{{ $activeId ? 'false' : 'true' }}" :aria-expanded="open.toString()">
            <span x-text="open ? 'Sembunyikan' : 'Tampilkan'">{{ $activeId ? 'Tampilkan' : 'Sembunyikan' }}</span>
            <x-icon name="chevron-down" size="sm" />
        </button>
    </div>

    <div id="chat-history-list" class="sg-chat__history-body" @if ($activeId) data-collapsed @endif :data-collapsed="open ? null : ''">
        @if ($conversations->count() > 0)
            <ul class="sg-chat__conversations">
                @foreach ($conversations as $conv)
                    @php $lastAt = $conv->last_message_at ?? $conv->created_at; @endphp
                    <li>
                        <a href="{{ route('student.chatbot.show', $conv->id) }}" class="sg-chat__conversation"
                           @if ($conv->id === $activeId) aria-current="page" @endif>
                            <span class="sg-chat__conversation-title">{{ $conv->title }}</span>
                            <span class="sg-chat__conversation-meta">
                                {{ number_format((int) $conv->message_count, 0, ',', '.') }} pesan
                                <span aria-hidden="true">·</span>
                                <time datetime="{{ $lastAt->toIso8601String() }}">{{ time_ago($lastAt) }}</time>
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
            <x-ui.pagination :paginator="$conversations" label="Halaman riwayat percakapan" class="sg-chat__history-pages" />
        @else
            <p class="sg-chat__history-empty">Belum ada percakapan. Percakapan yang Anda mulai akan tersimpan di sini.</p>
        @endif
    </div>
</aside>
