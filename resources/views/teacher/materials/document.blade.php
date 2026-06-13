@extends('layouts.classroom')

@section('title', 'Giảng viên - Kho tài liệu')

@section('classroom_content')

{{-- HIỂN THỊ THÔNG BÁO THÀNH CÔNG / LỖI --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show small mb-3" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show small mb-3" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

{{-- KIỂM TRA XEM ĐÃ CHỌN LỚP HỌC CHƯA --}}
@if(!$selectedClass)
    <div class="alert alert-warning text-center small shadow-sm">
        <i class="bi bi-exclamation-circle me-2"></i> Vui lòng chọn một lớp học từ danh sách để quản lý tài liệu.
    </div>
@else

    {{-- SUB-MENU CHUYỂN TAB TÀI LIỆU --}}
    <div class="d-flex justify-content-start gap-2 mb-4 border-bottom pb-3">
        <button id="tabListBtn" onclick="toggleDocTab('list')" class="btn btn-sm px-4 fw-bold shadow-sm" style="background-color: #FDBA74; color: #7C2D12; border: 1px solid #F97316;">
            <i class="bi bi-folder2-open"></i> Danh sách tài liệu hiện có ({{ $stats['total'] ?? 0 }})
        </button>
        <button id="tabUploadBtn" onclick="toggleDocTab('upload')" class="btn btn-sm px-4 fw-bold shadow-sm" style="background-color: #FEE2E2; color: #990000; border: 1px solid #FCA5A5;">
            <i class="bi bi-cloud-upload"></i> Tải lên tài liệu mới
        </button>
    </div>

    {{-- PHÂN HỆ 1: DANH SÁCH TÀI LIỆU HIỆN CÓ --}}
    <div id="docListSection">
        <div class="table-responsive shadow-sm border rounded">
            <table class="table m-0 text-center align-middle table-hover">
                <thead style="background-color: #800000; color: white; font-size: 13px;">
                    <tr>
                        <th style="width: 60px; padding: 12px;">#</th>
                        <th class="text-start ps-4">Tên tài liệu / Tiêu đề</th>
                        <th>Loại file</th>
                        <th>Ngày đăng tải</th>
                        <th style="width: 220px;">Hành động</th>
                    </tr>
                </thead>
                <tbody style="font-size: 14px;">
                    @forelse (($materials ?? []) as $index => $material)
                        @php
                            // Tự động phân tích đuôi file để hiển thị icon và badge tương ứng giống Controller
                            $ext = strtolower(pathinfo($material->file_path, PATHINFO_EXTENSION));
                            $icon = 'bi-file-earmark-text text-secondary';
                            $badgeClass = 'bg-light text-dark';
                            $typeName = strtoupper($ext);

                            if ($ext === 'pdf') {
                                $icon = 'bi-file-earmark-pdf text-danger';
                                $badgeClass = 'bg-danger-subtle text-danger';
                                $typeName = 'PDF';
                            } elseif (in_array($ext, ['doc', 'docx'])) {
                                $icon = 'bi-file-earmark-word text-primary';
                                $badgeClass = 'bg-primary-subtle text-primary';
                                $typeName = 'Word';
                            } elseif (in_array($ext, ['ppt', 'pptx'])) {
                                $icon = 'bi-file-earmark-ppt text-warning';
                                $badgeClass = 'bg-warning-subtle text-warning';
                                $typeName = 'PowerPoint';
                            } elseif (in_array($ext, ['xls', 'xlsx'])) {
                                $icon = 'bi-file-earmark-excel text-success';
                                $badgeClass = 'bg-success-subtle text-success';
                                $typeName = 'Excel';
                            }
                        @endphp
                        <tr>
                            {{-- Tính số thứ tự chuẩn khi có phân trang --}}
                            <td>{{ method_exists($materials, 'firstItem') ? ($materials->firstItem() + $index) : ($index + 1) }}</td>
                            
                            {{-- Tên tài liệu --}}
                            <td class="text-start ps-4 fw-semibold text-dark">
                                <i class="bi {{ $icon }} me-2"></i>{{ $material->title }}
                            </td>
                            
                            {{-- Loại tài liệu --}}
                            <td><span class="badge {{ $badgeClass }} border">{{ $typeName }}</span></td>
                            
                            {{-- Ngày upload --}}
                            <td>{{ $material->created_at->format('d/m/Y H:i') }}</td>
                            
                            {{-- Các hành động hành vi yêu cầu --}}
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    {{-- 1. Chi tiết (Mở xem trực tiếp file trên trình duyệt nếu là PDF/Hình ảnh) --}}
                                    <a href="{{ Storage::url($material->file_path) }}" target="_blank" class="btn btn-xs btn-link text-info fw-bold p-0 text-decoration-none">
                                        Chi tiết
                                    </a>
                                    
                                    <span class="text-muted">|</span>

                                    {{-- 2. Tải xuống --}}
                                    <a href="{{ route('teacher.materials.download', $material->id) }}" class="btn btn-xs btn-link text-primary fw-bold p-0 text-decoration-none">
                                        Tải xuống
                                    </a>
                                    
                                    <span class="text-muted">|</span>

                                    {{-- 3. Xóa (Dùng form POST kèm @method('DELETE') để đúng chuẩn bảo mật RESTful) --}}
                                    <form action="{{ route('teacher.materials.destroy', $material->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa tài liệu này?')" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        {{-- Giữ lại tham số tìm kiếm/lọc sau khi xóa để không mất bộ lọc hiện tại --}}
                                        <input type="hidden" name="search" value="{{ $search }}">
                                        <input type="hidden" name="type" value="{{ $filterType }}">
                                        
                                        <button type="submit" class="btn btn-xs btn-link text-danger fw-bold p-0 border-0 bg-transparent">
                                            Xóa
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-muted py-4">
                                <i class="bi bi-folder-x display-6 d-block mb-2 text-secondary"></i>
                                Chưa có tài liệu nào trong lớp học này.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- HIỂN THỊ PHÂN TRANG (PAGINATION) --}}
        @if(method_exists($materials, 'links'))
            <div class="mt-3">
                {{ $materials->links() }}
            </div>
        @endif
    </div>

    {{-- PHÂN HỆ 2: FORM THÊM TÀI LIỆU MỚI --}}
    <div id="docUploadSection" style="display: none;">
        <div class="card p-4 shadow-sm border-0 mx-auto ms-0" style="max-width: 650px; background-color: #FFFDF0; border: 1px solid #E2E8F0 !important;">
            <h6 class="fw-bold text-dark mb-4 border-bottom pb-2"><i class="bi bi-upload me-2"></i>THÊM TÀI LIỆU CHO LỚP HỌC</h6>
            
            <form action="{{ route('teacher.materials.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                {{-- Gửi ngầm class_id để Controller biết tài liệu này thuộc lớp nào --}}
                <input type="hidden" name="class_id" value="{{ $selectedClass->id }}">
                <input type="hidden" name="search" value="{{ $search }}">
                <input type="hidden" name="type" value="{{ $filterType }}">

                <div class="mb-3 small">
                    <label class="form-label fw-bold text-dark mb-1">Tiêu đề hiển thị <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control form-control-sm @error('title') is-invalid @enderror" style="background-color: #FFF;" placeholder="Ví dụ: Đề cương ôn tập kỹ năng Nói..." value="{{ old('title') }}" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4 small">
                    <label class="form-label fw-bold text-dark mb-1">Chọn tệp tài liệu <span class="text-danger">*</span></label>
                    <input type="file" name="file" class="form-control form-control-sm @error('file') is-invalid @enderror" style="background-color: #FFF;" required>
                    <div class="form-text text-muted" style="font-size: 11px;">Hỗ trợ: PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX (Tối đa 20MB)</div>
                    @error('file')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="text-start">
                    <button type="submit" class="btn btn-sm fw-bold px-4 text-white shadow-sm" style="background-color: #800000;">
                        XÁC NHẬN TẢI LÊN
                    </button>
                </div>
            </form>
        </div>
    </div>

@endif

<script>
    function toggleDocTab(targetTab) {
        const listBtn = document.getElementById('tabListBtn');
        const uploadBtn = document.getElementById('tabUploadBtn');
        const listSec = document.getElementById('docListSection');
        const uploadSec = document.getElementById('docUploadSection');

        if(!listBtn || !uploadBtn || !listSec || !uploadSec) return;

        if(targetTab === 'list') {
            listBtn.style = "background-color: #FDBA74; color: #7C2D12; border: 1px solid #F97316;";
            uploadBtn.style = "background-color: #FEE2E2; color: #990000; border: 1px solid #FCA5A5;";
            listSec.style.display = 'block';
            uploadSec.style.display = 'none';
        } else {
            uploadBtn.style = "background-color: #FDBA74; color: #7C2D12; border: 1px solid #F97316;";
            listBtn.style = "background-color: #FEE2E2; color: #990000; border: 1px solid #FCA5A5;";
            listSec.style.display = 'none';
            uploadSec.style.display = 'block';
        }
    }
</script>
@endsection