<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class UsersExport implements FromView, WithEvents, ShouldAutoSize
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function view(): View
    {
        return view('printHasil', ['data' => $this->data]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $highestColumn = $sheet->getHighestColumn();
                $highestRow = $sheet->getHighestRow();
                $headerRange = 'A1:' . $highestColumn . '1';

                // Style Header (Biru, Bold, Left Align)
                $sheet->getStyle($headerRange)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '2196F3'], // Biru
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                // Border untuk semua data
                $sheet->getStyle('A1:' . $highestColumn . $highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                // Alternating row colors
                $sheet->getStyle('A2:' . $highestColumn . $highestRow)->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFFFFF'], // Putih
                    ],
                ]);

                // Set alignment for data cells
                $sheet->getStyle('A2:' . $highestColumn . $highestRow)->applyFromArray([
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // Auto size columns
                foreach (range('A', $highestColumn) as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }

                // Set row height for header
                $sheet->getRowDimension(1)->setRowHeight(25);

                // Set lebar maksimum untuk kolom selain A dan B
                foreach (range('C', $highestColumn) as $column) {
                    $sheet->getColumnDimension($column)->setWidth(30); // Atur lebar maksimum 30
                }

                // Set row height for data rows
                for ($i = 2; $i <= $highestRow; $i++) {
                    $sheet->getRowDimension($i)->setRowHeight(20);
                }
            },
        ];
    }
}
