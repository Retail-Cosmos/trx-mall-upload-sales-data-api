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

    private array $paymentTypes;

    /**
     * pass date in Y-m-d format
     *
     * @var Collection<int,mixed>
     */
    private Collection $preparedSales;

    public function __construct(private string $date)
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

        $this->createTwentyFourHoursSalesForStore($storeData);

        $sales->groupBy(function ($sale) {
            return Carbon::parse($sale['happened_at'])->format('H');
        })->each(function ($sales, $hour) {
            $this->aggregateSalesForHour($sales, $hour);
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
                ...array_fill_keys($this->paymentTypes, 0),
                'gstregistered' => $storeData['gstregistered'],
            ]]);
        }
    }

    /**
     * @param  Collection<int,mixed>  $sales
     */
    private function aggregateSalesForHour(Collection $sales, string $hour): void
    {
        $this->preparedSales->transform(function ($sale) use ($sales, $hour) {
            if ($sale['sale']['hour'] === (int) $hour) {
                $sale['sale']['receiptcount'] += $sales->count();
                $sale['sale']['gto'] = round($sales->sum('net_amount'), 2);
                $sale['sale']['gst'] = round($sales->sum('gst'), 2);
                $sale['sale']['discount'] = round($sales->sum('discount'), 2);
                foreach ($this->paymentTypes as $paymentType) {
                    $sale['sale'][$paymentType] = round($sales->sum('payments.'.$paymentType), 2);
                }
            }

            return $sale;
        });

    }
}
