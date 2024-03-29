<?php

namespace RetailCosmos\TrxMallUploadSalesDataApi\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use RetailCosmos\TrxMallUploadSalesDataApi\Enums\PaymentType;

class SalesDataProcessor
{
    /**
     * @var array<int,string>
     */
    private array $amountRules = [
        'required',
        'decimal:0,2',
    ];

    /**
     * @var array<int,string>
     */
    private array $paymentTypes;

    /**
     * @var array<int,mixed>
     */
    private array $preparedSales = [];

    /**
     * pass date in Y-m-d format
     */
    public function __construct(private string $date, private int $batchId)
    {
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

        $this->createTwentyFourHoursSalesForStore($storeData);

        $sales->groupBy(function ($sale) {
            return Carbon::parse($sale['happened_at'])->format('H');
        })->each(function ($sales, $hour) {
            $this->aggregateSalesForHour($sales, $hour);
        });

        return $this->preparedSales;
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
        $validator = validator($sales, [
            '*.happened_at' => ['required', 'date_format:Y-m-d H:i:s', function ($attribute, $value, $fail) {
                if (Carbon::parse($value)->format('Y-m-d') !== $this->date) {
                    $fail('One of the sales is not on the date given');
                }
            }],
            '*.gst' => $this->amountRules,
            '*.net_amount' => $this->amountRules,
            '*.discount' => $this->amountRules,
            '*.payments' => ['required', 'array'],
            ...array_fill_keys(array_map(function ($type) {
                return '*.payments.'.$type;
            }, $this->paymentTypes), $this->amountRules),
        ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }
    }

    /**
     * @param  array<string,mixed>  $storeData
     */
    private function createTwentyFourHoursSalesForStore(array $storeData): void
    {
        $date = Carbon::parse($this->date)->format('Ymd');
        foreach (range(0, 23) as $i) {
            $this->preparedSales[] = ['sale' => [
                'machineid' => (string) $storeData['machineid'],
                'batchid' => (string) $this->batchId,
                'date' => (string) $date,
                'hour' => sprintf('%02d', $i),
                'receiptcount' => (string) 0,
                'gto' => (string) 0,
                'gst' => (string) 0,
                'discount' => (string) 0,
                'servicecharge' => (string) 0,
                'noofpax' => (string) 0,
                ...array_fill_keys($this->paymentTypes, (string) 0),
                'gstregistered' => (string) $storeData['gstregistered'],
            ]];
        }
    }

    /**
     * @param  Collection<int,mixed>  $sales
     */
    private function aggregateSalesForHour(Collection $sales, string $hour): void
    {
        foreach ($this->preparedSales as &$sale) {
            if ($sale['sale']['hour'] === $hour) {
                $sale['sale']['receiptcount'] = (string) $sales->count();
                $sale['sale']['gto'] = (string) round($sales->sum('net_amount'), 2);
                $sale['sale']['gst'] = (string) round($sales->sum('gst'), 2);
                $sale['sale']['discount'] = (string) round($sales->sum('discount'), 2);
                foreach ($this->paymentTypes as $paymentType) {
                    $sale['sale'][$paymentType] = (string) round($sales->sum('payments.'.$paymentType), 2);
                }
            }
        }
    }
}
