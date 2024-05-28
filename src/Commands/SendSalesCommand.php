<?php

namespace RetailCosmos\TrxMallUploadSalesDataApi\Commands;

use App\Services\TrxMallUploadSalesDataApiService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Psr\Log\LoggerInterface;
use RetailCosmos\TrxMallUploadSalesDataApi\Clients\TrxApiClient;
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
    protected $signature = 'trx:send-sales
                {--date= : Date in Y-m-d format. Defaults to previous day.}
                {--store_identifier= : Store identifier. If not provided, all stores will be processed.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send sales in hourly(00-23) format to TRX API';

    private LoggerInterface $trxLogChannel;

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->trxLogChannel = Log::channel(config('trx_mall_upload_sales_data_api.log.channel', 'stack'));
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $message = 'start sending sales data to TRX api';
        $this->trxLogChannel->info($message);
        $this->info($message);

        try {
            $this->info('validating options');

            $this->validateOptions();

            $this->info('validating config');

            $this->validateConfig();

            $date = $this->option('date') ?? now()->subDay()->format('Y-m-d');

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

            $this->sendSales($sales);

            $this->info('sales data sent successfully');

            $message = 'end sending sales data to TRX api';

            $this->trxLogChannel->info($message);

            $this->info($message);

            $message = 'total sales sent: '.collect($sales)->flatten(1)->count();

            $this->info($message);

            $this->trxLogChannel->info($message);

            $this->notify($message, 'success');

            return 0;
        } catch (\Exception $e) {
            $message = 'error sending sales data to TRX api';

            $this->trxLogChannel->error($message, [
                'exception' => $e,
            ]);

            $this->error($message);

            $this->error($e->getMessage());

            $this->notify($e->getMessage(), 'error');

            return 1;
        }
    }

    /**
     * @throws \Exception
     */
    private function validateOptions(): void
    {
        if ($this->option('date') === null) {
            return;
        }

        $validator = validator($this->options(), [
            'date' => ['nullable', 'before_or_equal:today', 'date_format:Y-m-d'],
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
        $requiredConfigMessage = 'The :attribute needs to be configured';
        $validator = validator(config('trx_mall_upload_sales_data_api', []), [
            'log.channel' => 'required|string',
            'api.base_uri' => 'required|url',
            'api.grant_type' => 'required|string',
            'api.username' => 'required|string',
            'api.password' => 'required|string',
            'notifications.mail.name' => 'nullable|string',
            'notifications.mail.email' => 'nullable|email',
            'notifications.mail.trigger_failure_notifications_only' => 'required|boolean',
            'date_of_first_sales_upload' => 'required|date_format:Y-m-d|before_or_equal:today',
        ], [
            '*.required' => $requiredConfigMessage,
            '*.*.required' => $requiredConfigMessage,
        ], [
            'log.channel' => 'TRX_MALL_LOG_CHANNEL',
            'api.base_uri' => 'TRX_MALL_API_BASE_URI',
            'api.grant_type' => 'TRX_MALL_API_GRANT_TYPE',
            'api.username' => 'TRX_MALL_API_USERNAME',
            'api.password' => 'TRX_MALL_API_PASSWORD',
            'notifications.mail.name' => 'TRX_MALL_NOTIFICATION_MAIL_NAME',
            'notifications.mail.email' => 'TRX_MALL_NOTIFICATION_MAIL_EMAIL',
            'notifications.mail.trigger_failure_notifications_only' => 'TRX_MALL_NOTIFICATION_MAIL_TRIGGER_FAILURE_NOTIFICATIONS_ONLY',
            'date_of_first_sales_upload' => 'TRX_MALL_DATE_OF_FIRST_SALES_UPLOAD',
        ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }
    }

    /**
     * @return array<string,mixed>
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
     * @param  array<string,mixed>  $stores
     * @return array<string,mixed>
     *
     * @throws \Exception
     */
    private function getProcessedSales(string $date, array $stores): array
    {
        $trxSalesService = resolve(TrxMallUploadSalesDataApiService::class);
        $batchId = $this->getBatchIdForDate($date);

        $processedSales = [];
        foreach ($stores as $store) {
            $sales = $trxSalesService->getSales($date, $store['store_identifier']);
            if ($sales->isEmpty()) {
                continue;
            }
            $processedSales[$store['store_identifier']] = (new SalesDataProcessor($date, $batchId))
                ->process($sales, $store);
        }

        return $processedSales;
    }

    /**
     * @param  array<string,mixed>  $groupedSales
     *
     * @throws \Exception
     */
    private function sendSales(array $groupedSales): void
    {
        if (empty($groupedSales)) {
            return;
        }

        $client = new TrxApiClient(config('trx_mall_upload_sales_data_api.api', []));

        $messages = '';

        foreach ($groupedSales as $storeIdentifier => $sales) {
            if (empty($sales)) {
                continue;
            }

            $response = $client->sendSalesHourly($sales);

            $this->trxLogChannel->error('Response from Tangent system', [
                'response' => $responseBody = $response->body(),
            ]);

            if (! $response->ok()) {
                $messages .= 'Error while sending sales for store: '.$storeIdentifier.PHP_EOL.
                'Error: '.$responseBody.PHP_EOL;
            }
        }

        if ($messages !== '') {
            throw new \Exception($messages);
        }
    }

    private function notify(string $message, string $status): void
    {
        if ($status !== 'error' && config('trx_mall_upload_sales_data_api.notifications.mail.trigger_failure_notifications_only')) {
            return;
        }

        if ($email = config('trx_mall_upload_sales_data_api.notifications.mail.email')) {
            $name = config('trx_mall_upload_sales_data_api.notifications.mail.name');

            Notification::route('mail', [$email => $name])->notify(new TrxApiStatusNotification($name, $status, $message));
        }
    }

    private function getBatchIdForDate(string $date): int
    {
        return Carbon::parse($date)
            ->diffInDays(config('trx_mall_upload_sales_data_api.date_of_first_sales_upload')) + 1;
    }
}
