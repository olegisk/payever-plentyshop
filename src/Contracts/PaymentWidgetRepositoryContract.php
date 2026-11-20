<?php

namespace Payever\Contracts;

use Payever\Models\PaymentWidget;

interface PaymentWidgetRepositoryContract
{
    /**
     * @return PaymentWidget
     */
    public function create(): PaymentWidget;

    /**
     * @param PaymentWidget $widget
     * @return PaymentWidget
     */
    public function persist(PaymentWidget $widget): PaymentWidget;

    /**
     * @param PaymentWidget $widget
     * @return bool
     */
    public function delete(PaymentWidget $widget): bool;

    /**
     * @param string $widgetId
     * @return PaymentWidget|null
     */
    public function getByWidgetId(string $widgetId): ?PaymentWidget;

    /**
     * @return array
     */
    public function getSavedWidgets(): array;
}
