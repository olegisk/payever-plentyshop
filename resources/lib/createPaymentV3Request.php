<?php

require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/PayeverSdkProvider.php';

use Payever\Sdk\Payments\Http\MessageEntity\ChannelEntity;
use Payever\Sdk\Payments\Http\MessageEntity\PurchaseEntity;
use Payever\Sdk\Payments\Http\MessageEntity\ShippingOptionEntity;
use Payever\Sdk\Payments\Http\RequestEntity\CreatePaymentV3Request;
use Payever\Sdk\Payments\Http\MessageEntity\CompanyEntity;
use Payever\Sdk\Payments\Http\MessageEntity\CustomerEntity;
use Payever\Sdk\Payments\Http\MessageEntity\UrlsEntity;

$apiData = SdkRestApi::getParam('sdkData');
$payeverApi = new PayeverSdkProvider($apiData);

$params = SdkRestApi::getParam('payment_parameters');
$paymentRequest = new CreatePaymentV3Request();

$channelEntity = new ChannelEntity();
$channelEntity->setName('plentymarkets')
    ->setType('ecommerce')
    ->setSource($apiData['pluginVersion']);

$customerEntity = new CustomerEntity();
$customerEntity->setType(CUSTOMER_PERSON_ACCOUNT_TYPE)
    ->setPhone($params['phone'])
    ->setEmail($params['email']);

if (isset($params['company'])) {
    // company require for "organization" type
    $customerEntity->setType(CUSTOMER_ORGANIZATION_ACCOUNT_TYPE);
    $companyEntity = new CompanyEntity($params['company']);
    $paymentRequest->setCompany($companyEntity);
}

$urlsEntity = new UrlsEntity();
$urlsEntity->setSuccess($params['success_url'])
    ->setFailure($params['failure_url'])
    ->setCancel($params['cancel_url'])
    ->setNotification($params['notice_url'])
    ->setPending($params['pending_url']);

$purchaseEntity = new PurchaseEntity();
$purchaseEntity->setAmount($params['amount'])
    ->setDeliveryFee($params['fee'])
    ->setCurrency($params['currency']);

// set order data
$paymentRequest
    ->setChannel($channelEntity)
    ->setPaymentMethod($params['payment_method'])
    ->setPluginVersion($apiData['pluginVersion'])
    ->setLocale($params['locale'])
    ->setClientIp($params['client_ip'])
    ->setReference($params['order_id'])
    ->setUrls($urlsEntity)
    ->setPurchase($purchaseEntity)
    ->setCustomer($customerEntity)
    ->setPaymentData(json_encode(['force_redirect' => (bool)$params['force_redirect']]))
    ->setShippingAddress(json_encode($params['shipping_address']))
    ->setBillingAddress(json_encode($params['billing_address']))
    ->setCart(json_encode($params['cart']));

$shippingOptionEntity = new ShippingOptionEntity();
$shippingOptionEntity->setName($params['shipping_title'])
    ->setCarrier($params['shipping_method'])
    ->setPrice($params['fee'])
    ->setTaxAmount(0)
    ->setTaxRate(0);

try {
    $paymentRequest->setShippingOption($shippingOptionEntity);
    if (!$paymentRequest->isValid()) {
        throw new \Exception("Request not valid:" . json_encode($paymentRequest->toArray()));
    }

    return $payeverApi
        ->getPaymentsApiClient()
        ->createPaymentV3Request($paymentRequest)
        ->getResponseEntity()
        ->toArray();
} catch (\Exception $exception) {
    return [
        'error' => true,
        'error_msg' => $exception->getMessage(),
    ];
}
