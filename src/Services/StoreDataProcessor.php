<?php

namespace RetailCosmos\TrxMallUploadSalesDataApi\Services;

class StoreDataProcessor
{
    /**
     * @param  array<string,mixed>  $storeData
     *
     * @throws \Exception
     */
    public function process(array $storeData): array
    {
        $this->validate($storeData);

        $this->prepare($storeData);

        return $storeData;
    }

    /**
     * @param  array<string,mixed>  $storeData
     *
     * @throws \Exception
     */
    private function validate(array $storeData): void
    {
        $validator = validator($storeData, [
            '*.machine_id' => ['required', 'distinct', 'numeric', 'min:1'],
            '*.store_identifier' => ['required', 'distinct'],
            '*.gst_registered' => ['required', 'boolean'],
        ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }

    }

    /**
     * @param  array<string,mixed>  $storeData
     */
    private function prepare(array &$storeData): void
    {
        foreach ($storeData as &$store) {
            $store['gstregistered'] = $store['gst_registered'] ? 'Y' : 'N';
            unset($store['gst_registered']);
            $store['machineid'] = $store['machine_id'];
            unset($store['machine_id']);
        }
    }
}
