<?php

namespace Payever\Helper;

use Plenty\Modules\Frontend\Session\Storage\Contracts\FrontendSessionStorageFactoryContract;
use Plenty\Modules\Helper\Services\WebstoreHelper;
use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\Http\Request;

/**
 * Support for the headless PlentyONE Shop (plentyShop PWA).
 *
 * The PWA calls the core storefront REST API (/rest/storefront/...) through its middleware,
 * while plentyShop LTS (Ceres) uses the IO routes (/rest/io/...). In a PWA checkout payever
 * returns the customer to our callback routes on the PlentyONE system host (the PWA domain
 * doesn't serve plugin routes), and the callbacks then redirect to the PWA pages.
 */
class PwaHelper
{
    const STOREFRONT_MODE_AUTO = 'auto';
    const STOREFRONT_MODE_LTS = 'lts';
    const STOREFRONT_MODE_PWA = 'pwa';

    const STOREFRONT_REST_PREFIX = '/rest/storefront/';

    const REQUEST_CHANNEL = 'channel';
    const REQUEST_LANG = 'lang';
    const CHANNEL_PWA = 'pwa';

    /** Query parameter read by resources/js/pwa/payever.client.ts */
    const PWA_STATUS_PARAM = 'payever_status';
    const PWA_STATUS_SUCCESS = 'success';
    const PWA_STATUS_PENDING = 'pending';
    const PWA_STATUS_CANCEL = 'cancel';
    const PWA_STATUS_FAILURE = 'failure';

    const PWA_CONFIRMATION_PATH = '/confirmation';
    const PWA_CHECKOUT_PATH = '/checkout';
    const PWA_CART_PATH = '/cart';

    /**
     * @var ConfigRepository
     */
    private ConfigRepository $config;

    /**
     * @var WebstoreHelper
     */
    private WebstoreHelper $webstoreHelper;

    /**
     * @var Request
     */
    private Request $request;

    /**
     * @var FrontendSessionStorageFactoryContract
     */
    private FrontendSessionStorageFactoryContract $sessionStorage;

    /**
     * @param ConfigRepository $config
     * @param WebstoreHelper $webstoreHelper
     * @param Request $request
     * @param FrontendSessionStorageFactoryContract $sessionStorage
     */
    public function __construct(
        ConfigRepository $config,
        WebstoreHelper $webstoreHelper,
        Request $request,
        FrontendSessionStorageFactoryContract $sessionStorage
    ) {
        $this->config = $config;
        $this->webstoreHelper = $webstoreHelper;
        $this->request = $request;
        $this->sessionStorage = $sessionStorage;
    }

    /**
     * Whether the current checkout request comes from the PlentyONE Shop (PWA)
     *
     * @return bool
     */
    public function isPwaCheckout(): bool
    {
        $mode = (string) $this->config->get('Payever.storefront_mode');
        if ($mode === self::STOREFRONT_MODE_PWA) {
            return true;
        }

        if ($mode === self::STOREFRONT_MODE_LTS) {
            return false;
        }

        return strpos((string) $this->request->getRequestUri(), self::STOREFRONT_REST_PREFIX) === 0;
    }

    /**
     * Whether the current callback request belongs to a PWA checkout
     *
     * @return bool
     */
    public function isPwaCallback(): bool
    {
        return $this->request->get(self::REQUEST_CHANNEL) === self::CHANNEL_PWA;
    }

    /**
     * Base URL of the PlentyONE system that serves the plugin routes.
     * In a PWA checkout this is the API endpoint the PWA middleware talks to.
     *
     * @return string
     */
    public function getBackendBaseUrl(): string
    {
        $host = (string) $this->request->getHttpHost();
        if ($host !== '') {
            return 'https://' . $host;
        }

        return $this->getWebstoreDomain();
    }

    /**
     * Query parameters added to the payever return URLs of a PWA checkout
     *
     * @return array
     */
    public function getCallbackParams(): array
    {
        return [
            self::REQUEST_CHANNEL => self::CHANNEL_PWA,
            self::REQUEST_LANG => $this->getSessionLanguage(),
        ];
    }

    /**
     * @param int $orderId
     * @param string $accessKey
     * @param string $status
     * @return string
     */
    public function getConfirmationUrl(int $orderId, string $accessKey, string $status): string
    {
        $path = sprintf('%s/%d/%s', self::PWA_CONFIRMATION_PATH, $orderId, rawurlencode($accessKey));

        return $this->getPwaUrl($path, $status);
    }

    /**
     * @param string $status
     * @return string
     */
    public function getCheckoutUrl(string $status): string
    {
        return $this->getPwaUrl(self::PWA_CHECKOUT_PATH, $status);
    }

    /**
     * @param string $status
     * @return string
     */
    public function getCartUrl(string $status): string
    {
        return $this->getPwaUrl(self::PWA_CART_PATH, $status);
    }

    /**
     * @param string $path
     * @param string $status
     * @return string
     */
    private function getPwaUrl(string $path, string $status): string
    {
        return $this->getPwaBaseUrl()
            . $this->getLanguagePrefix()
            . $path
            . '?' . http_build_query([self::PWA_STATUS_PARAM => $status]);
    }

    /**
     * Public URL of the PlentyONE Shop: the configured one, or the client (webstore) domain
     *
     * @return string
     */
    public function getPwaBaseUrl(): string
    {
        $url = trim((string) $this->config->get('Payever.pwa_shop_url'));
        if ($url === '' || !preg_match('#^https?://#i', $url)) {
            $url = $this->getWebstoreDomain();
        }

        return rtrim($url, '/');
    }

    /**
     * The PWA serves the default language without a prefix and other languages under /{lang}
     *
     * @return string
     */
    private function getLanguagePrefix(): string
    {
        $lang = (string) $this->request->get(self::REQUEST_LANG);
        if (!preg_match('/^[a-z]{2}$/', $lang)) {
            return '';
        }

        $webstoreConfig = $this->webstoreHelper->getCurrentWebstoreConfiguration();
        $defaultLang = $webstoreConfig ? (string) $webstoreConfig->defaultLanguage : '';

        return $lang === $defaultLang ? '' : '/' . $lang;
    }

    /**
     * @return string
     */
    private function getSessionLanguage(): string
    {
        $localeSettings = $this->sessionStorage->getLocaleSettings();

        return $localeSettings ? (string) $localeSettings->language : '';
    }

    /**
     * @return string
     */
    private function getWebstoreDomain(): string
    {
        $webstoreConfig = $this->webstoreHelper->getCurrentWebstoreConfiguration();

        return $webstoreConfig ? rtrim((string) $webstoreConfig->domainSsl, '/') : '';
    }
}
