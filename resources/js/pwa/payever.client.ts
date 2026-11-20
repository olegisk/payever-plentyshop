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
 *   - clears the cart after a successful or pending payment (the order may have been placed outside the PWA session),
 *   - shows the payment status that plentyShop LTS would show as a session notification,
 *   - removes the payever_status parameter from the URL.
 */

const STATUS_PARAM = 'payever_status';

// Kept inline so the plugin stays a single file and never overwrites the theme's own locale files
const MESSAGES: Record<string, Record<string, string>> = {
  en: {
    pending: 'Your payment is being processed. You will be informed as soon as it is confirmed.',
    cancel: 'Payment has been canceled.',
    failure: 'Payment has been declined. Please try again or choose another payment method.',
  },
  de: {
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
});
