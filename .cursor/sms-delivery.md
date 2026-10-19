# SMS setup

Outbound SMS is configured by an administrator at `/admin/sms`. Frog and Twilio are the transmitting gateways. Log only records a message and does not call a network. A future gateway is one adapter plus one entry in `config/sms.php`. Credentials are stored encrypted on `sms_provider_configs`. They are not read from the environment.

`sms_settings` was the first single-row form (log or Frog only). It has been dropped. `sms_provider_configs` is the only gateway record.

## Sending

Traffic uses the active gateway with the lowest priority number. If that gateway cannot be reached, the next active gateway is tried. "Switch traffic here" turns the others off. A test send stays on the gateway being tested.

Frog accepts `POST https://frogapi.wigal.com.gh/api/v3/sms/send` with a JSON body: `senderid`, `destinations` (each with `destination` and `msgid`), `message`, and `smstype` set to `text`. The headers are `API-KEY` and `USERNAME`. Balance is `GET /api/v3/balance`. Frog spends bundle units first (`data.bundles.SMS`) and uses cash credit (`data.cashbalance`) after the bundle is finished. Twilio is credit only.

An administrator may leave the base blank, store the host `https://frogapi.wigal.com.gh`, or store the `/api/v3` prefix. The application adds `/sms/send` or `/balance` once, so a base that already ends in `/api/v3` is not doubled. HTTP 405 is treated as a transport failure and the next active gateway is tried. The website host `frog.wigal.com.gh` is not the API and returns 405. The configuration page shows the exact send address that will be used.

Phone verification uses the same gateways. `PHONE_VERIFICATION_DRIVER=sms` sends the one-time code through every active provider, in priority order, with no separate Frog or Twilio path. Frog receives digits. Twilio and Log only receive E.164. The code is not written to the log. Log only is the local backup: switch it on in `/admin/sms` and a failed transmitting gateway falls through to it. Automated tests keep the in-memory `array` driver.

## Balance alerts

On each gateway that reports a balance, the administrator can set a cash-credit threshold and, for Frog, an SMS-bundle threshold. A blank field is not watched. After "Refresh balance", and once a day via `sms:watch-balances`, the application compares the snapshot:

- Frog bundles at or below the bundle threshold, while cash credit is still above its threshold: the messaging team is told that bundles are low and cash can still send.
- Cash credit at or below its threshold, and bundles low as well when the gateway uses bundles: the team is told the account needs a top-up.
- Twilio alerts on the cash threshold only.

The notice is in-app for users who can manage SMS gateways. The same level is not repeated within 24 hours. A balance that recovers clears the alert, so the next dip notifies again. The team is not sent an SMS about a low SMS balance.

## Delivery reports

The callback routes live in `routes/webhook.php`, outside the session:

- `/webhooks/sms/frog`
- `/webhooks/sms/twilio`

Save a Frog or Twilio configuration first. That generates the accepted URL on the gateway card and on the configuration page. Copy that URL into the provider console. The Frog URL includes a shared token; treat the whole URL as a secret. Twilio signs each report with the stored auth token, and the clean URL is also attached as `StatusCallback` when a message is sent.

Nothing is stored until the check passes. Frog compares the token in constant time and also accepts an HMAC-SHA256 of the raw body when that header is present. Twilio requires `X-Twilio-Signature`. Reports with no message id are ignored. The Frog token is not written into the webhook log. Calls are rate-limited, and stored reports are pruned after 30 days.

Status fields are mapped from `config/sms.php` for each provider, so a new gateway does not need a separate callback job. A matching report updates the message to delivered, undelivered, or failed. A later report cannot move a delivered message backwards.

## Message log

`/admin/sms/messages` lists every outbound message. Opening a row shows the text, the recipient, and the delivery reports. A failed or undelivered message can be sent again. That queues a new message and leaves the original row unchanged. Users with `sms.messages.view` can open the log. Gateway configuration stays on `sms.manage`.

## Bulk SMS

`/admin/sms/campaigns` prepares a campaign of at most 2,000 recipients and 520 characters. Content managers and the super administrator can message investors. Only `sms.manage` can message all staff.

Audiences:

- active approved investors
- all investors with a phone number
- investors with an overdue certificate renewal (listed, but certificate dates are not stored yet, so this audience is empty)
- all staff
- phone numbers separated by semicolons
- a CSV or Excel (`.xlsx`) file with `phone`, `name`, and `business_name`

A personal message for people already in IOMP uses one placeholder. Staff are addressed with `[[NAME]]`. Investors use either `[[NAME]]` or `[[BUSINESS_NAME]]`, not both. A typed number list and an uploaded file are separate sends, and those messages go out exactly as written. Each block has its own send button. A draft is not offered: the campaign page is the record of what was sent.

## Permissions

| Permission | Who |
| --- | --- |
| `sms.manage` | Super Administrator. Gateway credentials, thresholds, switching, and balance refresh. |
| `sms.messages.view` | Super Administrator and Content / Data Manager. Transmission log and resend. |
| `sms.campaigns.manage` | Super Administrator and Content / Data Manager. Bulk SMS, except the all-staff audience. |
