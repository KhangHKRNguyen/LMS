@extends('layouts.student_classroom')

@section('title', 'Học viên - Kho tài liệu lớp học')

@section('class_content')

<div class="mb-3">
    <h5 class="fw-bold m-0 text-dark">KHO TÀI LIỆU HỌC TẬP - {{ $class->class_name }}</h5>
    <small class="text-muted fw-semibold">Khóa học: {{ $class->course->name ?? 'N/A' }}</small>
</div>

{{-- THANH MENU CHỈ HIỂN THỊ DUY NHẤT TAB DANH SÁCH --}}
<div class="d-flex justify-content-start gap-2 mb-4 border-bottom pb-3">
    <button class="btn btn-sm px-4 fw-bold shadow-sm" style="background-color: #FDBA74; color: #7C2D12; border: 1px solid #F97316;">
        <i class="bi bi-folder2-open me-1"></i> Danh sách tài liệu hiện có ({{ $stats['total'] ?? 0 }})
    </button>
</div>

{{-- KHU VỰC THỐNG KÊ SỐ LƯỢNG FILE Y HỆT GIÁO VIÊN --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="p-3 border rounded shadow-sm d-flex align-items-center justify-content-between" style="background-color: #FFF7ED; border-color: #FED7AA !important;">
            <div>
                <small class="text-muted fw-bold d-block text-uppercase" style="font-size: 11px;">Định dạng PDF</small>
                <span class="fs-4 fw-black" style="color: #C2410C;">{{ $stats['pdf'] ?? 0 }}</span>
            </div>
            <i class="bi bi-file-earmark-pdf fs-2" style="color: #EA580C;"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="p-3 border rounded shadow-sm d-flex align-items-center justify-content-between" style="background-color: #EFF6FF; border-color: #BFDBFE !important;">
            <div>
                <small class="text-muted fw-bold d-block text-uppercase" style="font-size: 11px;">Định dạng Word</small>
                <span class="fs-4 fw-black" style="color: #1D4ED8;">{{ $stats['word'] ?? 0 }}</span>
            </div>
            <i class="bi bi-file-earmark-word fs-2" style="color: #2563EB;"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="p-3 border rounded shadow-sm d-flex align-items-center justify-content-between" style="background-color: #F0FDF4; border-color: #BBF7D0 !important;">
            <div>
                <small class="text-muted fw-bold d-block text-uppercase" style="font-size: 11px;">Định dạng Excel</small>
                <span class="fs-4 fw-black" style="color: #15803D;">{{ $stats['excel'] ?? 0 }}</span>
            </div>
            <i class="bi bi-file-earmark-excel fs-2" style="color: #16A34A;"></i>
        </div>
    </div>
    <div class="col-md-3">
        <div class="p-3 border rounded shadow-sm d-flex align-items-center justify-content-between" style="background-color: #FEF2F2; border-color: #FEE2E2 !important;">
            <div>
                <small class="text-muted fw-bold d-block text-uppercase" style="font-size: 11px;">Định dạng PowerPoint</small>
                <span class="fs-4 fw-black" style="color: #B91C1C;">{{ $stats['ppt'] ?? 0 }}</span>
            </div>
            <i class="bi bi-file-earmark-slides fs-2" style="color: #DC2626;"></i>
        </div>
    </div>
</div>

{{-- KHU VỰC TÌM KIẾM VÀ BỘ LỌC DỮ LIỆU --}}
<div class="card border-0 shadow-sm mb-4 bg-light">
    <div class="card-body p-3">
        <form action="{{ route('student.classes.materials', $class->id) }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Nhập tên từ khóa tìm kiếm tài liệu..." value="{{ $search }}">
                </div>
            </div>
            <div class="col-md-4">
                <select name="type" class="form-select form-select-sm">
                    <option value="">-- Tất cả định dạng tệp --</option>
                    <option value="pdf" {{ $filterType == 'pdf' ? 'selected' : '' }}>Tệp tin PDF (.pdf)</option>
                    <option value="word" {{ $filterType == 'word' ? 'selected' : '' }}>Văn bản Word (.doc, .docx)</option>
                    <option value="excel" {{ $filterType == 'excel' ? 'selected' : '' }}>Bảng tính Excel (.xls, .xlsx)</option>
                    <option value="powerpoint" {{ $filterType == 'powerpoint' ? 'selected' : '' }}>Báo cáo PowerPoint (.ppt, .pptx)</option>
                    <option value="image" {{ $filterType == 'image' ? 'selected' : '' }}>Hình ảnh minh họa (.png, .jpg)</option>
                    <option value="archive" {{ $filterType == 'archive' ? 'selected' : '' }}>Nén giải nén (.zip, .rar)</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm text-white fw-bold w-100 shadow-sm" style="background-color: #800000;">
                    LỌC KẾT QUẢ
                </button>
                @if(!empty($search) || !empty($filterType))
                    <a href="{{ route('student.classes.materials', $class->id) }}" class="btn btn-sm btn-secondary shadow-sm">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- BẢNG DANH SÁCH TÀI LIỆU (CHỈ CÓ NÚT DOWNLOAD) --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle m-0" style="font-size: 13.5px;">
                <thead class="text-white" style="background-color: #800000;">
                    <tr>
                        <th class="text-center" style="width: 60px; padding: 12px;">STT</th>
                        <th style="min-width: 250px;">Tiêu đề tài liệu học tập</th>
                        <th class="text-center" style="width: 130px;">Định dạng</th>
                        <th class="text-center" style="width: 130px;">Dung lượng</th>
                        <th class="text-center" style="width: 150px;">Ngày đăng tải</th>
                        <th class="text-center" style="width: 120px;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($materials as $index => $material)
                        @php
                            $ext = strtolower(pathinfo($material->file_path, PATHINFO_EXTENSION));
                            $badgeClass = 'bg-secondary';
                            if($ext === 'pdf') $badgeClass = 'bg-danger';
                            elseif(in_array($ext, ['doc', 'docx'])) $badgeClass = 'bg-primary';
                            elseif(in_array($ext, ['xls', 'xlsx'])) $badgeClass = 'bg-success';
                            elseif(in_array($ext, ['ppt', 'pptx'])) $badgeClass = 'bg-warning text-dark';
                        @endphp
                        <tr>
                            <td class="text-center font-monospace fw-bold text-muted">
                                {{ $materials->firstItem() + $index }}
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $material->title }}</div>
                                <small class="text-muted font-monospace" style="font-size: 11px;">{{ basename($material->file_path) }}</small>
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $badgeClass }} text-uppercase px-2 py-1" style="font-size: 10px;">
                                    .{{ $ext }}
                                </span>
                            </td>
                            <td class="text-center text-secondary font-monospace">
                                @if($material->file_size)
                                    {{ number_format($material->file_size / 1024, 2) }} KB
                                @else
                                    --
                                @endif
                            </td>
                            <td class="text-center text-muted font-monospace">
                                {{ $material->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="text-center">
                                <a href="{{ route('student.classes.materials.download', $material->id) }}" class="btn btn-sm btn-outline-success fw-bold px-3 py-1 shadow-sm" title="Tải tài liệu về máy">
                                    Tải xuống
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center p-5 text-muted">
                                <i class="bi bi-folder-x fs-1 d-block mb-2 text-secondary"></i>
                                Không tìm thấy bất kỳ tài liệu học tập nào trong mục này.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- PHÂN TRANG TỰ ĐỘNG GIỐNG GIÁO VIÊN --}}
<div class="d-flex justify-content-center mt-4">
    {{ $materials->appends(request()->query())->links() }}
</div>

@endsection