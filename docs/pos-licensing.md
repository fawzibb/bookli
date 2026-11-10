# POS licensing — phase one

This phase adds the server foundation only. The desktop does not yet consume these licenses, and product/order synchronization and the local primary service are separate phases.

## Local setup

1. Deploy the listed PHP, Blade, config and route files.
2. Run `php artisan migrate --path=database/migrations/2026_11_07_000001_create_pos_licensing_tables.php --force`. This adds four POS-only tables; it does not reset existing data or redeem website codes.
3. Run `php artisan pos:generate-keys` once. Back up `storage/app/pos-licensing/private.pem` securely; never ship it to clients or expose storage via the web server. This command refuses to overwrite keys. Keep the public key for pinning in the desktop application.
4. Admin: `/admin/pos`. Owner: `/owner/pos-subscription`. These are independent of website subscription validity.

Only local/testing environments permit plain HTTP. Production requires HTTPS, correct trusted-proxy configuration, and secure server/private-key permissions. Keep the signing key across deployments. Key rotation requires an explicit client trust migration; deleting the old key is not a renewal mechanism.

## API contract

All responses must be treated as private and not cached by intermediaries. Requests use JSON and `Accept: application/json`.

- `POST /api/pos/subscription/redeem`: owner `phone`, `password`, `code`. Adds one calendar year after the later of now and the current POS expiry. A code is scoped to one business and can be redeemed once. Response: `status`, `ends_at`. Website activation codes cannot activate POS.
- `POST /api/pos/activate`: owner `phone`, `password`, persistent random UUID `installation_id`, display `name`, `role` (`primary` or `terminal`). First activate one primary, then terminals. Response includes `device_token`, signed `license`, `server_time`, `subscription_ends_at`, `offline_until`, device/business summaries, `signing_key_id`. Store token securely. Repeat activation of the same installation rotates its token without consuming another slot. Device limits include the primary.
- `GET /api/pos/license`: `Authorization: Bearer <device_token>`. Checks business, subscription and device state, then refreshes the signed lease. It never returns another device token.

Machine errors: `invalid_credentials`/`invalid_device_token` (401), `pos_expired` (402), `business_disabled`/`pos_suspended` (403), `device_limit_reached`/`primary_required`/`primary_already_registered`/`device_role_conflict`/`devices_exceed_limit`/`pos_code_used` (409), `invalid_pos_code` (422), missing signing setup (503). Input validation uses Laravel 422; throttling uses 429.

Licenses are compact JWTs signed RS256. Pin the public key independently in the desktop; never trust a key fetched from an arbitrary API response. Validate signature, algorithm, issuer `bookli-pos`, audience `bookli-pos-desktop`, business/device/installation binding, `nbf`, `iat` and `exp`. `exp` and `offline_until` equal min(server verification time + seven days, subscription expiry). Use UTC timestamps. A client must enforce the lease at startup and during use, handle clock rollback, securely persist its last trusted server time, and immediately lock work when an online response rejects entitlement. Network failure alone can use the remaining valid cached lease. Renewal/check remain available when work is locked.

Revoked/suspended devices can retain an already-issued offline lease until its deadline. Revocation frees the server slot immediately but cannot remotely erase an offline token. Terminal licenses also require an active registered primary; actual primary availability on the shop LAN is enforced by the later desktop/local-service phase. Installation UUIDs and tokens are credentials, not hardware attestation.

## Integrity and tests

Activation, configuration, revocation and redemption serialize through a transaction locking the business row. Database uniqueness backs device installations, code hashes and single redemption. Only code hashes and token hashes are stored; full new codes appear once in the authenticated admin session. Annual renewal preserves an audit row with previous and new expiry.

Run only the isolated licensing test class with PDO SQLite enabled: `php -d extension=php_pdo_sqlite.dll vendor/bin/phpunit --filter PosLicensingTest`. The class explicitly forces an in-memory database before its database-reset trait runs. Never run database-reset tests against a live database.

The pre-existing default `Feature/ExampleTest` does not initialize its database: on a fresh in-memory database its home-page request fails because `site_settings` is absent. The licensing test class also checks the home page and existing website subscription page after creating their schema fixture. This is a test-fixture limitation, not a POS runtime migration requirement.

Future phases: desktop activation/lock, one primary service and LAN terminals, POS-owned catalog publishing, durable order synchronization with unique event IDs. No inventory or cashier movements are introduced by licensing.
