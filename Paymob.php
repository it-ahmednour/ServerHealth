<?php

class Paymob{
    ## set paymob info ##
    protected $PAYMOB_API_KEY = "";
    protected $PAYMOB_INTEGRATION_ID = 12345678;
    protected $PAYMOB_IFRAME_ID = 12345678;
    protected $PAYMOB_CRURENCY = "EGP";
    ###########################
    protected $authToken;
    protected $orderId;
    protected $amountCents;
    protected $paymentKey;
    protected $redirect_url;
    protected $client = [];
    
    public function __construct()
    {
        return $this;
    }

    public function makeOrder($order , $client){
        $this->client = $client;
        ## Step 1
        $auth = $this->cURL(
            'https://accept.paymobsolutions.com/api/auth/tokens',
            [
                'api_key' => $this->PAYMOB_API_KEY,
            ]
        );
        if(!$auth['token']) {
            throw new Exception('Auth-Error => ' . $auth['detail']);
        }
        $this->authToken = $auth['token'];
        ## Step 2
        $order = $this->cURL(
            'https://accept.paymobsolutions.com/api/ecommerce/orders',
            [
                'auth_token'        => $auth['token'],
                'api_source'        => 'INVOICE',
                'currency'          => $this->PAYMOB_CRURENCY,
                'delivery_needed'   => false,
                'amount_cents'      => $order['total_amount'] * 100,
                'items'             => $order['items'],
            ]
        );
        if(!$order['id']) {
            throw new Exception('Order-Error => ' . $order['detail']);
        }
        $this->orderId = $order['id'];
        $this->amountCents = $order['amount_cents'];
        ## Step 3
        $payment = $this->cURL(
            'https://accept.paymobsolutions.com/api/acceptance/payment_keys',
            [
                'auth_token'        => $this->authToken,
                'currency'          => $this->PAYMOB_CRURENCY,
                'amount_cents'      => $this->amountCents,
                'integration_id'    => $this->PAYMOB_INTEGRATION_ID,
                'order_id'          => $this->orderId,
                'expiration'        => 3600,
                'billing_data'      => $this->client,
                'lock_order_when_paid' => true,
            ]
        );
        if(!$payment['token']) {
            throw new Exception('Pyament-Error => ' . $payment['detail']);
        }
        $this->paymentKey = $payment['token'];
        return $this;
    }

    public function checkout(){
        $this->redirect_url = "https://accept.paymobsolutions.com/api/acceptance/iframes/{$this->PAYMOB_IFRAME_ID}?payment_token={$this->paymentKey}";
        return header('Location: ' . $this->redirect_url);
    }

    #########################################################################
    private function curl($url, $data){
        $ch = curl_init($url);
        $headers = array();
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $output = curl_exec($ch);
        curl_close($ch);
        return json_decode($output,true);
    }
}

### change order & client info ##
$client = [
    'first_name'    => 'ahmed',
    'last_name'     => 'nour',
    'phone_number'  => '01148740661',
    'email'         => 'it.ahmednour@gmail.com',
    'country'       => 'EG',
    'city'          => 'NA',
    'street'        => 'NA',
    'building'      => 'NA',
    'floor'         => 'NA',
    'apartment'     => 'NA',
];
$order = [
    'total_amount' => 50,
    'items' => [],
];
#############################
try{
    $paymob = new Paymob();
    $paymob->makeOrder($order , $client)->checkout();
}catch(Exception $e){
    echo $e->getMessage();
}
