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
 *     only for payever payment methods (key "plenty_payever::..."),
 *   - clears the cart after a successful or pending payment (the order may have been placed outside the PWA session),
 *   - shows the payment status that plentyShop LTS would show as a session notification,
 *   - removes the payever_status parameter from the URL.
 */

const STATUS_PARAM = 'payever_status';
const EXECUTE_PAYMENT_REQUEST = 'doexecutepayment';
const PAYEVER_METHOD_KEY_PREFIX = 'plenty_payever::';

// Kept inline so the plugin stays a single file and never overwrites the theme's own locale files
const MESSAGES = {
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
} as const;

type MessageLocale = keyof typeof MESSAGES;
type MessageKey = keyof (typeof MESSAGES)['en'];
type Translate = (key: MessageKey) => string;

const isMessageLocale = (locale: string): locale is MessageLocale =>
  Object.prototype.hasOwnProperty.call(MESSAGES, locale);

export default defineNuxtPlugin((nuxtApp) => {
  if (typeof window === 'undefined') {
    return;
  }

  // Read the status before the router runs: after a cancellation the cart is already empty (the order exists),
  // so the PWA route middleware redirects /checkout to /cart and the query parameter would be lost
  const initialStatus = new URL(window.location.href).searchParams.get(STATUS_PARAM);

  const translate: Translate = (key) => {
    const i18n = (nuxtApp as any).$i18n;
    const locale = String(i18n?.locale?.value ?? document.documentElement.lang ?? 'en').slice(0, 2);

    return (isMessageLocale(locale) ? MESSAGES[locale] : MESSAGES.en)[key];
  };

  nuxtApp.hook('app:mounted', () => {
    handleStatus(initialStatus, translate).catch((error: unknown) =>
      console.error('[payever] Unable to handle payment status:', error),
    );
  });

  installExecutePaymentRedirect(nuxtApp, translate);
});

async function handleStatus(status: string | null, translate: Translate) {
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

  // The parameter may already be gone if the route middleware redirected
  const router = useRouter();
  const route = router.currentRoute.value;
  if (route.query[STATUS_PARAM] !== undefined) {
    const query = { ...route.query };
    delete query[STATUS_PARAM];
    await router.replace({ path: route.path, query, hash: route.hash });
  }
}

/**
 * The PWA checkout ignores the doExecutePayment response and navigates to the confirmation page.
 * The SDK sends its requests with axios (XMLHttpRequest), so the response is read here: when the order
 * was placed with a payever payment method and the response carries a redirect, the browser goes to payever
 * and the navigation to the confirmation page is cancelled.
 */
function installExecutePaymentRedirect(nuxtApp: any, translate: Translate) {
  let redirecting = false;
  let overlay: HTMLDivElement | null = null;

  const router = nuxtApp.$router;
  router?.beforeEach?.(() => (redirecting ? false : undefined));

  // "Back" from payever restores this page from the back/forward cache with the overlay and the
  // navigation lock still active, so reset both
  window.addEventListener('pageshow', (event: PageTransitionEvent) => {
    if (event.persisted) {
      redirecting = false;
      overlay?.remove();
      overlay = null;
    }
  });

  const redirect = (url: string) => {
    if (redirecting || !/^https:\/\//i.test(url)) {
      return;
    }
    redirecting = true;

    overlay = document.createElement('div');
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

  // doExecutePayment params: { orderId, paymentId }
  const readPaymentId = (body: unknown): number | null => {
    const match = typeof body === 'string' ? /"paymentId"\s*:\s*"?(\d+)/.exec(body) : null;

    return match?.[1] ? Number(match[1]) : null;
  };

  const isPayeverPaymentMethod = (paymentId: number | null): boolean => {
    if (paymentId === null) {
      return false;
    }
    const methods: Array<{ id: number; key?: string }> =
      nuxtApp.runWithContext(() => usePaymentMethods().data.value?.list) ?? [];
    const method = methods.find((paymentMethod) => Number(paymentMethod.id) === paymentId);

    return Boolean(method?.key?.startsWith(PAYEVER_METHOD_KEY_PREFIX));
  };

  const originalOpen = XMLHttpRequest.prototype.open;
  const originalSend = XMLHttpRequest.prototype.send;
  const executePaymentRequests = new WeakSet<XMLHttpRequest>();

  XMLHttpRequest.prototype.open = function (this: XMLHttpRequest, ...args: unknown[]) {
    if (String(args[1] ?? '').toLowerCase().includes(EXECUTE_PAYMENT_REQUEST)) {
      executePaymentRequests.add(this);
    } else {
      executePaymentRequests.delete(this);
    }

    return (originalOpen as (...openArgs: unknown[]) => void).apply(this, args);
  } as typeof XMLHttpRequest.prototype.open;

  XMLHttpRequest.prototype.send = function (this: XMLHttpRequest, ...args: Parameters<XMLHttpRequest['send']>) {
    if (executePaymentRequests.has(this) && isPayeverPaymentMethod(readPaymentId(args[0]))) {
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

    return originalSend.apply(this, args);
  };
}
