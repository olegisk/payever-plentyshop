<?php

require_once __DIR__ . '/PayeverSdkProvider.php';
$payeverApi = new PayeverSdkProvider(SdkRestApi::getParam('sdkData'));

$paymentWidgets = $payeverApi->getPaymentWidgetsApiClient()
    ->getWidgets()
    ->getResponseEntity()
    ->getResult();

$result = [];
foreach ($paymentWidgets as $paymentWidget) {
    if ($paymentWidget->getIsVisible()) {
        $result[$paymentWidget->getId()] = [
            'id' => $paymentWidget->getId(),
            'business_id' => $paymentWidget->getBusinessId(),
            'checkout_id' => $paymentWidget->getCheckoutId(),
            'type' => $paymentWidget->getType(),
            'name' => $paymentWidget->getName(),
        ];

        foreach ($paymentWidget->getPayments() as $payment) {
            if ($payment->getEnabled()) {
                $result[$paymentWidget->getId()]['payments'][] = $payment->getPaymentMethod();
            }
        }
    }
}

return $result;
