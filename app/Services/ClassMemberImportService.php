<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use ZipArchive;

class ClassMemberImportService
{
    public function importMembers(UploadedFile $file, $class): array
    {
        $extension = Str::lower($file->getClientOriginalExtension());

        $rows = match ($extension) {
            'csv', 'txt' => $this->readCsv($file->getRealPath()),
            'xlsx' => $this->readXlsx($file->getRealPath()),
            default => throw ValidationException::withMessages([
                'import_file' => 'File import phải có định dạng CSV hoặc XLSX.',
            ]),
        };

        return $this->validateAndNormalize($rows, $class);
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

    private function validateAndNormalize(array $rows, $class): array
    {
        if (empty($rows)) return [];

        // Lấy ô đầu tiên và xóa bỏ ký tự BOM ẩn (\xEF\xBB\xBF) nếu có
        $firstCell = isset($rows[0][0]) ? trim((string)$rows[0][0]) : '';
        $firstCellClean = str_replace("\xEF\xBB\xBF", "", $firstCell);

        // Kiểm tra tiêu đề chính xác sau khi đã làm sạch
        $hasHeader = (Str::lower($firstCellClean) === 'id' || Str::lower($firstCellClean) === 'ma');
        $dataRows = $hasHeader ? array_slice($rows, 1) : $rows;

        $members = [];
        $fileProcessedIds = []; // Mảng theo dõi ID trùng lặp nội bộ trong file import

        // YÊU CẦU 2A: Lấy danh sách toàn bộ ID của các thành viên ĐÃ CÓ MẶT trong lớp này
        $existingUserIdsInClass = $class->users()->pluck('users.id')->toArray();

        foreach ($dataRows as $row) {
            $id = trim((string)($row[0] ?? '')); 
            if ($id === '') continue;

            // YÊU CẦU 2B: Kiểm tra xem ID có bị trùng lặp dòng ngay trong file Excel/CSV không
            if (in_array($id, $fileProcessedIds)) {
                $members[] = [
                    'id'          => $id, 
                    'name'        => 'N/A',
                    'role_text'   => 'Không rõ',
                    'status_text' => 'Bị trùng lặp dòng ngay trong file import',
                    'is_valid'    => false
                ];
                continue;
            }
            $fileProcessedIds[] = $id;

            $user = User::find($id);

            if (!$user) {
                $members[] = [
                    'id'          => $id, 
                    'name'        => 'N/A',
                    'role_text'   => 'Không rõ',
                    'status_text' => 'Tài khoản không tồn tại',
                    'is_valid'    => false
                ];
            } else {
                // YÊU CẦU 2C: Kiểm tra tài khoản này đã tồn tại trong lớp học này chưa
                if (in_array($user->id, $existingUserIdsInClass)) {
                    $members[] = [
                        'id'          => $user->id,
                        'name'        => $user->name,
                        'role_text'   => $this->getRoleName($user->role_id), 
                        'status_text' => 'Đã tồn tại trong lớp học này rồi',
                        'is_valid'    => false 
                    ];
                    continue;
                }

                $members[] = [
                    'id'          => $user->id,
                    'name'        => $user->name,
                    'role_text'   => $this->getRoleName($user->role_id), 
                    'status_text' => $user->status === 'active' ? 'Hợp lệ' : 'Tài khoản đang bị khóa',
                    'is_valid'    => $user->status === 'active'
                ];
            }
        }
        return $members;
    }

    private function getRoleName(?int $roleId): string
    {
        return match($roleId) {
            1 => 'Quản trị viên',
            2 => 'Giảng viên',
            3 => 'Trợ giảng',
            4 => 'Học viên',
            default => 'Không xác định'
        };
    }
}