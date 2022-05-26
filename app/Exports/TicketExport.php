<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;

class TicketExport implements FromCollection, WithHeadings,  ShouldAutoSize, WithEvents
{
    /**
    * @return \Illuminate\Support\Collection
    */

    function __construct($agent,$status,$from_date,$to_date) {
        $this->agent = $agent;
        $this->status = $status;
        $this->from = $from_date;
        $this->to = $to_date;
    }
    public function collection()
    {
        dd("Testing");
        $from = date("Y-m-d",strtotime($this->from));
        $to = date("Y-m-d",strtotime($this->to));

    }
}
