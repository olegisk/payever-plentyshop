<?php

namespace Payever\Controllers;

use Payever\Helper\PaymentWidgetManager;
use Payever\Traits\Logger;
use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Class PaymentWidgetController
 */
class PaymentWidgetController extends Controller
{
    use Logger;

    /**
     * @var Request
     */
    private Request $request;

    /**
     * @var Response
     */
    private $response;

    /**
     * @var PaymentWidgetManager
     */
    private PaymentWidgetManager $paymentWidgetManager;

    public function __construct(
        Request $request,
        PaymentWidgetManager $paymentWidgetManager
    ) {
        parent::__construct();
        $this->request = $request;
        $this->response = pluginApp(Response::class);
        $this->paymentWidgetManager = $paymentWidgetManager;
    }

    public function synchronize(): SymfonyResponse
    {
        $paymentWidget = $this->paymentWidgetManager->getUpdatedWidgets();

        return $this->response->json($paymentWidget);
    }
}
