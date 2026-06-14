<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use ZipArchive;

class ClassMemberImportService
{
    public function importMembers(UploadedFile $file): array
    {
        $extension = Str::lower($file->getClientOriginalExtension());

        $rows = match ($extension) {
            'csv', 'txt' => $this->readCsv($file->getRealPath()),
            'xlsx' => $this->readXlsx($file->getRealPath()),
            default => throw ValidationException::withMessages([
                'import_file' => 'File import phải có định dạng CSV hoặc XLSX.',
            ]),
        };

        return $this->validateAndNormalize($rows);
    }

    public function sampleCsvContent(): string
    {
        return "\xEF\xBB\xBF".implode("\n", [
            'id',
            '"6"',
            '"7"',
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
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw ValidationException::withMessages(['import_file' => 'File XLSX không hợp lệ.']);
        }

        $sharedStrings = $this->readSharedStrings($zip);
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($sheetXml === false) {
            throw ValidationException::withMessages(['import_file' => 'Không tìm thấy dữ liệu Sheet 1.']);
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

    private function validateAndNormalize(array $rows): array
    {
        $rows = array_values(array_filter($rows, fn ($row) => count(array_filter($row, fn ($value) => trim((string) $value) !== '')) > 0));
        if (empty($rows)) {
            throw ValidationException::withMessages(['import_file' => 'File không có dữ liệu thành viên.']);
        }

        $firstCell = Str::lower(trim($rows[0][0]));
        $hasHeader = in_array($firstCell, ['id', 'user_code', 'ma', 'ma_tai_khoan', 'ma_thanh_vien']);
        $dataRows = $hasHeader ? array_slice($rows, 1) : $rows;

        $members = [];
        foreach ($dataRows as $row) {
            $id = trim((string)($row[0] ?? '')); // Đổi tên biến sang id cho đúng bản chất
            if ($id === '') continue;

            // SỬA LỖI CHÍNH: Tìm trực tiếp bằng User::find() vì id chính là mã chuỗi định danh (Ví dụ M002174)
            $user = User::find($id);

            if (!$user) {
                $members[] = [
                    'id'          => $id, // Đồng bộ key thành 'id' khớp với giao diện hiển thị preview
                    'name'        => 'N/A',
                    'role_text'   => 'Không rõ',
                    'status_text' => 'Tài khoản không tồn tại',
                    'is_valid'    => false
                ];
            } else {
                $roleMapping = [
                    1 => 'Quản trị viên',
                    2 => 'Giảng viên',
                    3 => 'Trợ lý lớp học',
                    4 => 'Học viên'
                ];

                $members[] = [
                    'id'          => $user->id,
                    'name'        => $user->name,
                    'role_text'   => $roleMapping[$user->role_id] ?? 'Không rõ', // Đổi từ $user->role sang $user->role_id
                    'status_text' => $user->status === 'active' ? 'Hợp lệ' : 'Bị khóa',
                    'is_valid'    => $user->status === 'active'
                ];
            }
        }

        return $members;
    }
}