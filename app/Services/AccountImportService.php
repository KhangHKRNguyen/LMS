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

    public function sampleCsvContent(): string
    {
        return "\xEF\xBB\xBF".implode("\n", [
            'id,name,email,role', // ĐÃ ĐỔI: user_code -> id tại tiêu đề mẫu file tải về
            '"M002174","Phạm Trang Nhung","nhunggiangvien@gmail.com","teacher"',
            '"M008821","Nguyễn Văn B","nguyenvanb@gmail.com","student"',
        ]);
    }

    private function readCsv(string $path): array
    {
        $rows = [];
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            throw ValidationException::withMessages(['import_file' => 'Không thể đọc file import.']);
        }
        while (($row = fgetcsv($handle)) !== false) {
            $rows[] = array_map(fn ($value) => trim((string) $value), $row);
        }
        fclose($handle);
        return $rows;
    }

    private function readXlsx(string $path): array
    {
        if (!class_exists(ZipArchive::class) || !function_exists('simplexml_load_string')) {
            throw ValidationException::withMessages(['import_file' => 'Máy chủ thiếu tiện ích mở rộng mở file XLSX. Vui lòng dùng CSV.']);
        }

        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw ValidationException::withMessages(['import_file' => 'File XLSX không hợp lệ.']);
        }

        $sharedStrings = $this->readSharedStrings($zip);
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($sheetXml === false) {
            throw ValidationException::withMessages(['import_file' => 'File XLSX phải có sheet đầu tiên chứa dữ liệu.']);
        }

        $sheet = simplexml_load_string($sheetXml);
        $rows = [];
        foreach ($sheet->sheetData->row as $row) {
            $values = [];
            foreach ($row->c as $cell) {
                $column = $this->columnIndex((string) $cell['r']);
                $values[$column] = $this->cellValue($cell, $sharedStrings);
            }
            if ($values !== []) {
                ksort($values);
                $rows[] = array_values($values);
            }
        }
        return $rows;
    }

    private function readSharedStrings(ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');
        if ($xml === false) return [];
        $shared = simplexml_load_string($xml);
        if ($shared === false) return [];
        
        $strings = [];
        foreach ($shared->si as $item) {
            if (isset($item->t)) { $strings[] = (string) $item->t; continue; }
            $text = '';
            foreach ($item->r as $run) { $text .= (string) $run->t; }
            $strings[] = $text;
        }
        return $strings;
    }

    private function cellValue(\SimpleXMLElement $cell, array $sharedStrings): string
    {
        $type = (string) $cell['t'];
        if ($type === 's') return trim($sharedStrings[(int) $cell->v] ?? '');
        if ($type === 'inlineStr') return trim((string) $cell->is->t);
        return trim((string) $cell->v);
    }

    private function columnIndex(string $cellReference): int
    {
        preg_match('/^[A-Z]+/', $cellReference, $matches);
        $letters = $matches[0] ?? 'A';
        $index = 0;
        foreach (str_split($letters) as $letter) {
            $index = ($index * 26) + (ord($letter) - 64);
        }
        return $index - 1;
    }

    private function normalizeRows(array $rows): array
    {
        $rows = array_values(array_filter($rows, fn ($row) => count(array_filter($row, fn ($value) => trim((string) $value) !== '')) > 0));

        if ($rows === []) {
            throw ValidationException::withMessages(['import_file' => 'File import không có dữ liệu tài khoản.']);
        }

        $header = array_map(fn ($value) => $this->normalizeHeader((string) $value), $rows[0]);
        $hasHeader = count(array_intersect($header, ['id', 'user_code', 'ma_tai_khoan', 'ma', 'name', 'ho_ten'])) > 0;
        $dataRows = $hasHeader ? array_slice($rows, 1) : $rows;

        $accounts = [];
        foreach ($dataRows as $index => $row) {
            $source = $hasHeader ? $this->mapHeaderRow($header, $row) : $this->mapFixedRow($row);
            $line = $hasHeader ? $index + 2 : $index + 1;

            // ĐÃ ĐỔI: Kiểm tra trường bắt buộc theo khóa 'id' thay vì 'user_code'
            foreach (['id', 'name', 'email', 'role'] as $field) {
                if (trim((string) ($source[$field] ?? '')) === '') {
                    throw ValidationException::withMessages(['import_file' => "Dòng {$line}: Thiếu dữ liệu của trường bắt buộc."]);
                }
            }

            // Chuẩn hóa quyền (Role)
            $role = Str::lower(trim((string)$source['role']));
            $role = match($role) {
                'giảng viên', 'giang vien', 'teacher' => User::ROLE_TEACHER,
                'trợ lý', 'tro ly', 'ta' => User::ROLE_TA,
                'học viên', 'hoc vien', 'student' => User::ROLE_STUDENT,
                'admin', 'quản trị' => User::ROLE_ADMIN,
                default => throw ValidationException::withMessages(['import_file' => "Dòng {$line}: Vai trò '{$role}' không hợp lệ."])
            };

            // ĐÃ ĐỔI: Định dạng key trả ra thành 'id' phục vụ View & Controller
            $accounts[] = [
                'id'    => trim((string) $source['id']),
                'name'  => trim((string) $source['name']),
                'email' => trim((string) $source['email']),
                'role'  => $role,
            ];
        }

        return $accounts;
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
            'id'    => $row[0] ?? '', // ĐÃ ĐỔI
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
            // ĐÃ ĐỔI: Toàn bộ từ khóa tương tự mã tài khoản quy về đích duy nhất là 'id'
            'id', 'ma', 'ma_tai_khoan', 'user_code', 'ma_nhan_su' => 'id',
            'ho_ten', 'ten', 'name', 'full_name'           => 'name',
            'email', 'thu_dien_tu'                          => 'email',
            'vai_tro', 'role', 'chuc_vu'                    => 'role',
            default => $key,
        };
    }
}