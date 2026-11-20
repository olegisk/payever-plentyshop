/**
 * payever client plugin for PlentyONE Shop (plentyShop PWA)
 *
 * Copy this file to "apps/web/app/plugins/payever.client.ts" of the PWA project.
 *
 * After the payment the payever plugin redirects the customer from the PlentyONE system back to the PWA:
 *   /confirmation/{orderId}/{accessKey}?payever_status=success|pending
 *   /checkout?payever_status=cancel|failure    (/cart for express widget payments)
 *
 * This plugin
 *   - redirects to the payever payment page when the storefront placed the order first
 *     (doPreparePayment returned "continue" and the payever plugin answered doExecutePayment with the redirect URL),
 *   - clears the cart after a successful or pending payment (the order may have been placed outside the PWA session),
 *   - shows the payment status that plentyShop LTS would show as a session notification,
 *   - removes the payever_status parameter from the URL.
 */

const STATUS_PARAM = 'payever_status';
const EXECUTE_PAYMENT_REQUEST = 'doexecutepayment';

// Kept inline so the plugin stays a single file and never overwrites the theme's own locale files
const MESSAGES: Record<string, Record<string, string>> = {
  en: {
    redirecting: 'Redirecting to payever...',
    pending: 'Your payment is being processed. You will be informed as soon as it is confirmed.',
    cancel: 'Payment has been canceled.',
    failure: 'Payment has been declined. Please try again or choose another payment method.',
  },
  de: {
    redirecting: 'Weiterleitung zu payever...',
    pending: 'Ihre Zahlung wird bearbeitet. Sie werden informiert, sobald sie bestätigt ist.',
    cancel: 'Die Zahlung wurde abgebrochen.',
    failure: 'Die Zahlung wurde abgelehnt. Bitte versuchen Sie es erneut oder wählen Sie eine andere Zahlungsart.',
  },
};

export default defineNuxtPlugin((nuxtApp) => {
  if (typeof window === 'undefined') {
    return;
  }

  const translate = (key: string): string => {
    const i18n = (nuxtApp as any).$i18n;
    const locale = String(i18n?.locale?.value ?? document.documentElement.lang ?? 'en').slice(0, 2);

    return (MESSAGES[locale] ?? MESSAGES.en)[key] ?? MESSAGES.en[key];
  };

  const handleStatus = async () => {
    const router = useRouter();
    const route = router.currentRoute.value;
    const status = route.query[STATUS_PARAM]?.toString();

    if (!status) {
      return;
    }

    const { send } = useNotification();

    switch (status) {
      case 'success':
      case 'pending': {
        usePlentyEvent().emit('module:clearCart', null);
        if (status === 'pending') {
          send({ type: 'warning', message: translate('pending') });
        }
        break;
      }
      case 'cancel': {
        send({ type: 'warning', message: translate('cancel') });
        break;
      }
      case 'failure': {
        send({ type: 'negative', message: translate('failure') });
        break;
      }
    }

    const query = { ...route.query };
    delete query[STATUS_PARAM];
    await router.replace({ path: route.path, query, hash: route.hash });
  };

  nuxtApp.hook('app:mounted', () => {
    handleStatus().catch((error: unknown) => console.error('[payever] Unable to handle payment status:', error));
  });

  installExecutePaymentRedirect(nuxtApp, translate);
});

/**
 * The PWA checkout ignores the doExecutePayment response and navigates to the confirmation page.
 * The SDK sends its requests with axios (XMLHttpRequest), so the response is read here: when it carries
 * a payever redirect, the browser goes to payever and the navigation to the confirmation page is cancelled.
 */
function installExecutePaymentRedirect(nuxtApp: any, translate: (key: string) => string) {
  let redirecting = false;

  const router = nuxtApp.$router;
  router?.beforeEach?.(() => (redirecting ? false : undefined));

  const redirect = (url: string) => {
    if (redirecting || !/^https:\/\//i.test(url)) {
      return;
    }
    redirecting = true;

    const overlay = document.createElement('div');
    overlay.style.cssText =
      'position:fixed;inset:0;background:rgba(255,255,255,.9);z-index:999999;display:flex;' +
      'align-items:center;justify-content:center;font-size:20px;';
    overlay.textContent = translate('redirecting');
    document.body.appendChild(overlay);

    window.location.assign(url);
  };

  const readRedirect = (responseText: string): string | null => {
    try {
      const payload = JSON.parse(responseText);
      const result = payload?.data?.type ? payload.data : payload;
      if ((result?.type === 'redirectUrl' || result?.type === 'redirect') && typeof result.value === 'string') {
        return result.value;
      }
    } catch {
      // not a JSON response, nothing to do
    }

    return null;
  };

  const originalOpen = XMLHttpRequest.prototype.open;
  const originalSend = XMLHttpRequest.prototype.send;

  XMLHttpRequest.prototype.open = function (this: XMLHttpRequest, method: string, url: string | URL, ...rest: any[]) {
    (this as any).__payeverExecutePayment = url.toString().toLowerCase().includes(EXECUTE_PAYMENT_REQUEST);
    return (originalOpen as any).call(this, method, url, ...rest);
  } as typeof XMLHttpRequest.prototype.open;

  XMLHttpRequest.prototype.send = function (this: XMLHttpRequest, body?: Document | XMLHttpRequestBodyInit | null) {
    if ((this as any).__payeverExecutePayment) {
      // Runs synchronously when the response arrives, before the SDK promise resolves,
      // so the redirect starts before the checkout navigates to the confirmation page
      this.addEventListener('readystatechange', () => {
        if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
          const redirectUrl = readRedirect(this.responseText);
          if (redirectUrl) {
            redirect(redirectUrl);
          }
        }
      });
    }
    return originalSend.call(this, body);
  };
}
