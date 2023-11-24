<?php

class wc_money extends WC_Payment_Gateway
{

    public function __construct()
    {
        $this->id = 'hubtel-pay';
        $this->icon = get_bloginfo('url') . '/wp-content/plugins/hubtel-pay/files/hubtel.png';
        $this->has_fields = true;
        $this->method_title = __('Hubtel Pay', 'hubtel-pay');
        $this->method_description = __('Accept payment from mobile money wallets and bank cards issued in Ghana.',
            'hubtel-pay');

        $this->init_form_fields();
        $this->init_settings();

//        $this->title = 'Hubtel Pay';
        $this->description = 'Pay with Momo or card';

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, [&$this, 'process_admin_options']);
        add_action('woocommerce_api_' . strtolower(get_class($this)), [$this, 'boomerang']);

        add_action('woocommerce_thankyou_' . $this->id, array($this, 'thank_you_page'));
        add_action('woocommerce_email_before_order_table', array($this, 'email_instructions'), 10, 3);

    }

    public function boomerang()
    {
        $this->tail("Stuff coming in 2...");
        $data = json_decode(file_get_contents('php://input'), true);
        $this->tail("Stuff coming in...");
        $this->tail(json_encode($data));
        if (empty($data)) {
            exit('ended');
        }
//        $trans_ref = $data["trans_ref"];
//        $trans_status = $data["trans_status"];
//        $order_id = substr($trans_ref, strpos($trans_ref, '_') + 1);
//        $order = wc_get_order($order_id);
//        if (strpos($trans_status, '000') !== false) {
//            //Success
//            $order->update_status('completed', sprintf(__('Authorized  %s %s on mobile wallet.', 'hubtel-pay'),
//                get_woocommerce_currency(), $order->get_total()));
//            $this->tail('Order marked as successful');
//        } else {
//            //Failed
//            $order->update_status('failed', sprintf(__('Mobile payment failed.', 'hubtel-pay')));
//            $this->tail('Order marked as failed');
//        }
    }

    public function payment_fields()
    {
    }

    public function process_admin_options()
    {
        parent::process_admin_options();
    }

    public function init_form_fields()
    {
        $this->form_fields = [
            'enabled' => [
                'title' => __('Enable/Disable', 'hubtel-pay'),
                'type' => 'checkbox',
                'label' => __('Enable Hubtel Pay', 'hubtel-pay'),
                'default' => 'yes',
            ],
            'merchant_options' => [
                'title' => __('Hubtel Merchant Options', 'hubtel-pay'),
                'type' => 'title',
                'description' => __("The following options affect where your funds will be sent when userpay.
                \r\n Use only if you have a fund collection account", 'hubtel-pay'),
                'id' => 'merchant_options'
            ],
            'mobile_number' => [
                'title' => __('Your Mobile Number', 'hubtel-pay'),
                'id' => 'hubtel_mobile_number',
                'type' => 'text',
                'description' => __('Your mobile number, as used on Hubtel', 'hubtel-pay'),
                'desc_tip' => true,
            ],
            'client_id' => [
                'title' => __('Client ID / API ID', 'hubtel-pay'),
                'id' => 'hubtel_client_id',
                'type' => 'text',
                'description' => __('Your Client Id (consumer) or API ID (merchant) issued on Hubtel.', 'hubtel-pay'),
                'desc_tip' => true
            ],
            'client_secret' => [
                'title' => __('Client Secret / API Key', 'hubtel-pay'),
                'id' => 'hubtel_client_secret',
                'type' => 'text',
                'description' => __('Your Client Secret (consumer) or API Key (merchant) issued on Hubtel.',
                    'hubtel-pay'),
                'desc_tip' => true
            ],
            'activation_code' => [
                'title' => __('Activation Key', 'hubtel-pay'),
                'id' => 'activation_code',
                'type' => 'password',
                'description' => __('Code to activate the plugin.',
                    'hubtel-pay'),
                'desc_tip' => true
            ],
        ];
    }

    public function process_payment($order_id)
    {
        $order = wc_get_order($order_id);
        $order_data = json_decode($order);
        $hp_settings = get_option('woocommerce_hubtel-pay_settings');
        $client_id = $hp_settings['client_id'];
        $client_secret = $hp_settings['client_secret'];
        $mobileNumber = $hp_settings['mobile_number'];

        //  get_bloginfo('url') . '/wc-api/hubtel-pay/'

        $payload = [
            'clientReference' => $this->generateId('WOO') . $order_data->id,
            'callbackUrl' => get_bloginfo('url') . '/wc-api/hubtel-pay/',
            'cancellationUrl' => get_bloginfo('url') . '/checkout/',
            'returnUrl' => get_bloginfo('url') . '/checkout/',
            'amount' => 1,
            'title' => 'Test title',
            'description' => 'Purchase made on ' . get_bloginfo('name'),
            'mobileNumber' => $mobileNumber
        ];

        $this->tail("Payload");
        $this->tail(json_encode($payload));

        $response = $this->postCall($payload, $client_id . ":" . $client_secret);

        $this->tail("Response");
        $this->tail($response);

        $body = json_decode($response, true);

        $this->tail("Body");
        $this->tail(json_encode($body));
        if ($body['code'] == 201) {
            $link = $body['data']['paylinkUrl'];
            $this->tail("Body: $link");
        }

//        $order->update_status('pending', __('Awaiting payment confirmation from user.', 'hubtel-pay'));
//        $order->reduce_order_stock();
//        WC()->cart->empty_cart();
//        return [
//            'result' => 'success',
//            'redirect' => $this->get_return_url($order)
//        ];
    }

    public function thank_you_page()
    {
        if ($this->instructions) {
            echo wpautop(wptexturize($this->instructions));
        }
    }

    public function email_instructions($order, $sent_to_admin, $plain_text = false)
    {
        if ($this->instructions && !$sent_to_admin && 'offline' === $order->payment_method && $order->has_status('on-hold')) {
            echo wpautop(wptexturize($this->instructions)) . PHP_EOL;
        }
    }

    public function tail($str)
    {
        @file_put_contents(__DIR__ . '/bro.txt', print_r($str, true) . "\r\n", FILE_APPEND | LOCK_EX);
    }

    public function generateId($prefix): string
    {
        $date = new DateTime ();
        $stamp = $date->format('Y-m-d');
        return $prefix . str_replace('-', '', $stamp) . mt_rand(10000, 50000);
    }

    public function postCall($payload, $key)
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "Authorization: Basic " . base64_encode($key)
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_URL => "https://devp-reqsendmoney-230622-api.hubtel.com/request-money/" . $payload['mobileNumber'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error) {
            $this->tail('POST ERR');
            $this->tail($error);
        }
        return $response;
    }

}