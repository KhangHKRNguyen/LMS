<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use ZipArchive;

class AccountImportService
{
    public function importAccounts(UploadedFile $file): array
    {
        $extension = Str::lower($file->getClientOriginalExtension());

        $rows = match ($extension) {
            'csv', 'txt' => $this->readCsv($file->getRealPath()),
            'xlsx' => $this->readXlsx($file->getRealPath()),
            default => throw ValidationException::withMessages([
                'import_file' => 'File import phải có định dạng CSV hoặc XLSX.',
            ]),
        };

        return $this->normalizeRows($rows);
    }

    /**
     * Cập nhật file mẫu không cần cột ID/mã tài khoản
     */
    public function sampleCsvContent(): string
    {
        return "\xEF\xBB\xBF".implode("\n", [
            'name,email,role', 
            '"Phạm Trang Nhung","nhunggiangvien@gmail.com","teacher"',
            '"Nguyễn Văn B","nguyenvanb@gmail.com","student"',
        ]);
    }

    private function readCsv(string $path): array
    {
        $rows = [];
        $handle = fopen($path, 'rb');
        while (($row = fgetcsv($handle)) !== false) {
            $rows[] = array_map(fn ($value) => trim((string) $value), $row);
        }
        fclose($handle);
        return $rows;
    }

    private function readXlsx(string $path): array
    {
        $rows = [];
        $zip = new ZipArchive();
        
        if ($zip->open($path) === true) {
            $sharedStrings = [];
            if ($zip->locateName('xl/sharedStrings.xml') !== false) {
                $stringsXml = simplexml_load_string($zip->getFromName('xl/sharedStrings.xml'));
                foreach ($stringsXml->si as $val) {
                    $sharedStrings[] = (string) $val->t;
                }
            }

            if ($zip->locateName('xl/worksheets/sheet1.xml') !== false) {
                $sheetXml = simplexml_load_string($zip->getFromName('xl/worksheets/sheet1.xml'));
                foreach ($sheetXml->sheetData->row as $row) {
                    $rowData = [];
                    foreach ($row->c as $cell) {
                        $val = (string) $cell->v;
                        if (isset($cell['t']) && (string) $cell['t'] === 's') {
                            $rowData[] = $sharedStrings[(int) $val] ?? '';
                        } else {
                            $rowData[] = $val;
                        }
                    }
                    $rows[] = $rowData;
                }
            }
            $zip->close();
        }

        return $rows;
    }

    /**
     * Xử lý chuẩn hóa dữ liệu hàng loạt, kiểm tra trùng lặp và tự động sinh chuỗi ID ngẫu nhiên
     */
    private function normalizeRows(array $rows): array
    {
        if (empty($rows)) {
            return [];
        }

        $processedAccounts = [];
        $seenEmailsInFile = []; 

        //Lấy số ID lớn nhất hiện tại trong bảng users, nếu bảng trống sẽ xuất phát từ 0
        $currentMaxId = (int) User::max('id');
        $nextId = $currentMaxId + 1; // ID tiếp theo sẽ bắt đầu từ đây

        // 1. Lấy dòng đầu tiên để phân tích Tiêu đề (Header)
        $firstRow = $rows[0];
        
        if (count($firstRow) === 1 && (str_contains($firstRow[0], ',') || str_contains($firstRow[0], ';'))) {
            $delimiter = str_contains($firstRow[0], ';') ? ';' : ',';
            $firstRow = str_getcsv($firstRow[0], $delimiter);
        }

        $normalizedFirstRow = array_map([$this, 'normalizeHeader'], $firstRow);
        $headerKeywords = ['id', 'ma', 'name', 'ten', 'ho_ten', 'email', 'role', 'vai_tro'];
        
        $isHeader = false;
        foreach ($normalizedFirstRow as $cell) {
            if (in_array($cell, $headerKeywords)) {
                $isHeader = true;
                break;
            }
        }

        $headerMap = null;
        $startRowIndex = 0;

        if ($isHeader) {
            $headerMap = $normalizedFirstRow;
            $startRowIndex = 1; 
        }

        // 2. Duyệt qua từng dòng dữ liệu thực tế
        for ($i = $startRowIndex; $i < count($rows); $i++) {
            $row = $rows[$i];
            $lineNumber = $i + 1; 

            if (count($row) === 1 && (str_contains($row[0], ',') || str_contains($row[0], ';'))) {
                $delimiter = str_contains($row[0], ';') ? ';' : ',';
                $row = str_getcsv($row[0], $delimiter);
            }

            if (empty($row) || (count($row) === 1 && trim((string)$row[0]) === '')) {
                continue;
            }

            $source = [];
            if ($headerMap) {
                foreach ($headerMap as $cellIndex => $key) {
                    $source[$key] = $row[$cellIndex] ?? '';
                }
            } else {
                if (count($row) >= 4) {
                    $source['name']  = $row[1] ?? '';
                    $source['email'] = $row[2] ?? '';
                    $source['role']  = $row[3] ?? '';
                } else {
                    $source['name']  = $row[0] ?? '';
                    $source['email'] = $row[1] ?? '';
                    $source['role']  = $row[2] ?? '';
                }
            }

            $name    = trim((string)($source['name'] ?? $source['ten'] ?? $source['ho_ten'] ?? ''));
            $email   = strtolower(trim((string)($source['email'] ?? '')));
            $roleRaw = strtolower(trim((string)($source['role'] ?? $source['vai_tro'] ?? '')));

            // --- VALIDATION DỮ LIỆU ---
            if (empty($email)) {
                throw ValidationException::withMessages([
                    'import_file' => "Dòng số {$lineNumber}: Email không được để trống.",
                ]);
            }

            if (empty($name)) {
                throw ValidationException::withMessages([
                    'import_file' => "Dòng số {$lineNumber}: Họ tên không được để trống.",
                ]);
            }

            // --- KIỂM TRA TRÙNG LẶP EMAIL ---
            if (isset($seenEmailsInFile[$email])) {
                throw ValidationException::withMessages([
                    'import_file' => "Dòng số {$lineNumber}: Email '{$email}' bị trùng lặp với dòng số {$seenEmailsInFile[$email]} trong file import.",
                ]);
            }
            $seenEmailsInFile[$email] = $lineNumber;

            if (User::where('email', $email)->exists()) {
                throw ValidationException::withMessages([
                    'import_file' => "Dòng số {$lineNumber}: Email '{$email}' đã tồn tại trong hệ thống.",
                ]);
            }

            // --- CHUẨN HÓA VAI TRÒ (ROLE) ---
            $role = match ($roleRaw) {
                'admin', 'quan_tri_vien', 'quản trị viên' => 'admin',
                'teacher', 'giang_vien', 'giảng viên', 'giáo viên' => 'teacher',
                'ta', 'tro_ly', 'trợ lý', 'assistant', 'trợ lý lớp học' => 'ta',
                default => 'student',
            };

            $generatedId = $nextId;
            $nextId++; // Tự động cộng 1 đơn vị cho tài khoản kế tiếp trong danh sách

            $processedAccounts[] = [
                'id'    => $generatedId,
                'name'  => $name,
                'email' => $email,
                'role'  => $role,
            ];
        }

        return $processedAccounts;
    }

    private function mapHeaderRow(array $header, array $row): array
    {
        $map = [];
        foreach ($header as $index => $key) {
            $map[$this->canonicalKey($key)] = $row[$index] ?? '';
        }
        return $map;
    }

    private function mapFixedRow(array $row): array
    {
        return [
            'id'    => $row[0] ?? '',
            'name'  => $row[1] ?? '',
            'email' => $row[2] ?? '',
            'role'  => $row[3] ?? '',
        ];
    }

    private function normalizeHeader(string $value): string
    {
        return trim(preg_replace('/_+/', '_', preg_replace('/[^a-z0-9]+/', '_', Str::lower(Str::ascii($value)))), '_');
    }

    private function canonicalKey(string $key): string
    {
        return match ($key) {
            'id', 'ma', 'ma_tai_khoan', 'user_code' => 'id',
            'name', 'ten', 'ho_ten' => 'name',
            'email' => 'email',
            'role', 'vai_tro' => 'role',
            default => $key,
        };
    }
}