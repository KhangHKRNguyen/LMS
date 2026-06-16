@extends('layouts.admin')

@section('title', 'Chi tiết thành viên lớp học')

@section('admin_content')
<div class="container-fluid py-4">

    {{-- CONTAINER 1: DANH SÁCH THÀNH VIÊN LỚP --}}
    <div id="memberListContainer" style="{{ isset($previewMembers) ? 'display: none;' : 'display: block;' }}">
        <div class="mb-4">
            <a href="{{ route('admin.classes.index') }}" class="text-decoration-none text-secondary fw-medium">
                <i class="bi bi-arrow-left"></i> QUẢN LÝ LỚP HỌC - {{ $class->class_name }}
            </a>
        </div>

        <div class="text-center mb-4">
            <h4 class="fw-bold" style="letter-spacing: 0.5px; color: #990000 !important;">CHI TIẾT THÀNH VIÊN LỚP HỌC</h4>
        </div>

        <div class="card border-0 shadow-sm mb-4" style="border-radius: 8px;">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('admin.classes.members', $class->id) }}" class="row g-2 align-items-center">
                    <div class="col-md-4 position-relative">
                        <span class="position-absolute top-50 start-0 translate-middle-y ps-3 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" value="{{ $search ?? request('search') }}" placeholder="Tìm theo mã hoặc tên..." class="form-control ps-5 border-secondary-subtle" style="height: 42px; border-radius: 6px;">
                    </div>
                    <div class="col-md-auto">
                        <button type="submit" class="btn btn-dark fw-semibold px-4" style="height: 42px; border-radius: 6px;">Lọc</button>
                        @if(($search ?? request('search')))
                            <a href="{{ route('admin.classes.members', $class->id) }}" class="btn btn-light border ms-1 fw-semibold text-secondary" style="height: 42px; border-radius: 6px;">Xóa bộ lọc</a>
                        @endif
                    </div>
                    <div class="col-md-auto ms-auto">
                        <button type="button" onclick="switchToAction('add')" class="btn fw-semibold shadow-sm" style="background-color: #FEE2E2; border: 1px solid #FCA5A5; color: #990000; padding: 8px 24px; border-radius: 4px;">
                            Thêm thành viên
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="table-responsive shadow-sm" style="border-radius: 8px;">
            <table class="table m-0 text-center align-middle bg-white table-bordered">
                <thead style="background-color: #990000; color: white;">
                    <tr>
                        <th style="width: 60px; padding: 12px;">#</th>
                        <th>Mã thành viên</th>
                        <th>Họ tên</th>
                        <th>Chức vụ / Vai trò</th>
                        <th style="width: 150px;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($members ?? [] as $index => $member)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td><span class="fw-bold text-dark">{{ $member->id }}</span></td>
                            <td>{{ $member->name }}</td>
                            <td>
                                <span class="badge {{ $member->role === 'teacher' ? 'bg-danger' : ($member->role === 'ta' ? 'bg-warning text-dark' : 'bg-primary') }}">
                                    {{ $member->role_text }}
                                </span>
                            </td>
                            <td>
                                <form action="{{ route('admin.classes.members.remove', [$class->id, $member->id]) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa thành viên này khỏi lớp?')">
                                    @csrf 
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm fw-bold px-4 text-white btn-danger" style="border-radius: 4px; border: none;">Xóa</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-4 text-muted">Lớp học hiện chưa có thành viên nào gán vào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>


    {{-- CONTAINER 2: VIEW CHỨC NĂNG THÊM THÀNH VIÊN --}}
    <div id="addMemberContainer" style="{{ isset($previewMembers) ? 'display: block;' : 'display: none;' }}">
        <div class="mb-4">
            <button onclick="triggerBackWithCheck()" class="btn btn-link text-decoration-none text-secondary p-0 fw-medium">
                <i class="bi bi-arrow-left"></i> Quay về danh sách
            </button>
        </div>

        <div class="text-center mb-4">
            <h4 class="fw-bold mb-1" style="color: #990000 !important;">THÊM THÀNH VIÊN VÀO LỚP</h4>
            <p class="text-muted fw-semibold">Lớp học: {{ $class->class_name }}</p>
        </div>

        <div class="d-flex justify-content-center gap-2 mb-4">
            <button id="tabSingleBtn" onclick="switchTab('single')" class="btn px-4 fw-bold shadow-sm" style="{{ isset($previewMembers) ? 'background-color: #FEE2E2; color: #990000; border: 1px solid #FCA5A5;' : 'background-color: #FDBA74; color: #7C2D12; border: 1px solid #F97316;' }}">
                Thêm từng thành viên
            </button>
            <button id="tabBulkBtn" onclick="switchTab('bulk')" class="btn px-4 fw-bold shadow-sm" style="{{ isset($previewMembers) ? 'background-color: #FDBA74; color: #7C2D12; border: 1px solid #F97316;' : 'background-color: #FEE2E2; color: #990000; border: 1px solid #FCA5A5;' }}">
                Thêm hàng loạt (Excel)
            </button>
        </div>

        {{-- TAB 1: THÊM TỪNG THÀNH VIÊN --}}
        <div id="tabSingleContent" class="mx-auto card p-4 shadow-sm border-0" style="max-width: 500px; background-color: #FFFBEB; {{ isset($previewMembers) ? 'display: none;' : 'display: block;' }}">
            <form action="{{ route('admin.classes.members.add_single', $class->id) }}" method="POST">
                @csrf
                <div class="mb-3 position-relative">
                    <label for="singleMemberCode" class="form-label fw-semibold text-secondary">Nhập mã định danh tài khoản <span class="text-danger">*</span></label>
                    <input type="text" 
                        class="form-control" 
                        id="singleMemberCode" 
                        name="user_id" 
                        placeholder="Tên hoặc Email để tìm kiếm..." 
                        autocomplete="off" 
                        style="height: 44px;">
                    
                    {{-- Danh sách kết quả gợi ý (Mới thêm) --}}
                    <div id="searchSuggestions" class="list-group shadow-sm position-absolute w-100 mt-1 d-none" style="z-index: 1050; max-height: 250px; overflow-y: auto;">
                        </div>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn text-white fw-bold px-4 shadow-sm" style="background-color: #990000; height: 42px;">Xác nhận thêm</button>
                </div>
            </form>
        </div>

        {{-- TAB 2: THÊM HÀNG LOẠT --}}
        <div id="tabBulkContent" style="{{ isset($previewMembers) ? 'display: block;' : 'display: none;' }}">
            <div class="card p-4 shadow-sm border-0 mx-auto mb-4" style="max-width: 650px; background-color: #F8FAFC;">
                <form action="{{ route('admin.classes.members.preview', $class->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="d-flex align-items-center gap-3 justify-content-center mb-3">
                        <input type="file" name="import_file" class="form-control" style="max-width: 400px; height: 42px;" required accept=".xlsx, .csv, .txt">
                        <button type="submit" class="btn text-white fw-bold px-4" style="background-color: #990000; height: 42px;">Tải Lên</button>
                    </div>
                    <div class="text-center">
                        <a href="{{ route('admin.classes.members.sample') }}" class="btn btn-sm btn-outline-dark fw-semibold px-4 py-2">
                            <i class="bi bi-download"></i> Tải file Excel mẫu tại đây
                        </a>
                    </div>
                </form>
            </div>

            {{-- HIỂN THỊ DANH SÁCH XEM TRƯỚC TỪ EXCEL --}}
            @if(isset($previewMembers))
            <div class="mt-4">
                <div class="text-center mb-3">
                    <h6 class="fw-bold text-danger" style="letter-spacing: 0.5px;">DANH SÁCH THÀNH VIÊN TRONG FILE XEM TRƯỚC</h6>
                </div>
                <div class="table-responsive shadow-sm rounded mb-4">
                    <table class="table m-0 text-center align-middle bg-white table-bordered">
                        <thead class="table-red">
                            <tr>
                                <th>#</th>
                                <th>Mã tài khoản</th>
                                <th>Họ tên</th>
                                <th>Vai trò hệ thống</th>
                                <th>Trạng thái kiểm tra</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($previewMembers as $index => $pMem)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="fw-bold text-primary">{{ $pMem['id'] ?? '' }}</td>
                                <td>{{ $pMem['name'] }}</td>
                                <td>{{ $pMem['role_text'] }}</td>
                                <td>
                                    @if($pMem['is_valid'])
                                        <span class="text-success fw-bold"><i class="bi bi-check-circle-fill"></i> Hợp lệ</span>
                                    @else
                                        <span class="text-danger fw-bold"><i class="bi bi-x-circle-fill"></i> {{ $pMem['status_text'] }}</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="text-center mb-5">
                    <form action="{{ route('admin.classes.members.store_bulk', $class->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="verified_data" value="{{ json_encode($previewMembers) }}">
                        <button type="submit" class="btn btn-success fw-bold px-5 shadow-sm" style="height: 44px;">XÁC NHẬN LƯU VÀO LỚP HỌC</button>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- MODAL CẢNH BÁO KHI THOÁT --}}
<div id="confirmExitModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); z-index: 9999; justify-content: center; align-items: center;">
    <div class="bg-white p-4 text-center rounded border" style="width: 440px; box-shadow: 0 4px 20px rgba(0,0,0,0.15);">
        <h5 class="fw-bold text-dark mb-4">Bạn có chắc chắn muốn hủy thao tác và quay lại danh sách?</h5>
        <div class="d-flex justify-content-center gap-3">
            <button onclick="forceExit()" class="btn btn-danger text-white px-4">Có, Thoát</button>
            <button onclick="closeExitModal()" class="btn btn-light border px-4">Không</button>
        </div>
    </div>
</div>

<script>
    function switchToAction(mode) {
        if(mode === 'add') {
            document.getElementById('memberListContainer').style.display = 'none';
            document.getElementById('addMemberContainer').style.display = 'block';
        } else {
            document.getElementById('addMemberContainer').style.display = 'none';
            document.getElementById('memberListContainer').style.display = 'block';
            window.location.href = "{{ route('admin.classes.members', $class->id) }}";
        }
    }

    function switchTab(tab) {
        const singleBtn = document.getElementById('tabSingleBtn');
        const bulkBtn = document.getElementById('tabBulkBtn');
        const singleContent = document.getElementById('tabSingleContent');
        const bulkContent = document.getElementById('tabBulkContent');

        if(tab === 'single') {
            singleBtn.style = "background-color: #FDBA74; color: #7C2D12; border: 1px solid #F97316;";
            bulkBtn.style = "background-color: #FEE2E2; color: #990000; border: 1px solid #FCA5A5;";
            singleContent.style.display = 'block';
            bulkContent.style.display = 'none';
        } else {
            bulkBtn.style = "background-color: #FDBA74; color: #7C2D12; border: 1px solid #F97316;";
            singleBtn.style = "background-color: #FEE2E2; color: #990000; border: 1px solid #FCA5A5;";
            singleContent.style.display = 'none';
            bulkContent.style.display = 'block';
        }
    }

    function triggerBackWithCheck() {
        const singleInput = document.getElementById('singleMemberCode') ? document.getElementById('singleMemberCode').value.trim() : "";
        if(singleInput !== "") {
            document.getElementById('confirmExitModal').style.display = 'flex';
        } else {
            switchToAction('list');
        }
    }

    function closeExitModal() {
        document.getElementById('confirmExitModal').style.display = 'none';
    }

    function forceExit() {
        if(document.getElementById('singleMemberCode')) document.getElementById('singleMemberCode').value = "";
        closeExitModal();
        switchToAction('list');
    }

    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('singleMemberCode');
        const suggestionsBox = document.getElementById('searchSuggestions');
        let debounceTimeout = null;

        if (searchInput && suggestionsBox) {
            searchInput.addEventListener('input', function() {
                const query = this.value.trim();
                
                // Xóa thời gian chờ cũ nếu người dùng đang gõ liên tục để tránh spam request
                clearTimeout(debounceTimeout);

                if (query.length < 2) {
                    suggestionsBox.classList.add('d-none');
                    suggestionsBox.innerHTML = '';
                    return;
                }

                // Chờ người dùng dừng gõ phím 300ms rồi mới gửi request đi
                debounceTimeout = setTimeout(() => {
                    const searchUrl = "{{ route('admin.classes.search_users', $class->id) }}?q=" + encodeURIComponent(query);

                    fetch(searchUrl)
                        .then(response => response.json())
                        .then(users => {
                            suggestionsBox.innerHTML = '';

                            if (users.length === 0) {
                                suggestionsBox.innerHTML = `<div class="list-group-item text-muted py-2 small">Không tìm thấy tài khoản thích hợp hoặc đã có trong lớp</div>`;
                                suggestionsBox.classList.remove('d-none');
                                return;
                            }

                            // Đổ dữ liệu tìm kiếm được vào dropdown danh sách
                            users.forEach(user => {
                                const item = document.createElement('a');
                                item.href = '#';
                                item.className = 'list-group-item list-group-item-action py-2 d-flex justify-content-between align-items-center';
                                item.innerHTML = `
                                    <div>
                                        <strong class="text-dark d-block mb-0">${user.name}</strong>
                                        <span class="text-muted small">${user.email}</span>
                                    </div>
                                    <span class="badge bg-secondary font-monospace">${user.id}</span>
                                `;

                                // Khi người dùng bấm chuột chọn 1 kết quả cụ thể
                                item.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    searchInput.value = user.id; // Tự động điền Mã tài khoản vào ô input
                                    suggestionsBox.classList.add('d-none'); // Ẩn danh sách gợi ý đi
                                });

                                suggestionsBox.appendChild(item);
                            });

                            suggestionsBox.classList.remove('d-none');
                        })
                        .catch(error => console.error('Lỗi tìm kiếm:', error));
                }, 300);
            });

            // Ẩn danh sách gợi ý nếu người dùng click chuột ra vùng bên ngoài
            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
                    suggestionsBox.classList.add('d-none');
                }
            });
        }
    });
</script>
@endsection