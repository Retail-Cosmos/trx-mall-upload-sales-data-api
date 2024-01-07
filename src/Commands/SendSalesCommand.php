<?php

namespace RetailCosmos\TrxMallUploadSalesDataApi\Commands;

use App\Services\TrxMallUploadSalesDataApiService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Psr\Log\LoggerInterface;
use RetailCosmos\TrxMallUploadSalesDataApi\Clients\TangentApiClient;
use RetailCosmos\TrxMallUploadSalesDataApi\Notifications\TrxApiStatusNotification;
use RetailCosmos\TrxMallUploadSalesDataApi\Services\SalesDataProcessor;
use RetailCosmos\TrxMallUploadSalesDataApi\Services\StoreDataProcessor;

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

    private LoggerInterface $trxLogChannel;

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->trxLogChannel = Log::channel(config('trx_mall_upload_sales_data_api.log.channel'));
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $message = 'start sending sales data to tangent api';
        $this->trxLogChannel->info($message);
        $this->info($message);

        try {
            $this->info('validating options');

            $this->validateOptions();

            $this->info('validating config');

            $this->validateConfig();

            $date = $this->argument('date') ?? now()->subDay()->format('Y-m-d');

            $storeIdentifier = $this->option('store_identifier');

            $this->info("sending sales data for date: $date");

            if ($storeIdentifier) {
                $this->info("for store identifier: $storeIdentifier");
            }

            $this->info('fetching and processing stores');

            $stores = $this->getProcessedStores($storeIdentifier);

            $this->info('fetching and processing sales');

            $sales = $this->getProcessedSales($date, $stores);

            $this->info('sending sales data');

            $this->sendSalesData($sales);

            $this->info('sales data sent successfully');

            $message = 'end sending sales data to tangent api';

            $this->trxLogChannel->info($message);

            $this->info($message);

            $message = 'total sales sent: '.count($sales);

            $this->info($message);

            $this->trxLogChannel->info($message);

            if ($email = config('trx_mall_upload_sales_data_api.notifications.mail.email')) {
                $name = config('trx_mall_upload_sales_data_api.notifications.mail.name', 'sir/madam');
                Notification::route('mail', $email)
                    ->notify(new TrxApiStatusNotification($name, 'success', $message));
            }

            return 0;
        } catch (\Exception $e) {
            $message = 'error sending sales data to tangent api';

            $this->trxLogChannel->error($message, [
                'exception' => $e,
            ]);

            $this->error($message);

            $this->error($e->getMessage());

            if ($email = config('trx_mall_upload_sales_data_api.notifications.mail.email')) {
                $name = config('trx_mall_upload_sales_data_api.notifications.mail.name', 'sir/madam');
                Notification::route('mail', $email)
                    ->notify(new TrxApiStatusNotification($name, 'error', $e->getMessage()));
            }

            return 1;
        }
    }

    /**
     * @throws \Exception
     */
    private function validateOptions(): void
    {
        $validator = validator($this->arguments(), [
            'date' => 'nullable|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }
    }

    /**
     * @throws \Exception
     */
    private function validateConfig(): void
    {
        $requiredAttributeMessage = 'The :attribute needs to be configured';
        $validator = validator(config('trx_mall_upload_sales_data_api', []), [
            'log.channel' => 'required|string',
            'api.base_uri' => 'required|url',
            'api.grant_type' => 'required|string',
            'api.username' => 'required|string',
            'api.password' => 'required|string',
            'notifications.mail.name' => 'nullable|string',
            'notifications.mail.email' => 'nullable|email',
            'date_of_first_sales_upload' => 'required|date_format:Y-m-d,before_or_equal:now',
        ], [
            '*.required' => $requiredAttributeMessage,
            '*.*.required' => $requiredAttributeMessage,
        ], [
            'log.channel' => 'TRX_MALL_LOG_CHANNEL',
            'api.base_uri' => 'TRX_MALL_API_BASE_URI',
            'api.grant_type' => 'TRX_MALL_API_GRANT_TYPE',
            'api.username' => 'TRX_MALL_API_USERNAME',
            'api.password' => 'TRX_MALL_API_PASSWORD',
            'notifications.mail.name' => 'TRX_MALL_NOTIFICATION_MAIL_NAME',
            'notifications.mail.email' => 'TRX_MALL_NOTIFICATION_MAIL_EMAIL',
            'date_of_first_sales_upload' => 'TRX_MALL_DATE_OF_FIRST_SALES_UPLOAD',
        ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }
    }

    /**
     * @return array<int,mixed>
     *
     * @throws \Exception
     */
    private function getProcessedStores(?string $storeIdentifier): array
    {
        $trxSalesService = resolve(TrxMallUploadSalesDataApiService::class);

        $stores = $trxSalesService->getStores($storeIdentifier);

        if (empty($stores)) {
            throw new \Exception('no stores found'.($storeIdentifier ? " for identifier: $storeIdentifier" : ''));
        }

        return (new StoreDataProcessor())->process($stores);
    }

    /**
     * @param  array<int,mixed>  $stores
     * @return array<int,mixed>
     *
     * @throws \Exception
     */
    private function getProcessedSales(string $date, array $stores): array
    {
        $trxSalesService = resolve(TrxMallUploadSalesDataApiService::class);
        $batchId = Carbon::parse($date)->diffInDays(config('trx_mall_upload_sales_data_api.date_of_first_sales_upload')) + 1;

        $processedSales = [];
        foreach ($stores as $store) {
            $sales = $trxSalesService->getSales($date, $store['store_identifier']);
            if ($sales->isEmpty()) {
                continue;
            }
            $salesService = new SalesDataProcessor($date, $batchId);
            $processedSales = array_merge($processedSales, $salesService->process($sales, $store));
        }

        if (empty($processedSales)) {
            throw new \Exception('no sales found');
        }

        return $processedSales;
    }

    /**
     * @param  array<int,mixed>  $sales
     *
     * @throws \Exception
     */
    private function sendSalesData(array $sales): void
    {
        $config = config('trx_mall_upload_sales_data_api.api');

        $client = new TangentApiClient($config);

        collect($sales)->chunk(900)->each(function ($sales) use ($client) {
            $response = $client->sendSalesHourly($sales->toArray());
            if (! $response->ok()) {
                throw new \Exception($response->json('errors.0.message'));
            }
        });
    }
}
