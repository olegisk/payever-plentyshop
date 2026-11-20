<?php

namespace Payever\Migrations;

use Payever\Models\PaymentWidget;
use Plenty\Modules\Plugin\DataBase\Contracts\Migrate;

class CreatePaymentWidgetTable
{
    /**
     * @param Migrate $migrate
     */
    public function run(Migrate $migrate)
    {
        $migrate->createTable(PaymentWidget::class);
    }
}
