@extends('layouts.classroom')

@section('title', 'Phản hồi học viên')

@section('classroom_content')
<div class="container-fluid p-0">
    <div class="mb-3">
        <a href="{{ route('teacher.submissions.index', [$class->id, $distribution->id]) }}" class="btn btn-sm btn-light border">
            <i class="bi bi-arrow-left me-1"></i> Quay lại danh sách bài nộp
        </a>
    </div>

    <div class="card shadow-sm border-0 bg-white rounded">
        <div class="card-header bg-danger text-white fw-bold py-3 d-flex justify-content-between align-items-center">
            <span>HỘP THOẠI ĐANG CHAT VỚI: <span class="text-warning text-uppercase">{{ $submission->user->name }}</span></span>
            <span class="badge bg-white text-danger font-monospace fs-8">Mã HV: {{ $submission->user->id }}</span>
        </div>
        
        <div class="p-3 bg-white border-bottom fs-7 text-muted">
            Bài tập: <strong>{{ $distribution->assignment->title }}</strong> | Điểm số hiện tại: <strong class="text-primary">{{ $submission->total_grade ?? 'Chưa chấm' }}</strong>
        </div>

        <div class="card-body p-0 bg-light">
            <div class="chat-box p-4" style="height: 400px; overflow-y: auto; background-color: #f8f9fa;">
                @forelse($chats as $chat)
                    @php $isMe = ($chat->user_id === Auth::id()); @endphp
                    <div class="d-flex {{ $isMe ? 'justify-content-end' : 'justify-content-start' }} mb-3">
                        <div class="max-width-70">
                            <small class="text-muted d-block mb-1 fs-8 {{ $isMe ? 'text-end' : '' }}">
                                <strong>{{ $chat->user->name }}</strong> ({{ $chat->user->role === 'teacher' ? 'Giảng viên' : 'Học viên' }})
                            </small>
                            <div class="p-3 rounded-3 shadow-sm {{ $isMe ? 'bg-danger text-white rounded-start' : 'bg-white text-dark rounded-end' }} fs-7" style="white-space: pre-wrap;">{{ $chat->content }}</div>
                            <small class="text-muted d-block mt-1 fs-8 text-end font-monospace">
                                {{ \Carbon\Carbon::parse($chat->created_at)->format('H:i d/m/Y') }}
                            </small>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-5">
                        Chưa có lịch sử hội thoại nào cho bài nộp này.
                    </div>
                @endforelse
            </div>

            <div class="p-3 bg-white border-top">
                <form action="{{ route('teacher.submissions.feedback.send', [$class->id, $submission->id]) }}" method="POST">
                    @csrf
                    <div class="input-group">
                        <textarea name="content" class="form-control border fs-7" rows="2" placeholder="Nhập câu trả lời, nhận xét giải thích hoặc hướng dẫn bổ sung cho học viên..." required></textarea>
                        <button class="btn btn-danger fw-bold px-4" type="submit">
                            Trả lời
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Tự động cuộn khung chat xuống đáy
    document.addEventListener("DOMContentLoaded", function() {
        var chatBox = document.querySelector(".chat-box");
        chatBox.scrollTop = chatBox.scrollHeight;
    });
</script>
@endsection