<?php

namespace RetailCosmos\TrxMallUploadSalesDataApi\Services;

use Illuminate\Support\Facades\Validator;

Class SalesDataProcessor
{
    /**
     * @var array<int,string>
     */
    private array $amountRules = [
        'required',
        'decimal:2',
        'min:0',
    ];

    /**
     * @var array<int,string>
     */
    private array $integerRules = [
        'required',
        'integer',
        'min:0',
    ];

    /**
     * @var array<int,string>
     */
    private array $idRules = [
        'required',
        'integer',
        'min:1',
    ];

    /**
     * @throws \Exception
     * @param array<int,mixed> $sales
     * @return array<int,mixed>
     */
    public function process(array $sales): array
    {
        // if required, transform the sales data here
        $this->transform($sales);

        $this->validate($sales);

        return $sales;

    }

    /**
     * @param array<int,mixed> $sales
     */
    private function transform(array &$sales): void
    {
        // Todo: transform the sales data here
        return;
    }

    /**
     * @throws \Exception
     * @param array<int,mixed> $sales
     */
    private function validate(array $sales): void
    {
        $validator = Validator::make($sales, [
            'sales' => ['required', 'array'],
            'sales.*.sale' => ['required', 'array'],
            'sales.*.sale.machineid' => $this->idRules,
            'sales.*.sale.batchid' => $this->idRules,
            'sales.*.sale.date' => [
                'required',
                'date_format:Ymd',
            ],
            'sales.*.sale.hour' => [
                'required',
                'between:00,23',
            ],
            'sales.*.sale.receiptcount' =>$this->integerRules,
            'sales.*.sale.gto' => $this->amountRules,
            'sales.*.sale.gst' => $this->amountRules,
            'sales.*.sale.discount' => $this->amountRules,
            'sales.*.sale.servicecharge' => $this->amountRules,
            'sales.*.sale.noofpax' =>$this->integerRules,
            'sales.*.sale.cash' => $this->amountRules,
            'sales.*.sale.visa' => $this->amountRules,
            'sales.*.sale.mastercard' => $this->amountRules,
            'sales.*.sale.amex' => $this->amountRules,
            'sales.*.sale.voucher' => $this->amountRules,
            'sales.*.sale.othersamount' => $this->amountRules,
            'sales.*.sale.gstregistered' => [
                'required',
                'string',
                'in:Y,N',
            ],
        ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }
    }
}
