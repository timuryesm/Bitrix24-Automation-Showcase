<?php

class DealProcessor {
    private $bitrixAPI;

    // Constructor that accepts a BitrixAPI object for API interaction
    public function __construct(BitrixAPI $bitrixAPI) {
        $this->bitrixAPI = $bitrixAPI;
    }

    // Method to process a specific deal by ID
    public function processDeal($dealId) {
        // Get deal information
        $deal = $this->bitrixAPI->getDeal($dealId);

        // Check if the "Cancellation Type" field has the value "Return and Replacement"
        if ($deal['result']['UF_CRM_1728979186202'] === 'Возврат и замена') {
            // Get product IDs for replacement and return from their respective fields
            $productsForReplacementIds = $deal['result']['UF_CRM_1730284775'];
            $productsForReturnIds = $deal['result']['UF_CRM_1730285200'];

            // Get all product rows from the original deal
            $originalProductRows = $this->bitrixAPI->getDealProductRows($dealId)['result'];

            // Filter products for return
            $returnProductRows = array_filter($originalProductRows, function ($productRow) use ($productsForReturnIds) {
                return in_array($productRow['PRODUCT_ID'], $productsForReturnIds);
            });

            // Filter products for replacement
            $replacementProductRows = array_filter($originalProductRows, function ($productRow) use ($productsForReplacementIds) {
                return in_array($productRow['PRODUCT_ID'], $productsForReplacementIds);
            });

            // Create a copy of the deal for returns
            $returnDealData = $deal['result'];
            $returnDealData['UF_CRM_1728979186202'] = 'Возврат';  // Set "Cancellation Type" to "Return"
            unset($returnDealData['ID']);  // Remove the ID to create a new deal
            $returnDealId = $this->bitrixAPI->addDeal($returnDealData)['result'];

            // Assign the return products to the new deal
            $this->bitrixAPI->setDealProductRows($returnDealId, $returnProductRows);

            // Create a copy of the deal for replacements
            $replacementDealData = $deal['result'];
            $replacementDealData['UF_CRM_1728979186202'] = 'Замена';  // Set "Cancellation Type" to "Replacement"
            unset($replacementDealData['ID']);  // Remove the ID to create a new deal
            $replacementDealId = $this->bitrixAPI->addDeal($replacementDealData)['result'];

            // Assign the replacement products to the new deal
            $this->bitrixAPI->setDealProductRows($replacementDealId, $replacementProductRows);

            // Delete the original deal after creating both copies
            $this->bitrixAPI->deleteDeal($dealId);

            return [
                'returnDealId' => $returnDealId,
                'replacementDealId' => $replacementDealId
            ];
        } else {
            return ['status' => 'No action required'];
        }
    }
}
