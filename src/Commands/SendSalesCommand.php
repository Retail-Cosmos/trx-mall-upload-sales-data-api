<?php

namespace RetailCosmos\TrxMallUploadSalesDataApi\Commands;

use App\Services\TrxMallUploadSalesDataApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Psr\Log\LoggerInterface;
use RetailCosmos\TrxMallUploadSalesDataApi\Clients\TangentApiClient;
use RetailCosmos\TrxMallUploadSalesDataApi\Notifications\TrxApiStatusNotification;
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

            if($email = config('trx_mall_upload_sales_data_api.notifications.mail.email')) {
                Notification::route('mail', $email)
                    ->notify(new TrxApiStatusNotification('success', $message));
            }

            return 0;
        } catch (\Exception $e) {
            $message = 'error sending sales data to tangent api';

            $this->trxLogChannel->error($message, [
                'exception' => $e,
            ]);

            $this->error($message);

            $this->error($e->getMessage());

            if($email = config('trx_mall_upload_sales_data_api.notifications.mail.email')) {
                Notification::route('mail', $email)
                    ->notify(new TrxApiStatusNotification('error', $e->getMessage()));
            }

            return 1;
        }
    }

    private function validateConfig(): void
    {
        $validator = validator(config('trx_mall_upload_sales_data_api'), [
            'log.channel' => 'required|string',
            'tangent_api_client.base_uri' => 'required|url',
            'tangent_api_client.grant_type' => 'required|string',
            'tangent_api_client.username' => 'required|string',
            'tangent_api_client.password' => 'required|string',
            'notifications.mail.name' => 'nullable|string',
            'notifications.mail.email' => 'nullable|email',
        ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }
    }

    private function getProcessedStores(?string $storeIdentifier): array
    {
        $trxSalesService = resolve(TrxMallUploadSalesDataApiService::class);

        $stores = $trxSalesService->getStores($storeIdentifier);

        if (empty($stores)) {
            throw new \Exception('no stores found'.($storeIdentifier ? " for identifier: $storeIdentifier" : ''));
        }

        return (new StoreDataProcessor())->process($stores);
    }

    private function getProcessedSales(string $date, array $stores): array
    {
        $trxSalesService = resolve(TrxMallUploadSalesDataApiService::class);

        $processedSales = [];
        foreach ($stores as $store) {
            $sales = $trxSalesService->getSales($date, $store['identifier']);
            $salesService = resolve('sales-data-processor', [ // later SalesDataProcessor::class
                'date' => $date,
            ]);
            $processedSales = array_merge($processedSales, $salesService->processSales($sales, $store));
        }

        return $processedSales;
    }

    private function sendSalesData(array $sales): void
    {
        $config = config('trx_mall_upload_sales_data_api.api');

        $client = new TangentApiClient($config);

        collect($sales)->chunk(900)->each(function ($sales) use ($client) {
            $response = $client->sendSalesHourly($sales);
            if (! $response->ok()) {
                throw new \Exception($response->json('errors.0.message'));
            }
        });

    }
}
