<?php

namespace RetailCosmos\TrxMallUploadSalesDataApi\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

class SalesDataProcessor
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

    private array $paymentTypes;

    /**
     * @var Collection<int,mixed>
     */
    private Collection $preparedSales;

    public function __construct()
    {
        $this->preparedSales = new Collection();

        $this->paymentTypes = [ // later PaymentType::values()
            'cash',
            'tng',
            'visa',
            'mastercard',
            'amex',
            'voucher',
            'othersamount',
        ];
    }

    /**
     * @param  Collection<int,mixed>  $sales
     * @return array<int,mixed>
     *
     * @throws \Exception
     */
    public function process(Collection $sales, array $storeData): array
    {
        $this->validate($sales->toArray());

        $sales->groupBy(function ($sale) {
            return Carbon::parse($sale['date'])->format('dmy');
        })->each(function ($sales, $date) use ($storeData) {
            $this->createTwentyFourHoursForMachine($storeData, $date);
            $sales->groupBy(function ($sale) {
                return Carbon::parse($sale['date'])->format('H');
            })->each(function ($sales, $hour) use ($date) {
                $this->aggregateSalesForHour($sales, $hour, $date);
            });
        });

        return $this->preparedSales->all();
    }

    /**
     * for validating data given by the developer
     *
     * @param  array<int,mixed>  $sales
     *
     * @throws \Exception
     */
    private function validate(array $sales): void
    {
        $validator = Validator::make($sales, [
            '*.gst' => $this->amountRules,
            '*.gto' => $this->amountRules,
            '*.discount' => $this->amountRules,
            '*.servicecharge' => $this->amountRules,
            '*.noofpax' => $this->integerRules,
            ...array_fill_keys(array_map(function ($type) {
                return '*.' . $type;
            }, $this->paymentTypes), $this->amountRules),
        ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }
    }

    private function createTwentyFourHoursForMachine($storeData, $date)
    {
        $hours = [];
        for ($i = 0; $i < 24; $i++) {
            $hours[$i] = ['sale' => [
                'machineid' => $storeData['machineid'],
                'batchid' => 1,
                'date' => $date,
                'hour' => $i,
                'receiptcount' => 0,
                'gto' => 0,
                'gst' => 0,
                'discount' => 0,
                'servicecharge' => 0,
                'noofpax' => 0,
                'gstregistered' => $storeData['gstregistered'],
                ...array_fill_keys($this->paymentTypes, 0),
            ]];
        }
        $this->preparedSales->push(...$hours);
    }

    private function aggregateSalesForHour($sales, $hour, $date)
    {
        $oldSale = $this->preparedSales->where('sale.hour', $hour)->where('sale.date', $date)->pop();

        $this->preparedSales->push([
            'sale' => [
                'machineid' => $oldSale['sale']['machineid'],
                'batchid' => $oldSale['sale']['batchid'],
                'date' => $oldSale['sale']['date'],
                'hour' => $oldSale['sale']['hour'],
                'receiptcount' => $oldSale['sale']['receiptcount'] + $sales->count(),
                'gto' => $oldSale['sale']['gto'] + $sales->sum('gto'),
                'gst' => $oldSale['sale']['gst'] + $sales->sum('gst'),
                'discount' => $oldSale['sale']['discount'] + $sales->sum('discount'),
                'servicecharge' => $oldSale['sale']['servicecharge'] + $sales->sum('servicecharge'),
                'noofpax' => $oldSale['sale']['noofpax'] + $sales->sum('noofpax'),
                ...array_map(function ($paymentType) use ($sales, $oldSale) {
                    return $oldSale['sale'][$paymentType] + $sales->sum($paymentType);
                }, $this->paymentTypes),
                'gstregistered' => $oldSale['sale']['gstregistered'],
            ],
        ]);
    }
}
