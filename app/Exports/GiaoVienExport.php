<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class GiaoVienExport implements WithMultipleSheets
{
    /**
    * @return \Illuminate\Support\Collection
    */
    use Exportable;
    protected $data;
    public function __construct($data)
    {
        $this->data = $data;
    }
    public function sheets(): array
    {
        $sheets = [];
        
        foreach($this->data as $magd) {
            $sheets[] = new GiaoVienSheetExport($magd);
        }

        return $sheets;
    }
}
