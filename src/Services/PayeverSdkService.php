<?php

namespace Payever\Services;

use Payever\Helper\PayeverHelper;
use Payever\Helper\RoutesHelper;
use Payever\Traits\Logger;
use Plenty\Modules\Plugin\Libs\Contracts\LibraryCallContract;
use Plenty\Plugin\ConfigRepository;

class PayeverSdkService
{
    use Logger;

    /**
     * @var LibraryCallContract
     */
    private $libCall;

    /**
     * @var ConfigRepository
     */
    private $config;

    /**
     * @var PayeverHelper
     */
    private $payeverHelper;

    /**
     * @var RoutesHelper
     */
    private $routesHelper;

    /**
     * @param LibraryCallContract $libCall
     * @param ConfigRepository $config
     * @param PayeverHelper $payeverHelper
     * @param RoutesHelper $routesHelper
     */
    public function __construct(
        LibraryCallContract $libCall,
        ConfigRepository $config,
        PayeverHelper $payeverHelper,
        RoutesHelper $routesHelper
    ) {
        $this->libCall = $libCall;
        $this->config = $config;
        $this->payeverHelper = $payeverHelper;
        $this->routesHelper = $routesHelper;
    }

    /**
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function call(string $method, array $parameters)
    {
        if (!isset($parameters['sdkData'])) {
            $parameters['sdkData'] = [
                'clientId' => $this->config->get('Payever.clientId'),
                'clientSecret' => $this->config->get('Payever.clientSecret'),
                'slug' => $this->config->get('Payever.slug'),
                'environment' => $this->config->get('Payever.environment'),
            ];
        }

        $parameters['sdkData']['customSandboxUrl'] = $this->payeverHelper->getCustomSandboxUrl();
        $parameters['sdkData']['customLiveUrl'] = $this->payeverHelper->getCustomLiveUrl();
        $parameters['sdkData']['host'] = $this->routesHelper->getBaseUrl();
        $parameters['sdkData']['commandEndpoint'] = $this->routesHelper->getCommandEndpoint();
        $parameters['sdkData']['pluginVersion'] = $this->payeverHelper->getPluginVersion();

        try {
            $this->log(
                'debug',
                __METHOD__,
                'Payever::debug.sdkCallRequest',
                'Calling Payever::' . $method,
                [
                    'call' => $method,
                    'parameters' => []
                ]
            );

            $result = $this->libCall->call('Payever::' . $method, $parameters);

            $this->log(
                'debug',
                __METHOD__,
                'Payever::debug.sdkCallResponse',
                'Result of calling Payever::' . $method,
                [
                    'call' => $method,
                    'result' => $result
                ]
            );

            return $result;
        } catch (\Exception $exception) {
            $this->log(
                'critical',
                __METHOD__,
                'Payever::debug.sdkCallResponse',
                'Exception of calling Payever::' . $method,
                [
                    'call' => $method,
                    'error_message' => $exception->getMessage(),
                ]
            );

            throw $exception;
        }
    }
}
