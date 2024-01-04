<?php

namespace RetailCosmos\TrxMallUploadSalesDataApi\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use RetailCosmos\TrxMallUploadSalesDataApi\Enums\PaymentType;

class SalesDataProcessor
{
    /**
     * @var array<int,string>
     */
    private array $amountRules = [
        'required',
        'decimal:0,2',
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

        $this->paymentTypes = PaymentType::values();

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
            return Carbon::parse($sale['made_at'])->format('Ymd');
        })->each(function ($sales, $date) use ($storeData) {
            $this->createTwentyFourHoursSalesForStore($storeData, $date);
            $sales->groupBy(function ($sale) {
                return Carbon::parse($sale['made_at'])->format('H');
            })->each(function ($sales, $hour) use ($date) {
                $this->aggregateSalesForHour($sales, $hour, $date);
            });
        });

        return $this->preparedSales->values()->all();
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
            '*.made_at' => ['required', 'date_format:Y-m-d H:i:s'],
            '*.gst' => $this->amountRules,
            '*.gto' => $this->amountRules,
            '*.discount' => $this->amountRules,
            '*.service_charge' => $this->amountRules,
            '*.no_of_persons' => $this->integerRules,
            ...array_fill_keys(array_map(function ($type) {
                return '*.'.$type;
            }, $this->paymentTypes), $this->amountRules),
        ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }
    }

    /**
     * @param  array<string,mixed>  $storeData
     */
    private function createTwentyFourHoursSalesForStore(array $storeData, string $date): void
    {
        for ($i = 0; $i < 24; $i++) {
            $this->preparedSales->push(['sale' => [
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
            ]]);
        }
    }

    /**
     * @param  Collection<int,mixed>  $sales
     */
    private function aggregateSalesForHour(Collection $sales, string $hour, string $date): void
    {
        $oldSale = $this->preparedSales->pull($this->preparedSales->where('sale.hour', $hour)->where('sale.date', $date)->keys()->first());

        $this->preparedSales->push([
            'sale' => [
                'machineid' => $oldSale['sale']['machineid'],
                'batchid' => $oldSale['sale']['batchid'],
                'date' => $oldSale['sale']['date'],
                'hour' => $oldSale['sale']['hour'],
                'receiptcount' => $oldSale['sale']['receiptcount'] + $sales->count(),
                'gto' => round($oldSale['sale']['gto'] + $sales->sum('gto'), 2),
                'gst' => round($oldSale['sale']['gst'] + $sales->sum('gst'), 2),
                'discount' => round($oldSale['sale']['discount'] + $sales->sum('discount'), 2),
                'servicecharge' => round($oldSale['sale']['servicecharge'] + $sales->sum('service_charge'), 2),
                'noofpax' => $oldSale['sale']['noofpax'] + $sales->sum('no_of_persons'),
                ...array_combine($this->paymentTypes, array_map(function ($paymentType) use ($sales, $oldSale) {
                    return round($oldSale['sale'][$paymentType] + $sales->sum($paymentType), 2);
                }, $this->paymentTypes)),
                'gstregistered' => $oldSale['sale']['gstregistered'],
            ],
        ]);

    }
}
