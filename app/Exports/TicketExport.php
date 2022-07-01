<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use App\Ticket;

class TicketExport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents
{
    /**
    * @return \Illuminate\Support\Collection
    */

    function __construct($agent,$status,$priority,$from_date,$to_date) {
        $this->agent = $agent;
        $this->status = $status;
        $this->priority = $priority;
        $this->from = $from_date;
        $this->to = $to_date;
    }
    public function collection()
    {
        $from = date("Y-m-d",strtotime($this->from));
        $to = date("Y-m-d",strtotime($this->to));
        $data = Ticket::select('tickets.*','users.name','users.email','statuses.name as status_name','priorities.name as priority_name')
            ->join('users','users.id','=','tickets.assigned_to_user_id')
            ->join('statuses','statuses.id','=','tickets.status_id')
            ->join('priorities','priorities.id','=','tickets.priority_id');
            if(!empty($this->from) && !empty( $this->to)){
                $data->whereBetween('tickets.created_at', [ $this->from, $this->to]);
            }else{
                if(!empty($this->from)){
                    $data->whereDate('tickets.created_at','>=',  $this->from);
                }
                if(!empty($this->to)){
                    $data->whereDate('tickets.created_at','<=',  $this->to);
                }
            }
            if($this->status != ''){
                $data->where(['tickets.status_id'=>$this->status]);
            }
            if(!empty($this->priority)){
                $data->where(['priorities.id'=>$this->priority]);
            }
            if(!empty($this->agent)){
                $data->where(['tickets.assigned_to_user_id'=>$this->agent]);
            }
            $data = $data->get();
            $ArrCode = array();
            foreach ($data as $key => $value) {
                $Arr = [
                    'agent_name' => $value['name'],
                    'agent_email' => $value['email'],
                    'customer_name' => $value['customer_name'],
                    'customer_mobile' => $value['customer_mobile'],
                    'category' => $value['category'],
                    'state' => $value['state'],
                    'city' => $value['city'],
                    'pincode' => $value['pincode'],
                    'address' => $value['address'],
                    'status'  =>  $value['status_name'],
                    'priority'  =>  $value['priority_name'],
                    'created_date' => date('Y-m-d', strtotime($value['created_at']))
                ];
                $ArrCode[] = $Arr;
            }
            return collect($ArrCode);
    }

    public function headings(): array
    {
        return [
            'Agent Name',
            'Agent Email',
            'Customer Name',
            'Customer Mobile',
            'Category',
            'State',
            'City',
            'Pincode',
            'Address',
            'Status',
            'Priority',
            'Created Date',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getStyle('A1:L1')->applyFromArray([
                    'font' => [
                        'bold' => true
                    ]
                ]);
            },
        ];
    }
}
