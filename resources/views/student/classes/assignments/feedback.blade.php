@extends('layouts.student_classroom')

@section('title', 'Trao đổi phản hồi bài làm')

@section('class_content')
<div class="container-fluid p-0">
    <div class="mb-3">
        <a href="{{ route('student.classes.assignments.submissions.show', [$class->id, $distribution->id, $submission->id]) }}" class="btn btn-sm btn-light border">
            <i class="bi bi-arrow-left me-1"></i> Quay lại chi tiết bài làm
        </a>
    </div>

    <div class="card shadow-sm border-0 bg-white rounded">
        <div class="card-header bg-red text-dark fw-bold py-3">
            KHUNG TRAO ĐỔI VỚI GIẢNG VIÊN - LƯỢT LÀM BÀI #{{ $submission->attempt_number }}
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
                            <div class="p-3 rounded-3 shadow-sm {{ $isMe ? 'bg-primary text-white rounded-start' : 'bg-white text-dark rounded-end' }} fs-7" style="white-space: pre-wrap;">{{ $chat->content }}</div>
                            <small class="text-muted d-block mt-1 fs-8 text-end font-monospace">
                                {{ \Carbon\Carbon::parse($chat->created_at)->format('H:i d/m/Y') }}
                            </small>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-5">
                        Chưa có cuộc hội thoại nào. Hãy nhập nội dung bên dưới để gửi thắc mắc đến Giảng viên.
                    </div>
                @endforelse
            </div>

            <div class="p-3 bg-white border-top">
                <form action="{{ route('student.classes.assignments.submissions.feedback.send', [$class->id, $distribution->id, $submission->id]) }}" method="POST">
                    @csrf
                    <div class="input-group">
                        <textarea name="content" class="form-submit form-control border fs-7" rows="2" placeholder="Nhập câu hỏi hoặc nội dung phản hồi về điểm số, đáp án bài tập tại đây..." required></textarea>
                        <button class="btn btn-primary fw-bold px-4" type="submit">
                            Gửi đi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Tự động cuộn khung chat xuống cuối cùng khi tải trang
    document.addEventListener("DOMContentLoaded", function() {
        var chatBox = document.querySelector(".chat-box");
        chatBox.scrollTop = chatBox.scrollHeight;
    });
</script>
@endsection