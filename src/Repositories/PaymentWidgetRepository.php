<?php

namespace Payever\Repositories;

use Payever\Contracts\PaymentWidgetRepositoryContract;
use Payever\Models\PaymentWidget;
use Plenty\Modules\Plugin\DataBase\Contracts\DataBase;

class PaymentWidgetRepository implements PaymentWidgetRepositoryContract
{
    /**
     * @var DataBase
     */
    private $dataBase;

    /**
     * @param DataBase $dataBase
     */
    public function __construct(DataBase $dataBase)
    {
        $this->dataBase = $dataBase;
    }

    /**
     * @inheritDoc
     */
    public function create(): PaymentWidget
    {
        /** @var PaymentWidget $widget */
        $widget = pluginApp(PaymentWidget::class);

        return $widget;
    }

    /**
     * @inheritDoc
     */
    public function persist(PaymentWidget $widget): PaymentWidget
    {
        /** @var PaymentWidget $result */
        $result = $this->dataBase->save($widget);

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function delete(PaymentWidget $widget): bool
    {
        return $this->dataBase->delete($widget);
    }

    /**
     * @inheritDoc
     */
    public function getByWidgetId(string $widgetId): ?PaymentWidget
    {
        $query = $this->dataBase->query(PaymentWidget::class);
        $query->where('id', '=', $widgetId);

        $rows = $query->get();

        return $rows[0] ?? null;
    }

    /**
     * @return PaymentWidget[]
     */
    public function getSavedWidgets(): array
    {
        $query = $this->dataBase->query(PaymentWidget::class);

        return $query->get();
    }
}
