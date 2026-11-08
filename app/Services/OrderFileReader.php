<?php

namespace App\Services;

use Generator;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class OrderFileReader
{
    public function rows(UploadedFile $file): Generator
    {
        $extension = strtolower($file->getClientOriginalExtension());
        if (in_array($extension, ['csv', 'tsv'], true)) {
            $handle = fopen($file->getRealPath(), 'r');
            try {
                while (($row = fgetcsv($handle, 0, $extension === 'tsv' ? "\t" : ',', '"', '')) !== false) {
                    yield $row;
                }
            } finally {
                fclose($handle);
            }

            return;
        }

        $type = ['xlsx' => 'Xlsx', 'xls' => 'Xls', 'ods' => 'Ods'][$extension];
        $reader = IOFactory::createReader($type);
        $spreadsheet = null;
        try {
            if (! $reader->canRead($file->getRealPath())) {
                throw ValidationException::withMessages(['file' => 'Nội dung file không khớp định dạng đã chọn.']);
            }
            $spreadsheet = $reader->load($file->getRealPath());
            $sheet = $spreadsheet->getSheet(0);
            foreach ($sheet->getRowIterator() as $row) {
                $values = [];
                foreach ($row->getCellIterator('A', $sheet->getHighestDataColumn()) as $cell) {
                    if ($cell->isFormula()) {
                        throw ValidationException::withMessages(['file' => 'File chứa công thức tại ô '.$cell->getCoordinate().'. Hãy chuyển công thức thành giá trị trước khi nhập.']);
                    }
                    $value = $cell->getValue();
                    if (is_numeric($value) && Date::isDateTime($cell)) {
                        $value = Date::excelToDateTimeObject((float) $value)->format('Y-m-d H:i:s');
                    }
                    $values[] = $value === null ? null : (string) $value;
                }
                yield $values;
            }
        } catch (Exception $exception) {
            throw ValidationException::withMessages(['file' => 'Không đọc được bảng tính. Hãy kiểm tra file có bị hỏng hoặc đặt mật khẩu không.']);
        } finally {
            $spreadsheet?->disconnectWorksheets();
        }
    }
}
