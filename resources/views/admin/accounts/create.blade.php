@extends('layouts.admin')

@section('title', 'Tạo tài khoản người dùng')

@section('admin_content')
@php
    $showBulkTab = (!empty($previewUsers) || $errors->has('import_file'));
@endphp

<div class="mb-4">
    <a href="{{ route('admin.accounts.index') }}" class="text-decoration-none text-secondary fw-medium">
        <i class="bi bi-arrow-left"></i> Quay lại danh sách tài khoản
    </a>
</div>

<div class="card shadow-sm border-0 mx-auto" style="max-width: 900px; border-radius: 8px;">
    <div class="card-body p-4">
        
        <ul class="nav nav-pills mb-4 d-flex justify-content-center gap-3" id="accountTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ !$showBulkTab ? 'active' : '' }} fw-bold px-4 py-2 text-dark border bg-light" id="single-tab" data-bs-toggle="tab" data-bs-target="#single-pane" type="button" role="tab" style="border-radius: 4px;">
                    Tạo tài khoản lẻ
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $showBulkTab ? 'active' : '' }} fw-bold px-4 py-2 text-dark border bg-light" id="bulk-tab" data-bs-toggle="tab" data-bs-target="#bulk-pane" type="button" role="tab" style="border-radius: 4px;">
                    Tạo hàng loạt bằng file
                </button>
            </li>
        </ul>

        <div class="tab-content pt-3" id="accountTabContent">
            
            {{-- Vùng nội dung Tab Lẻ --}}
            <div class="tab-pane fade {{ !$showBulkTab ? 'show active' : '' }}" id="single-pane" role="tabpanel" aria-labelledby="single-tab">
                <form method="POST" action="{{ route('admin.accounts.store') }}">
                    @csrf

                    <div class="row g-3 mb-4">
                        {{-- Họ và Tên --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Họ và Tên <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" placeholder="Nhập họ và tên đầy đủ" style="height: 44px; border-radius: 6px;" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Email đăng nhập --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Email đăng nhập <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" placeholder="vi_du@gmail.com" style="height: 44px; border-radius: 6px;" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Vai trò hệ thống --}}
                        <div class="col-md-12">
                            <label class="form-label fw-semibold text-secondary">Vai trò thành viên <span class="text-danger">*</span></label>
                            <select class="form-select @error('role') is-invalid @enderror" name="role" style="height: 44px; border-radius: 6px;" required>
                                <option value="" disabled selected>-- Lựa chọn vai trò phù hợp --</option>
                                <option value="student" {{ old('role') == 'student' ? 'selected' : '' }}>Học viên (Student)</option>
                                <option value="teacher" {{ old('role') == 'teacher' ? 'selected' : '' }}>Giảng viên (Teacher)</option>
                                <option value="ta" {{ old('role') == 'ta' ? 'selected' : '' }}>Trợ giảng (TA)</option>
                                <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Quản trị viên (Admin)</option>
                            </select>
                            @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    {{-- Khối nút hành động --}}
                    <div class="text-end border-top pt-3">
                        <a href="{{ route('admin.accounts.index') }}" class="btn btn-light border fw-bold px-4 me-2" style="height: 44px; line-height: 30px; border-radius: 6px;">HỦY</a>
                        <button type="submit" class="btn text-white fw-bold px-4" style="background-color: var(--primary-color); height: 44px; border-radius: 6px;">TẠO TÀI KHOẢN</button>
                    </div>
                </form>
            </div>

            {{-- Vùng nội dung Tab Hàng loạt --}}
            <div class="tab-pane fade {{ $showBulkTab ? 'show active' : '' }}" id="bulk-pane" role="tabpanel" aria-labelledby="bulk-tab">
                
                {{-- Khối thông báo lỗi cục bộ của File --}}
                @error('import_file')
                    <div class="alert alert-danger mb-3">{{ $message }}</div>
                @enderror

                <div class="p-4 mb-4 style-bg-sample" style="background-color: #FFFDF0; border: 1px dashed #E2E8F0; border-radius: 6px;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="fw-bold text-dark m-0">Tải tệp tin mẫu định dạng hệ thống chuẩn</h6>
                            <small class="text-muted">Vui lòng điền đúng các cột thông tin bắt buộc trước khi tải lên hệ thống.</small>
                        </div>
                        <a href="{{ route('admin.accounts.sample') }}" class="btn btn-sm btn-outline-danger fw-bold px-3">
                            <i class="bi bi-download"></i> Tải file mẫu .csv
                        </a>
                    </div>
                </div>

                {{-- Form tải file lên để Đọc và Preview dữ liệu --}}
                <form action="{{ route('admin.accounts.preview') }}" method="POST" enctype="multipart/form-data" class="mb-5">
                    @csrf
                    <div class="d-flex gap-2">
                        <input type="file" class="form-control" name="import_file" accept=".xlsx, .csv, .txt" style="height: 44px;" required>
                        <button type="submit" class="btn text-white fw-bold px-4" style="background-color: var(--primary-color); white-space: nowrap;">TIẾP TỤC</button>
                    </div>
                </form>

                {{-- Chỉ hiển thị bảng nếu thực sự tồn tại biến mảng $previewUsers được truyền sang --}}
                @if(!empty($previewUsers) && count($previewUsers) > 0) 
                <div class="mt-4">
                    <h5 class="fw-bold mb-3 text-secondary">Danh sách tài khoản xem trước trước khi lưu</h5>
                    <div class="table-responsive shadow-sm mb-4" style="max-height: 400px; overflow-y: auto;">
                        <table class="table-arena m-0">
                            <thead>
                                <tr>
                                    <th>Mã số (ID)</th>
                                    <th>Họ và Tên</th>
                                    <th>Email đăng nhập</th>
                                    <th>Vai trò nhận diện</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($previewUsers as $pUser)
                                <tr>
                                    <td><span class="fw-bold text-primary">{{ $pUser['id'] ?? '' }}</span></td>
                                    <td>{{ $pUser['name'] }}</td>
                                    <td>{{ $pUser['email'] }}</td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $pUser['role'] }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Form cuối gửi dữ liệu thô dạng JSON lên Database --}}
                    <form action="{{ route('admin.accounts.store_bulk') }}" method="POST" class="text-end">
                        @csrf
                        <input type="hidden" name="verified_data" value="{{ json_encode($previewUsers) }}">
                        <button type="submit" class="btn text-white fw-bold px-5" style="background-color: var(--primary-color); height: 44px;">XÁC NHẬN TẠO HÀNG LOẠT</button>
                    </form>
                </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection