<?php

class BitrixAPI {
    private $webhookUrl;

    // Class constructor that accepts the webhook URL
    public function __construct($webhookUrl) {
        $this->webhookUrl = $webhookUrl;
    }

    // Method to execute requests to the Bitrix API
    public function callMethod($method, $params = []) {
        $url = "{$this->webhookUrl}/$method";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    // Method to retrieve deal information by ID
    public function getDeal($dealId) {
        return $this->callMethod('crm.deal.get', ['id' => $dealId]);
    }

    // Method to create a new deal
    public function addDeal($dealData) {
        return $this->callMethod('crm.deal.add', ['fields' => $dealData]);
    }

    // Method to delete a deal by ID
    public function deleteDeal($dealId) {
        return $this->callMethod('crm.deal.delete', ['id' => $dealId]);
    }

    // Method to get product rows from a deal by deal ID
    public function getDealProductRows($dealId) {
        return $this->callMethod('crm.deal.productrows.get', ['id' => $dealId]);
    }

    // Method to set product rows for a deal by deal ID
    public function setDealProductRows($dealId, $productRows) {
        return $this->callMethod('crm.deal.productrows.set', ['id' => $dealId, 'rows' => $productRows]);
    }
}
