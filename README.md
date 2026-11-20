# payever Checkout for PlentyONE: experimental preview

> [!WARNING]
> **EXPERIMENTAL. For testing only, not for production.**
>
> This repository contains a **preview build** of the payever plugin for PlentyONE (formerly plentymarkets). It adds support for the new **PlentyONE Shop** (the headless plentyShop PWA).
>
> - The changes haven't been released yet and may still change or contain bugs.
> - Install the plugin only in a **separate test plugin set**, and use payever **sandbox** credentials.
> - For live shops, use the official **payever Checkout** plugin from the [plentyMarketplace](https://marketplace.plentyone.com/en/plugins/payment/payment-integrations).

## What's new in this preview

**3.7.2:**
- **Zinia is now Openbank Pay.** The Zinia payment methods are renamed to **Openbank Pay**, with new names, settings tabs, default titles and descriptions, and logos, in English and German. For example, *Openbank Pay Rechnungskauf* (`zinia_bnpl_de`), *Openbank Pay Ratenkauf* (`zinia_installment_de`) and *Openbank Pay Ratenkredit* (`zinia_lending_de`).
- The payment method codes don't change, so your settings and existing orders keep working.
- Existing payment methods are renamed after the next **Deploy plugin set**.

**3.7.1:**
- **Works when the PlentyONE Shop answers the payment preparation with "continue".** The plugin then creates the payever payment for the placed order, and the PWA client plugin redirects to payever.
- **New setting *PlentyONE system URL*.** It's for shops whose domain only forwards `/rest/*` to PlentyONE, so `/payment/payever/*` returns a 404 there.

**3.7.0:**

- **PlentyONE Shop (PWA) support:**
  - the payever checkout works through a redirect
  - after the payment the customer returns to the PWA order confirmation page, or to the checkout if the payment was cancelled or declined
  - webhooks and order actions work as in plentyShop LTS
- **New settings** in *Plugin set » payever » Configuration » Settings*:
  - **Storefront**: *Detect automatically* (default), *plentyShop LTS (Ceres)* or *PlentyONE Shop (PWA)*
  - **PlentyONE Shop URL**: optional, the public URL of the PWA, for example `https://shop.example.com`. If it's empty, the client domain is used.
  - **PlentyONE system URL**: optional, the address of your PlentyONE system, for example `https://p12345.my.plentysystems.com`. payever's return URLs and webhooks go there.
- **plentyShop LTS (Ceres)** keeps working as before, but is no longer required to install the plugin.
- **Fix:** customers returning from pending payments created with payever API v3 now reach the correct page.

**Not available in PlentyONE Shop yet:**
- the iframe checkout mode (the PWA always redirects to payever)
- the express widget (Finance Express)
- the B2B company search

## Requirements

- A PlentyONE system with access to *Plugins » Git* and *Plugins » Plugin set overview*.
- The **IO** plugin (version > 5.0.45) in the same plugin set.
- For plentyShop LTS: **Ceres** (plentyShop LTS) >= 5.0.36.
- A payever business account. Use **sandbox** credentials for testing: Client ID, Client Secret, Business UUID.
- For PlentyONE Shop: access to the PWA project, to add one file (see step 6).

## Installation

### 1. Add this Git repository
1. Open **Plugins » Git** and click **Add new repository** (+).
2. **Repository URL:** `https://github.com/olegisk/payever-plentyshop.git`
3. **User name / token:** the repository is public, so enter any value, for example `public` / `public`.
4. **Branch:** `main`.
5. Save. PlentyONE checks the repository and adds the plugin **payever** to the system.

### 2. Use a separate test plugin set
The preview uses the same plugin name as the official marketplace plugin. **A plugin set can't contain both.**
1. Open **Plugins » Plugin set overview**.
2. **Copy** your current plugin set, or create a new one, for example *payever preview*.
3. If the copy contains the marketplace version of **payever Checkout**, remove it from **this copy only**.

### 3. Install the plugin in the test set
1. Open the test plugin set and click **Add plugin**.
2. Select **payever** (source: Git) and install it.
3. Make sure **IO** is installed in the same set. For plentyShop LTS, plentyShop LTS (Ceres) must be installed too.
4. Check the plugin priorities: IO highest, then plentyShop LTS (if used), then payever.
5. Activate the payever plugin in the set.

### 4. Deploy and link the set
1. Click **Deploy plugin set** and wait until the build has finished without errors.
2. Link the set to a **test client** (*Plugin set » Link clients*), or open it with the plugin set **preview** (eye icon).
3. *Optional, for order actions in the back end:* select the set as the back-end plugin set in your user account settings.
4. *Optional, for the hourly and daily payever cron jobs:* cron jobs run only for the plugin set linked to the **standard client**.

### 5. Configure payever
1. Open **Setup » Assistants » Payment » payever Assistant**, select the client and run the assistant. Enter:
   - environment: **Sandbox**
   - your sandbox Client ID, Client Secret and Business UUID
   - payment methods, countries and currencies
2. Open **Plugin set » payever » Configuration » Settings**:
   - **Storefront:** leave on *Detect automatically*.
   - **PlentyONE Shop URL:** set it if your PWA domain differs from the client domain.
   - **PlentyONE system URL:** check `https://<your shop domain>/payment/payever/checkoutStatus` in a browser. If it shows your shop's 404 page, enter your system address here, for example `https://p12345.my.plentysystems.com`. It must start with `https://`.
   - **Create order before payment:** *Yes* is recommended for PlentyONE Shop.
3. Open **System » Orders » Payment » payever Checkout** and click **Synchronize** to load the payment methods from your payever account.
4. Open **Setup » Orders » Payment » Methods** and activate the payever payment methods for the test client.
5. **Deploy the plugin set again.** Changes can take a few minutes to appear because of caching.

### 6. PlentyONE Shop (PWA) only: add the payever client plugin (required)
1. Copy [`resources/js/pwa/payever.client.ts`](resources/js/pwa/payever.client.ts) from this repository into your PWA project as `apps/web/app/plugins/payever.client.ts`.
2. Build and deploy the PWA.

The plugin:
- **redirects to the payever payment page** when your shop places the order before the payment. Without it, the customer lands on the order confirmation of an unpaid order.
- empties the cart after a successful payment
- shows a message when the payment is pending, cancelled or declined

### 7. Optional: automatic order actions
In **Setup » Orders » Events** you can create events that run the payever procedures: *Refund*, *Partial return*, *Cancel* and *Shipping the payever payment*. Shipping is required for buy-now-pay-later and installment methods.

## Test checklist

For each payever payment method:
- [ ] The payever payment page opens. In PlentyONE Shop it always opens as a redirect.
- [ ] After a successful payment you land on the **order confirmation** page in the right language, and the cart is empty.
- [ ] The order has a payever payment with the transaction ID, and the order and payment statuses are correct.
- [ ] If you cancel on the payever page, you return to the checkout with the message *"Payment has been canceled"*.
- [ ] If the payment is declined, you return to the checkout with a decline message.
- [ ] Refund, cancel and shipping from the order (or through events) show the correct amounts in your payever account.

If something goes wrong, check **Data » Log** (filter by plugin **Payever**). For each checkout you should see `paymentMethodContent` or `executePayment`. If neither appears, the plugin set linked to your shop's client doesn't contain the payever plugin, or the selected payment method isn't a payever one.

Send us:
- the time of the test
- the order ID
- the payment method
- the storefront (plentyShop LTS or PlentyONE Shop)

## Updating the preview

New preview builds are pushed to the `main` branch. To update:
1. Open the test plugin set, open the **payever** plugin and click **Git** → **Pull**.
2. **Deploy plugin set** again.

## Removing the preview

Delete the test plugin set, or relink your clients to your original plugin set. Then remove the repository under **Plugins » Git**. Your original plugin set and the marketplace plugin are not affected.

## Feedback and support

Please report results and issues to your payever contact or to service@getpayever.com, mentioning **"PlentyONE preview"**.

## License

GNU Affero General Public License. See [LICENSE](LICENSE).
