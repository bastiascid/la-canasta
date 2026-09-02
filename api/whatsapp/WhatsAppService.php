<?php
class WhatsAppService {
    private $accessToken;
    private $phoneNumberId;
    private $apiUrl = 'https://graph.facebook.com/v17.0/';

    public function __construct() {
        $this->accessToken = getenv('WA_ACCESS_TOKEN');
        $this->phoneNumberId = getenv('WA_PHONE_NUMBER_ID');
    }

    public function sendMessage($to, $text) {
        if (empty($this->accessToken) || empty($this->phoneNumberId)) {
            error_log("WhatsAppService: Tokens no configurados.");
            return false;
        }

        $url = $this->apiUrl . $this->phoneNumberId . '/messages';
        
        $data = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'text',
            'text' => [
                'body' => $text
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->accessToken,
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            error_log("WhatsAppService Error: " . $response);
            return false;
        }
        return true;
    }
}
