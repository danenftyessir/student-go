{{-- resources/views/student/chatbot/show.blade.php --}}
@extends('layouts.app')

@section('title', $conversation->title . ' · SAGO')

{{-- phase 16: percakapan SAGO. kolom kiri riwayat (partial), kolom kanan percakapan.
     endpoint dan payload tetap: POST student.chatbot.send {conversation_id, message} (json), POST student.chatbot.archive,
     DELETE student.chatbot.destroy; konfirmasi lewat dialog bersama (window.sgConfirm), galat lewat toast. --}}

@php
    $startedAt = $conversation->created_at;
    // relasi messages tidak berurutan; tampilkan kronologis (waktu, lalu id)
    $messages = $conversation->messages->sortBy([['created_at', 'asc'], ['id', 'asc']]);
@endphp

@section('content')
<div class="sg-container sg-chat">
    <x-ui.page-header title="SAGO" description="Student Assistant for Guidance & Opportunities. Tanya seputar proyek KKN dan dokumen di Repositori Pengetahuan.">
        <x-slot:actions>
            {{-- D-25: percakapan baru dibuat lewat POST, bukan GET --}}
            <form method="POST" action="{{ route('student.chatbot.store') }}">
                @csrf
                <x-ui.button type="submit" icon="plus">Percakapan baru</x-ui.button>
            </form>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="sg-chat__layout">
        @include('student.chatbot.components.history', ['conversations' => $conversations, 'activeId' => $conversation->id])

        <section class="sg-chat__pane" aria-labelledby="chat-title">
            <header class="sg-chat__pane-head">
                <div class="sg-chat__pane-title">
                    <h2 id="chat-title">{{ $conversation->title }}</h2>
                    <p>
                        {{ number_format((int) $conversation->message_count, 0, ',', '.') }} pesan
                        <span aria-hidden="true">·</span>
                        dimulai <time datetime="{{ $startedAt->toIso8601String() }}">{{ format_date_indonesian($startedAt) }}</time>
                    </p>
                </div>
                <div class="sg-chat__pane-actions">
                    <x-ui.button variant="tertiary" onclick="archiveConversation({{ $conversation->id }})">Arsipkan</x-ui.button>
                    <x-ui.button variant="danger" icon="trash-2" onclick="deleteConversation({{ $conversation->id }})">Hapus</x-ui.button>
                </div>
            </header>

            <div class="sg-chat__thread" id="messagesContainer" role="log" aria-live="polite" aria-label="Isi percakapan" tabindex="0">
                @forelse ($messages as $message)
                    <article class="sg-chat__msg" data-role="{{ $message->isUser() ? 'user' : 'assistant' }}">
                        <p class="sg-chat__author">{{ $message->isUser() ? 'Anda' : 'SAGO' }}</p>
                        <p class="sg-chat__text">{{ $message->content }}</p>

                        @if ($message->isAssistant() && $message->documentReferences->count() > 0)
                            <div class="sg-chat__sources">
                                <p>Sumber</p>
                                <ul>
                                    @foreach ($message->documentReferences->take(3) as $ref)
                                        @if ($ref->document)
                                            <li>
                                                <a href="{{ route('student.repository.show', $ref->document->id) }}" target="_blank" rel="noopener">
                                                    <x-icon name="file-text" size="sm" />{{ $ref->document->title }}<span class="sr-only"> (tab baru)</span>
                                                </a>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <time class="sg-chat__time" datetime="{{ $message->created_at->toIso8601String() }}">{{ $message->created_at->format('H:i') }}</time>
                    </article>
                @empty
                    <p class="sg-chat__thread-empty" id="threadEmpty">Belum ada pesan. Tulis pertanyaan pertama Anda untuk SAGO di bawah.</p>
                @endforelse

                <p class="sg-chat__typing" id="typingIndicator" hidden>
                    <span class="sg-spinner" aria-hidden="true"></span>SAGO sedang menyusun jawaban…
                </p>
            </div>

            <form id="chatForm" class="sg-chat__composer">
                @csrf
                <label for="messageInput" class="sg-label">Pesan untuk SAGO</label>
                <div class="sg-chat__composer-row">
                    <textarea id="messageInput" name="message" rows="2" class="sg-textarea" maxlength="5000" required
                              placeholder="Tulis pertanyaan Anda" aria-describedby="chat-help chat-count"></textarea>
                    <x-ui.button type="submit" id="sendButton" icon="send">Kirim</x-ui.button>
                </div>
                <div class="sg-chat__composer-foot">
                    <p id="chat-help">Enter untuk mengirim, Shift+Enter untuk baris baru.</p>
                    <p id="chat-count" class="sg-num"><span id="charCount">0</span>/5000 karakter</p>
                </div>
            </form>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const messageInput = document.getElementById('messageInput');
    const charCount = document.getElementById('charCount');
    const chatForm = document.getElementById('chatForm');
    const messagesContainer = document.getElementById('messagesContainer');
    const typingIndicator = document.getElementById('typingIndicator');
    const sendButton = document.getElementById('sendButton');
    const sprite = @js(asset('assets/icons/ui/sprite.svg'));

    // penghitung karakter + tinggi otomatis
    messageInput.addEventListener('input', function () {
        charCount.textContent = this.value.length;
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 160) + 'px';
    });

    // enter = kirim, shift+enter = baris baru
    messageInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey && !e.isComposing) {
            e.preventDefault();
            chatForm.requestSubmit();
        }
    });

    function scrollToBottom() {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
    scrollToBottom();

    chatForm.addEventListener('submit', async function (e) {
        e.preventDefault();

        const message = messageInput.value.trim();
        if (!message) return;

        messageInput.disabled = true;
        sendButton.disabled = true;

        addMessageToUI('user', message);
        messageInput.value = '';
        charCount.textContent = '0';
        messageInput.style.height = 'auto';

        typingIndicator.hidden = false;
        scrollToBottom();

        try {
            // payload sama seperti sebelumnya
            const response = await fetch(@js(route('student.chatbot.send')), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': @js(csrf_token()),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    conversation_id: {{ $conversation->id }},
                    message: message
                })
            });

            const data = await response.json();
            typingIndicator.hidden = true;

            if (data.success) {
                addMessageToUI('assistant', data.message, data.sources);
            } else {
                addMessageToUI('assistant', data.message || 'Maaf, terjadi kesalahan. Silakan coba lagi.');
            }
        } catch (error) {
            console.error('Error:', error);
            typingIndicator.hidden = true;
            addMessageToUI('assistant', 'Maaf, terjadi kesalahan koneksi. Silakan coba lagi.');
        } finally {
            messageInput.disabled = false;
            sendButton.disabled = false;
            messageInput.focus();
        }
    });

    // pesan baru dibangun lewat DOM (textContent), bukan innerHTML
    function addMessageToUI(role, content, sources = []) {
        const empty = document.getElementById('threadEmpty');
        if (empty) empty.remove();

        const article = document.createElement('article');
        article.className = 'sg-chat__msg';
        article.dataset.role = role;

        const author = document.createElement('p');
        author.className = 'sg-chat__author';
        author.textContent = role === 'user' ? 'Anda' : 'SAGO';
        article.appendChild(author);

        const text = document.createElement('p');
        text.className = 'sg-chat__text';
        text.textContent = content;
        article.appendChild(text);

        if (role === 'assistant' && sources && sources.length > 0) {
            const box = document.createElement('div');
            box.className = 'sg-chat__sources';
            const label = document.createElement('p');
            label.textContent = 'Sumber';
            box.appendChild(label);

            const list = document.createElement('ul');
            sources.forEach(function (source) {
                const item = document.createElement('li');
                const link = document.createElement('a');
                link.href = `/student/repository/${source.id}`;
                link.target = '_blank';
                link.rel = 'noopener';

                const icon = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
                icon.setAttribute('class', 'sg-icon sg-icon--sm');
                icon.setAttribute('aria-hidden', 'true');
                icon.setAttribute('focusable', 'false');
                const use = document.createElementNS('http://www.w3.org/2000/svg', 'use');
                use.setAttribute('href', `${sprite}#file-text`);
                icon.appendChild(use);

                const hint = document.createElement('span');
                hint.className = 'sr-only';
                hint.textContent = ' (tab baru)';

                link.appendChild(icon);
                link.appendChild(document.createTextNode(source.title));
                link.appendChild(hint);
                item.appendChild(link);
                list.appendChild(item);
            });
            box.appendChild(list);
            article.appendChild(box);
        }

        const now = new Date();
        const time = document.createElement('time');
        time.className = 'sg-chat__time';
        time.dateTime = now.toISOString();
        time.textContent = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        article.appendChild(time);

        messagesContainer.insertBefore(article, typingIndicator);
        scrollToBottom();
    }
});

// arsipkan percakapan (endpoint sama), konfirmasi lewat dialog bersama
async function archiveConversation(id) {
    const ok = await window.sgConfirm({
        title: 'Arsipkan percakapan?',
        message: 'Percakapan diarsipkan dan tidak lagi tampil di riwayat.',
        confirmLabel: 'Arsipkan',
        tone: 'primary',
    });
    if (!ok) return;

    fetch(@js(url('/student/chatbot')) + `/${id}/archive`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': @js(csrf_token()), 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = @js(route('student.chatbot.index'));
        } else {
            window.showNotification(data.message || 'Gagal mengarsipkan percakapan.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        window.showNotification('Terjadi kesalahan. Silakan coba lagi.', 'error');
    });
}

// hapus percakapan (endpoint sama)
async function deleteConversation(id) {
    const ok = await window.sgConfirm({
        title: 'Hapus percakapan?',
        message: 'Percakapan dan semua pesannya dihapus. Tindakan ini tidak dapat dibatalkan.',
        confirmLabel: 'Hapus',
    });
    if (!ok) return;

    fetch(@js(url('/student/chatbot')) + `/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': @js(csrf_token()), 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = @js(route('student.chatbot.index'));
        } else {
            window.showNotification(data.message || 'Gagal menghapus percakapan.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        window.showNotification('Terjadi kesalahan. Silakan coba lagi.', 'error');
    });
}
</script>
@endpush
