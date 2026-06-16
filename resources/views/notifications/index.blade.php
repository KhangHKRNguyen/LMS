@extends('layouts.' . Auth::user()->role)

@section(Auth::user()->role . '_content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Thông báo</h4>
        <div class="text-muted">Theo dõi các cập nhật mới nhất trong lớp học của bạn.</div>
    </div>

    <form method="POST" action="{{ route('notifications.read_all') }}">
        @csrf
        @method('PATCH')
        <button class="btn btn-outline-secondary btn-sm" type="submit">
            <i class="bi bi-check2-all me-1"></i> Đánh dấu tất cả đã đọc
        </button>
    </form>
</div>

<div class="bg-white shadow-sm border rounded-3 overflow-hidden">
    @forelse($notifications as $recipient)
        @php($notification = $recipient->notification)
        <div class="p-4 border-bottom {{ $recipient->is_read ? '' : 'bg-light' }}">
            <div class="d-flex justify-content-between gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        @unless($recipient->is_read)
                            <span class="badge bg-danger">Mới</span>
                        @endunless
                        <h6 class="fw-bold mb-0">{{ $notification->title }}</h6>
                    </div>
                    <div class="text-muted mb-2">{{ $notification->content }}</div>
                    <div class="small text-secondary">
                        Người gửi:
                        {{ str_starts_with((string) $notification->type, 'attendance_warning') || str_starts_with((string) $notification->type, 'missing_assignment_warning') || $notification->type === 'grade_updated' || $notification->type === 'summary_approved' ? 'Hệ thống' : ($notification->sender?->name ?? 'Hệ thống') }}
                        <span class="mx-2">•</span>
                        {{ optional($notification->sent_at ?? $notification->created_at)->format('d/m/Y H:i') }}
                    </div>
                </div>

                @unless($recipient->is_read)
                    <form method="POST" action="{{ route('notifications.read', $recipient) }}">
                        @csrf
                        @method('PATCH')
                        <button class="btn btn-sm btn-outline-primary" type="submit">Đã đọc</button>
                    </form>
                @endunless
            </div>
        </div>
    @empty
        <div class="p-5 text-center text-muted">
            <i class="bi bi-bell fs-2 d-block mb-2"></i>
            Chưa có thông báo nào.
        </div>
    @endforelse
</div>

<div class="mt-3">
    {{ $notifications->links() }}
</div>
@endsection
