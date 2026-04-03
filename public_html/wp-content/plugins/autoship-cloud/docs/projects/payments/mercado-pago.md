# Mercado Pago Integration Feasibility Assessment

**Document Version:** 1.0
**Date:** January 2026
**Status:** Research Complete

---

## Executive Summary

**Verdict: Integration NOT Feasible with Current Architecture**

Mercado Pago cannot be integrated with Autoship Cloud/QPilot for scheduled recurring payments due to a fundamental security requirement: **CVV is required for every transaction**, even with saved cards. This prevents any merchant-initiated transaction (MIT) capability, which is the foundation of QPilot's scheduled order processing.

| Criteria | Requirement | Mercado Pago Capability | Status |
|----------|-------------|------------------------|--------|
| Off-session payments | Required | Not supported | :x: |
| Merchant-initiated transactions | Required | Not supported | :x: |
| Card tokenization for recurring | Required | CVV required each time | :x: |
| Customer-present payments | Nice to have | Fully supported | :white_check_mark: |

---

## Technical Analysis

### Autoship Cloud/QPilot Payment Architecture

QPilot processes scheduled orders using this flow:

```
1. Customer creates scheduled order at checkout
2. Payment method tokenized and stored
3. QPilot backend triggers payment at scheduled time
4. Payment processed WITHOUT customer presence
5. WooCommerce order created from webhook
```

**Key Requirement:** The payment gateway must support charging a stored payment method without customer interaction (off-session/MIT).

### Mercado Pago Payment Architecture

Mercado Pago uses a fundamentally different model:

```
1. Customer initiates payment
2. Card tokenized via JS SDK (session-based "super token")
3. Payment processed with token
4. Token expires after use
5. Saved cards require CVV re-entry for each payment
```

**Critical Limitation:** From Mercado Pago's documentation:

> "In order for a customer to be able to make a payment with their cards previously saved in their account, it is necessary that a new capture of the card's security code is performed. This is because, for security reasons, Mercado Pago cannot store this data."

This single requirement makes Mercado Pago incompatible with any subscription/recurring billing system that expects to charge cards without customer presence.

---

## Official WooCommerce Plugin Analysis

### Plugin Information

| Property | Value |
|----------|-------|
| Plugin Name | WooCommerce Mercado Pago |
| Publisher | Mercado Pago (Official) |
| Primary Gateway ID | `woo-mercado-pago-custom` |
| Token Storage | None (server-side super tokens) |
| WC Payment Tokens API | Not used |
| Off-Session Support | Not implemented |

### Gateway IDs

| Gateway | ID | Purpose |
|---------|-----|---------|
| BasicGateway | `woo-mercado-pago-basic` | Checkout Pro (redirect) |
| CustomGateway | `woo-mercado-pago-custom` | Credit/Debit cards |
| CreditsGateway | `woo-mercado-pago-credits` | Mercado Pago Credits |
| PixGateway | `woo-mercado-pago-pix` | PIX (Brazil) |
| TicketGateway | `woo-mercado-pago-ticket` | Boleto/Offline |
| PseGateway | `woo-mercado-pago-pse` | PSE (Colombia) |
| YapeGateway | `woo-mercado-pago-yape` | YAPE (Peru) |

### Data Storage

**Order Meta Keys:**
- `_Mercado_Pago_Payment_IDs` - Payment ID(s)
- `_used_gateway` - Gateway used
- `is_production_mode` - Environment flag
- `mp_installments` - Installment count
- `Mercado Pago - {id} - card_last_four_digits` - Card last 4

**User Meta Keys:**
- None stored by the plugin

**Critical Finding:** No customer IDs, card tokens, or reusable payment credentials are stored locally.

### Settings Keys

```php
// Credentials
'_mp_public_key_prod'         // Production public key
'_mp_public_key_test'         // Sandbox public key
'_mp_access_token_prod'       // Production access token
'_mp_access_token_test'       // Sandbox access token

// Test mode
'checkbox_checkout_test_mode' // 'yes' or 'no'
```

---

## API Capabilities Research

### Customer & Card Management APIs

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/v1/customers` | POST | Create customer |
| `/v1/customers/{id}/cards` | POST | Save card to customer |
| `/v1/customers/{id}/cards` | GET | List saved cards |
| `/v1/payments` | POST | Create payment |

### Can Cards Be Saved After Payment?

**Yes**, but with critical limitations:

```php
// 1. Create customer
POST /v1/customers
{ "email": "user@example.com" }

// 2. Save card (using same token from checkout)
POST /v1/customers/{customer_id}/cards
{ "token": "card_token_from_checkout" }

// 3. Card is saved, but...
// CVV is STILL required for every future payment
```

### MIT (Merchant-Initiated Transactions)

**Not Supported.** Mercado Pago does not offer:
- `point_of_interaction.type = "unattended"` flag
- Stored CVV capability
- True off-session payment processing
- MIT exemption flags for 3DS

### 3DS/SCA Considerations

| Region | SCA Required | 3DS Available |
|--------|--------------|---------------|
| Latin America | No (not EU) | Optional |
| Mexico | No | Recommended |
| Brazil | No | Recommended |
| Argentina | No | Available |

Even without SCA requirements, the CVV requirement remains a hard blocker.

---

## Alternative Approaches Evaluated

### Approach 1: Hybrid Integration (Custom Development)

**Concept:** Intercept successful payments, create Mercado Pago customers, save cards for "faster checkout."

**Outcome:** :x: Not viable

- Cards can be saved
- Customer IDs can be stored
- **CVV still required for every payment**
- Cannot enable scheduled order processing

### Approach 2: Mercado Pago Subscriptions API

**Concept:** Use MP's native subscription infrastructure instead of QPilot.

```
POST /preapproval
{
  "payer_email": "customer@email.com",
  "reason": "Monthly Subscription",
  "auto_recurring": {
    "frequency": 1,
    "frequency_type": "months",
    "transaction_amount": 100,
    "currency_id": "MXN"
  }
}
```

**Outcome:** :warning: Possible but incompatible with QPilot model

| Feature | QPilot Model | MP Subscriptions |
|---------|-------------|------------------|
| Billing control | QPilot triggers | MP triggers |
| Flexible scheduling | Full control | Fixed intervals |
| Product changes | Dynamic | Limited |
| Skip/pause | Customer portal | MP portal |
| Payment processing | QPilot backend | MP backend |

This would require a completely separate integration that bypasses QPilot's core functionality.

### Approach 3: Customer-Present Recurring

**Concept:** Email customers when payment is due, they click link and complete payment with CVV.

**Outcome:** :warning: Possible with poor UX

- Could use QuickLinks-style one-click flow
- Customer must be present for each payment
- High friction, likely high churn
- Not truly "automated" subscriptions

---

## Country Availability

| Country | Code | Subscriptions API | Saved Cards | Notes |
|---------|------|------------------|-------------|-------|
| Mexico | MLM | :white_check_mark: | :white_check_mark: (CVV) | Full support |
| Brazil | MLB | :white_check_mark: | :white_check_mark: (CVV) | Full support |
| Argentina | MLA | :white_check_mark: | :white_check_mark: (CVV) | Full support |
| Colombia | MCO | :white_check_mark: | :white_check_mark: (CVV) | Limited methods |
| Chile | MLC | :white_check_mark: | :white_check_mark: (CVV) | Limited methods |
| Peru | MPE | :white_check_mark: | :white_check_mark: (CVV) | Limited methods |

---

## Comparison with Supported Gateways

| Gateway | Off-Session | MIT Support | Token Storage | QPilot Compatible |
|---------|-------------|-------------|---------------|-------------------|
| Stripe | :white_check_mark: | :white_check_mark: | WC Tokens | :white_check_mark: |
| PayPal | :white_check_mark: | :white_check_mark: | Billing Agreement | :white_check_mark: |
| Authorize.Net | :white_check_mark: | :white_check_mark: | CIM Profile | :white_check_mark: |
| Braintree | :white_check_mark: | :white_check_mark: | Vault | :white_check_mark: |
| Square | :white_check_mark: | :white_check_mark: | Card on File | :white_check_mark: |
| **Mercado Pago** | :x: | :x: | None (CVV required) | :x: |

---

## Implementation Requirements (If Feasible)

For reference, if Mercado Pago supported MIT, these would be the integration points:

### Autoship Cloud Plugin Changes

| File | Change |
|------|--------|
| `app/Domain/PaymentMethodType.php` | Add `MERCADO_PAGO = 20` constant |
| `app/Domain/PaymentIntegrations/MercadoPagoPaymentIntegration.php` | Create integration class |
| `app/Domain/PaymentIntegrationFactory.php` | Add factory case |
| `src/payments.php` | Add gateway mappings and handlers |

### QPilot Backend Changes

| Component | Change |
|-----------|--------|
| Payment Method Type | Add `MercadoPago` enum value |
| Payment Processor | Implement MP API integration |
| Credential Storage | Store access tokens per merchant |

---

## Recommendations

### Short Term

1. **Do not pursue Mercado Pago integration** for scheduled orders
2. **Communicate limitation** to merchants in LATAM markets
3. **Recommend alternative gateways** that support MIT (Stripe, PayPal, etc.)

### Medium Term

1. **Monitor Mercado Pago roadmap** for MIT/off-session capability
2. **Evaluate Mercado Pago Subscriptions** as a separate product (not QPilot integrated)
3. **Consider QuickLinks enhancement** for customer-present recurring (high friction fallback)

### Long Term

1. **Engage with Mercado Pago** about enterprise MIT requirements
2. **Build Subscriptions API integration** as separate module if market demands
3. **Re-evaluate** if Mercado Pago adds CVV-less recurring capability

---

## Conclusion

Mercado Pago's security model, which requires CVV for every transaction, is fundamentally incompatible with Autoship Cloud/QPilot's scheduled order architecture. This is not a plugin limitation or implementation gap—it is a core platform design decision by Mercado Pago.

**The only viable path for recurring payments with Mercado Pago is to use their native Subscriptions API**, which operates independently of WooCommerce Subscriptions and QPilot. This would require a separate product strategy and integration effort.

---

## References

- [Mercado Pago - Create Customer and Card](https://www.mercadopago.com.ar/developers/en/docs/checkout-api/cards-and-customers-management/create-customer-and-card)
- [Mercado Pago - Receive Payments with Saved Cards](https://www.mercadopago.com.ar/developers/en/docs/checkout-api/cards-and-customers-management/receive-payments-with-saved-cards)
- [Mercado Pago - Subscriptions Overview](https://www.mercadopago.com.ar/developers/en/docs/subscriptions/overview)
- [Mercado Pago - 3DS Integration](https://www.mercadopago.com.ar/developers/en/docs/checkout-api/payment-management/integrate-3ds)
- [Mercado Pago - Cards API Reference](https://www.mercadopago.com.ar/developers/en/reference/cards/_customers_customer_id_cards/post)

---

## Document History

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0 | January 2026 | Technical Assessment | Initial research and analysis |
