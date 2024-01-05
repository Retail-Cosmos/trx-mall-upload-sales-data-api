<?php

namespace RetailCosmos\TrxMallUploadSalesDataApi\Commands;

use App\Services\TrxMallUploadSalesDataApiSalesService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Psr\Log\LoggerInterface;
use RetailCosmos\TrxMallUploadSalesDataApi\Contracts\TrxSalesService;
use RetailCosmos\TrxMallUploadSalesDataApi\Enums\PaymentType;

class SendSalesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tangent:send-sales {date?} {--store_identifier=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send sales in hourly(00-23) format to Tangent API';

    protected LoggerInterface $trxLogChannel;

    public function __construct()
    {
        parent::__construct();

        $this->trxLogChannel = Log::channel(config('trx_mall_upload_sales_data_api.log.channel'));
    }

    public function handle()
    {
        $message = 'start sending sales data to tangent api';
        $this->trxLogChannel->info($message);
        $this->info($message);

        try {
            validator([
                'date' => $this->argument('date'),
            ], [
                'date' => 'nullable|date_format:Y-m-d',
            ])->validate();

            $date = $this->argument('date') ?? now()->subDay()->format('Y-m-d');

            $storeIdentifier = $this->option('store_identifier');

            $this->info("sending sales data for date: $date");

            $this->info("store identifier: $storeIdentifier");

            $this->info('fetching store data');

            $storeData = $this->getStoreData($storeIdentifier);

            $this->info('fetching sales data');

            $sales = $this->getSalesData($date, $storeIdentifier);

            $this->info('processing sales data');

            $processedSales = $this->processSalesData($sales, $storeData);

            $this->info('sending sales data');

            $this->sendSalesData($processedSales);

            $this->info('sales data sent successfully');

            $message = 'end sending sales data to tangent api';

            $this->trxLogChannel->info($message);

            $this->info($message);

            return 0;
        } catch (\Exception $e) {
            $message = 'error sending sales data to tangent api';

            $this->trxLogChannel->error($message, [
                'exception' => $e,
            ]);

            $this->error($message);

            $this->error($e->getMessage());

            return 1;
        }
    }

    private function getStoreData(string $storeIdentifier): array
    {
        $trxSalesService = resolve(TrxMallUploadSalesDataApiSalesService::class);

        return $trxSalesService->getStoreData($storeIdentifier);
    }

    private function getSalesData(string $date, string $storeIdentifier): array
    {
        $trxSalesService = resolve(TrxMallUploadSalesDataApiSalesService::class);

        return $trxSalesService->getSalesData($date, $storeIdentifier)->toArray();
    }

    private function processSalesData(array $sales, array $storeData): array
    {
        $processedSales = [
            [
                'sale' => [
                    'machineid' => '123',
                    'batchid' => 1,
                    'date' => '20230201',
                    'hour' => 0,
                    'receiptcount' => 2,
                    'gto' => 583.08,
                    'gst' => 14.20,
                    'discount' => 10.00,
                    PaymentType::CASH() => 27.94,
                    PaymentType::TNG() => 0.00,
                    PaymentType::VISA() => 253.56,
                    PaymentType::MASTERCARD() => 0.00,
                    PaymentType::AMEX() => 95.60,
                    PaymentType::VOUCHER() => 0.00,
                    PaymentType::OTHERS() => 57.99,
                    'gstregistered' => 'Y',
                ],
            ],
        ];

        return $processedSales;
    }

    private function sendSalesData(array $sales): void
    {

    }
}
