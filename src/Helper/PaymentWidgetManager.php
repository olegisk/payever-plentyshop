<?php

namespace Payever\Helper;

use Payever\Contracts\PaymentWidgetRepositoryContract;
use Payever\Models\PaymentWidget;
use Payever\Services\PayeverSdkService;
use Payever\Traits\Logger;

class PaymentWidgetManager
{
    use Logger;

    /**
     * @var PayeverSdkService
     */
    private PayeverSdkService $sdkService;

    /**
     * @var PaymentWidgetRepositoryContract
     */
    private PaymentWidgetRepositoryContract $widgetRepository;

    public function __construct(
        PayeverSdkService $sdkService,
        PaymentWidgetRepositoryContract $widgetRepository
    ) {
        $this->sdkService = $sdkService;
        $this->widgetRepository = $widgetRepository;
    }

    public function getUpdatedWidgets(): array
    {
        $paymentWidgets = $this->sdkService->call('getPaymentWidgets', []);
        $this->log(
            'debug',
            __METHOD__,
            'Payever::debug.getUpdatedWidgets',
            'Payment widget have been fetched.',
            [$paymentWidgets]
        );

        if (!is_array($paymentWidgets) || isset($paymentWidgets['error'])) {
            return [];
        }

        foreach ($paymentWidgets as $paymentWidget) {
            // Check if the widget exists
            $widget = $this->widgetRepository->getByWidgetId($paymentWidget['id']);
            if (!$widget) {
                $widget = $this->widgetRepository->create();
            }

            $widget->id = $paymentWidget['id'];
            $widget->type = $paymentWidget['type'];
            $widget->name = $paymentWidget['name'];
            $widget->businessId = $paymentWidget['business_id'];
            $widget->checkoutId = $paymentWidget['checkout_id'];

            $this->widgetRepository->persist($widget);
        }

        return $paymentWidgets;
    }

    /**
     * @return PaymentWidget[]
     */
    public function getSavedWidgets(): array
    {
        return $this->widgetRepository->getSavedWidgets();
    }
}
