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

    // ĐÃ SỬA: Loại bỏ "= null" để ép Laravel tự động Inject Service này vào Container chuẩn chỉ
    public function __construct(AccountImportService $importService)
    {
        $this->importService = $importService;
    }

    // GET /admin/accounts - Danh sách tài khoản
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = User::query();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('id', 'LIKE', "%{$search}%");
            });
        }

        // Tải kèm roleRelation để tối ưu hiển thị danh sách tài khoản
        $accounts = $query->with('roleRelation')->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.accounts.index', compact('accounts'));
    }

    // GET /admin/accounts/create - Giao diện thêm mới tài khoản
    public function create()
    {
        $previewUsers = []; // Tránh lỗi biến preview không tồn tại
        return view('admin.accounts.create', compact('previewUsers'));
    }

    // POST /admin/accounts - Lưu tài khoản thêm lẻ thủ công
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'role'  => 'required|string|in:admin,teacher,student,ta,assistant',
        ]);

        // Ánh xạ chuỗi từ giao diện sang ID số của Seeder
        $roleMap = [
            'admin'     => 1,
            'teacher'   => 2,
            'assistant' => 3,
            'ta'        => 3,
            'student'   => 4,
        ];

        User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'role_id'  => $roleMap[$validated['role']] ?? 4,
            'password' => Hash::make('12345678'), // Mật khẩu mặc định hệ thống
            'status'   => User::STATUS_ACTIVE,
        ]);

        return redirect()->route('admin.accounts.index')->with('success', 'Đã khởi tạo tài khoản thành công!');
    }

    // POST /admin/accounts/store-bulk - Xác nhận tạo hàng loạt từ file JSON
    public function storeBulk(Request $request)
    {
        $accountsData = [];
        if ($request->has('verified_data')) {
            $accountsData = json_decode($request->input('verified_data'), true) ?? [];
        } elseif ($request->has('accounts')) {
            $accountsData = $request->input('accounts');
        }

        if (empty($accountsData)) {
            return redirect()->route('admin.accounts.index')->with('error', 'Dữ liệu danh sách trống, không thể lưu!');
        }

        $roleMap = [
            'admin'     => 1,
            'teacher'   => 2,
            'assistant' => 3,
            'ta'        => 3,
            'student'   => 4,
        ];

        DB::beginTransaction();
        try {
            foreach ($accountsData as $accountData) {
                if (empty($accountData['email'])) continue;

                User::updateOrCreate(
                    ['email' => $accountData['email']],
                    [             
                        'name'     => $accountData['name'] ?? 'Thành viên mới',
                        'role_id'  => $roleMap[$accountData['role'] ?? 'student'] ?? 4,
                        'password' => Hash::make('12345678'),
                        'status'   => User::STATUS_ACTIVE,
                    ]
                );
            }
            DB::commit();

            return redirect()->route('admin.accounts.index')->with('success', 'Đã import thành công danh sách tài khoản!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.accounts.index')->with('error', 'Lỗi hệ thống khi lưu hàng loạt: ' . $e->getMessage());
        }
    }

    // GET /admin/accounts/{account}/edit - Giao diện sửa thông tin tài khoản
    public function edit(User $account)
    {
        return view('admin.accounts.edit', compact('account'));
    }

    // PUT/PATCH /admin/accounts/{account} - Cập nhật dữ liệu tài khoản
    public function update(Request $request, User $account)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $account->id,
            'role'  => 'required|string|in:admin,teacher,student,ta,assistant',
        ]);

        $roleMap = [
            'admin'     => 1,
            'teacher'   => 2,
            'assistant' => 3,
            'ta'        => 3,
            'student'   => 4,
        ];

        $account->update([
            'name'    => $validated['name'],
            'email'   => $validated['email'],
            'role_id' => $roleMap[$validated['role']] ?? 4,
        ]);

        return redirect()->route('admin.accounts.index')->with('success', 'Cập nhật tài khoản thành công!');
    }

    // DELETE /admin/accounts/{account} - Xóa tài khoản khỏi hệ thống
    public function destroy(User $account)
    {
        if ($account->id === auth()->id()) {
            return redirect()->route('admin.accounts.index')->with('error', 'Bạn không thể tự xóa chính mình.');
        }

        $account->delete();
        return redirect()->route('admin.accounts.index')->with('success', 'Xóa tài khoản thành công!');
    }

    // PATCH /admin/accounts/{user}/toggle-status - Thay đổi Trạng thái Khóa/Mở khóa
    public function toggleStatus(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.accounts.index')->with('error', 'Bạn không thể tự khóa chính mình.');
        }

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

    // GET /admin/accounts/sample - Tải file mẫu cấu hình chuẩn hệ thống
    public function sample()
    {
        $headers = [
            "Content-type"        => "text/csv; charset=utf-8",
            "Content-Disposition" => "attachment; filename=mau_tai_khoan_he_thong.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        // ĐÃ SỬA: Thêm cột tiêu đề 'id' vào đầu mảng để đồng bộ tuyệt đối với AccountImportService
        $columns = ['id', 'name', 'email', 'role'];

        $callback = function() use($columns) {
            $file = fopen('php://output', 'w');
            // Thêm BOM để Excel không bị lỗi font tiếng Việt khi đọc CSV
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); 
            
            fputcsv($file, $columns);
            
            // ĐÃ SỬA: Thêm giá trị mã định danh mẫu (ID) vào các dòng dữ liệu ví dụ
            fputcsv($file, ['M008821', 'Nguyễn Văn Học Viên', 'hocvien@example.com', 'student']);
            fputcsv($file, ['M002174', 'Trần Thị Giảng Viên', 'giangvien@example.com', 'teacher']);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // POST /admin/accounts/preview - Đọc file Excel/CSV và hiển thị lên bảng xem trước
    public function preview(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:csv,txt,xlsx,xls|max:5120', // Tối đa 5MB
        ], [
            'import_file.required' => 'Vui lòng chọn một tệp tin dữ liệu!',
            'import_file.mimes'    => 'Hệ thống chỉ chấp nhận định dạng file .csv, .xlsx, .xls hoặc .txt'
        ]);

        try {
            if (!$this->importService) {
                throw new \Exception("Hệ thống chưa cấu hình Service xử lý tệp tin.");
            }
            
            // ĐÃ SỬA: Thay thế hàm không tồn tại 'parse()' thành 'importAccounts()' chuẩn khớp với Service
            $previewUsers = $this->importService->importAccounts($request->file('import_file'));

            if (empty($previewUsers)) {
                return redirect()->back()->withErrors(['import_file' => 'File trống hoặc không đọc được dữ liệu phù hợp!']);
            }

            // Trả lại chính view create kèm theo mảng dữ liệu vừa đọc để Blade render bảng dữ liệu
            return view('admin.accounts.create', compact('previewUsers'));

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['import_file' => 'Có lỗi xảy ra khi xử lý file: ' . $e->getMessage()]);
        }
    }
}