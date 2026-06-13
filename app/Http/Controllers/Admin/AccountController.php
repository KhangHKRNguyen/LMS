<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AccountImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    protected $importService;

    // Sử dụng Dependency Injection để nạp Service xử lý file vào Controller
    public function __construct(AccountImportService $importService)
    {
        $this->importService = $importService;
    }

    // GET /admin/accounts - Danh sách tài khoản
    public function index(Request $request)
    {
        // 1. Lấy từ khóa tìm kiếm (nếu có) từ form tìm kiếm ở giao diện
        $search = $request->input('search');

        // 2. Query lọc danh sách tài khoản từ bảng users
        $query = User::query();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('id', 'LIKE', "%{$search}%"); // Hoặc user_code tùy cấu trúc bảng
            });
        }

        // 3. Phân trang và ĐẶT TÊN BIẾN LÀ $accounts
        $accounts = $query->orderBy('created_at', 'desc')->paginate(15);

        // 4. TRUYỀN BIẾN $accounts SANG VIEW (Sử dụng compact('accounts'))
        return view('admin.accounts.index', compact('accounts'));
    }

    // GET /admin/accounts/create - Giao diện thêm mới 1 tài khoản thủ công
    public function create()
    {
        return view('admin.accounts.create');
    }

    // POST /admin/accounts - Lưu tài khoản thêm thủ công
    public function store(Request $request)
    {
        // 1. Chỉ validate nghiêm ngặt đúng 3 trường được gửi lên
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'role'  => 'required|string|in:admin,teacher,student,ta',
        ]);

        // 2. Thực hiện tạo tài khoản với mật khẩu mặc định khởi tạo ban đầu và trạng thái hoạt động luôn
        User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'role'     => $validated['role'],
            'password' => Hash::make('12345678'),   // Đặt mật khẩu mẫu cố định (Ví dụ: 12345678)
            'status'   => User::STATUS_ACTIVE,      // Tự động kích hoạt trạng thái hoạt động 'active'
        ]);

        // 3. Điều hướng về trang danh sách kèm thông báo xanh
        return redirect()->route('admin.accounts.index')->with('success', 'Đã khởi tạo tài khoản thành công!');
    }

    // GET /admin/accounts/{account} - Xem chi tiết 1 tài khoản
    public function show(User $account)
    {
        return view('admin.accounts.show', compact('account'));
    }

    // GET /admin/accounts/{account}/edit - Giao diện sửa tài khoản
    public function edit(User $account)
    {
        return view('admin.accounts.edit', compact('account'));
    }

    // PUT/PATCH /admin/accounts/{account} - Cập nhật thông tin tài khoản
    public function update(Request $request, User $account)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $account->id,
            'role'  => 'required|string|in:admin,teacher,student,ta',
        ]);

        $account->update($validated);

        // Nếu có đổi mật khẩu mới thì cập nhật
        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:6|confirmed']);
            $account->update(['password' => Hash::make($request->password)]);
        }

        return redirect()->route('admin.accounts.index')->with('success', 'Cập nhật tài khoản thành công.');
    }

    // DELETE /admin/accounts/{account} - Xóa tài khoản
    public function destroy(User $account)
    {
        $account->delete();
        return redirect()->route('admin.accounts.index')->with('success', 'Xóa tài khoản thành công.');
    }


    // GET /admin/accounts/sample - Tải file mẫu CSV về máy
    public function sample()
    {
        $csvContent = $this->importService->sampleCsvContent();

        return response($csvContent)
            ->header('Content-Type', 'text/csv; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="mau_import_tai_khoan.csv"');
    }

    // POST /admin/accounts/preview - Đọc file và hiển thị lên bảng cho Admin soát lỗi
    public function preview(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:csv,txt,xlsx|max:5120', // Giới hạn file tối đa 5MB
        ]);

        try {
            // Gọi tầng Service xử lý đọc dữ liệu thô từ file
            $accounts = $this->importService->importAccounts($request->file('import_file'));
            
            // Trả dữ liệu ra view preview để admin xem trước dữ liệu dạng Table
            return view('admin.accounts.preview', compact('accounts'));
        } catch (\Exception $e) {
            return back()->withErrors(['import_file' => $e->getMessage()]);
        }
    }

    // POST /admin/accounts/store-bulk - Lưu hàng loạt danh sách tài khoản đã duyệt vào DB
    public function store_bulk(Request $request)
    {
        $request->validate([
            'accounts'          => 'required|array',
            'accounts.*.id'     => 'required|string', // Mã sinh viên / Giảng viên
            'accounts.*.name'   => 'required|string|max:255',
            'accounts.*.email'  => 'required|email',
            'accounts.*.role'   => 'required|string|in:admin,teacher,student,ta',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->accounts as $accountData) {
                // Sử dụng updateOrCreate để tránh lỗi trùng lặp Email nếu chạy import nhiều lần
                User::updateOrCreate(
                    ['email' => $accountData['email']],
                    [             
                        'name'     => $accountData['name'],
                        'role'     => $accountData['role'],
                        'password' => Hash::make('12345678'),
                    ]
                );
            }
            DB::commit();

            return redirect()->route('admin.accounts.index')->with('success', 'Đã import thành công danh sách tài khoản!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.accounts.index')->with('error', 'Có lỗi xảy ra khi lưu: ' . $e->getMessage());
        }
    }

    public function toggleStatus(User $user)
    {
        // Kiểm tra trạng thái hiện tại và đảo ngược lại dựa vào hằng số trong model User
        if ($user->status === User::STATUS_ACTIVE) {
            $user->status = User::STATUS_INACTIVE;
            $message = 'Đã khóa tài khoản thành công.';
        } else {
            $user->status = User::STATUS_ACTIVE;
            $message = 'Đã mở khóa tài khoản thành công.';
        }

        $user->save();

        return redirect()->back()->with('success', $message);
    }
}