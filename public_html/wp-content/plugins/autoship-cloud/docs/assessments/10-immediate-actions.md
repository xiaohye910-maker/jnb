# Immediate Actions: What to Fix NOW

> **The Brutally Honest Priority List**: These are the things that must be fixed before any new feature work.

---

## The Harsh Truth

Before reading this list, understand:

1. **This codebase was built for speed, not quality** - The agency delivered working software but accumulated massive debt.

2. **You cannot safely change 70% of the code** - The legacy layer has zero tests and procedural architecture that cannot be tested.

3. **You are flying blind** - Coverage reports are broken, no error tracking, no structured logging.

4. **Every deployment is a gamble** - Manual SVN deployment with no automation, no rollback, no staging.

5. **The team cannot scale** - Everyone touches the same god files, parallel development is impossible.

**The good news**: The modern `app/` architecture proves it can be done right. QuickLinks is the template. The path is clear.

---

## Priority 0: This Week

### ACTION-001: Fix Test Coverage Reporting
**Why**: You cannot improve what you cannot measure.

**Do This**:
```bash
# 1. Check if Xdebug or PCOV is installed
php -m | grep -E 'xdebug|pcov'

# 2. If not, install PCOV (faster than Xdebug)
pecl install pcov

# 3. Enable in php.ini
echo "extension=pcov.so" >> /path/to/php.ini
echo "pcov.enabled=1" >> /path/to/php.ini

# 4. Run coverage
composer coverage

# 5. Verify build/coverage/index.html exists
```

**Success**: `composer coverage` generates HTML reports.

---

### ACTION-002: Set Up GitHub Actions CI
**Why**: Tests must run automatically or they won't run.

**Do This**:
Create `.github/workflows/tests.yml`:
```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.1'
          extensions: mbstring, xml, json
          coverage: pcov

      - name: Install dependencies
        run: composer install --prefer-dist --no-progress

      - name: Run compliance
        run: composer compliance
        continue-on-error: true  # Don't block yet, too many ignores

      - name: Run tests
        run: composer test
```

**Success**: Every PR shows test results.

---

### ACTION-003: Add Error Tracking (Sentry)
**Why**: You need to know when things break before users tell you.

**Do This**:
1. Create Sentry account (free tier available)
2. Get DSN
3. Add to plugin:

```php
// In autoship.php or app/Core/Plugin.php
if ( class_exists( '\Sentry\SentrySdk' ) ) {
    \Sentry\init([
        'dsn' => get_option('autoship_sentry_dsn', ''),
        'environment' => wp_get_environment_type(),
        'release' => Autoship_Version,
    ]);
}
```

**Success**: Errors appear in Sentry dashboard.

---

### ACTION-004: Create PHPCS Ignore Audit
**Why**: You need to know exactly what security debt exists.

**Do This**:
```bash
# Generate list of all ignores with file and line
grep -rn "phpcs:ignore" src/ --include="*.php" > phpcs-ignores.txt

# Count by type
grep -o "WordPress\.[A-Za-z\.]*" phpcs-ignores.txt | sort | uniq -c | sort -rn
```

Create `docs/assessments/phpcs-ignores-audit.md` with:
- Each ignore listed
- Severity (Critical, High, Medium, Low)
- Reason it was added (if known)
- Plan to fix

**Success**: Complete inventory of security debt.

---

## Priority 1: Next Two Weeks

### ACTION-005: Automate WordPress SVN Deployment
**Why**: Manual deployment is error-prone and blocks daily releases.

**Do This**:
1. Create `.distignore` file (see deployment doc)
2. Add GitHub Action for deployment:

```yaml
# .github/workflows/deploy.yml
name: Deploy to WordPress.org

on:
  release:
    types: [published]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: WordPress Plugin Deploy
        uses: 10up/action-wordpress-plugin-deploy@stable
        env:
          SVN_PASSWORD: ${{ secrets.SVN_PASSWORD }}
          SVN_USERNAME: ${{ secrets.SVN_USERNAME }}
          SLUG: autoship-cloud
```

**Success**: Create GitHub release → Plugin deploys automatically.

---

### ACTION-006: Add First Integration Test
**Why**: Start the integration test suite with the most critical path.

**Do This**:
```php
// tests/Integration/CheckoutIntegrationTest.php
class CheckoutIntegrationTest extends WP_UnitTestCase {
    public function test_autoship_product_in_cart_has_frequency_options(): void {
        // Setup
        $product = $this->createAutoshipProduct();
        WC()->cart->add_to_cart($product->get_id());

        // Action
        $cart_item = array_values(WC()->cart->get_cart())[0];

        // Assert
        $this->assertArrayHasKey('autoship_frequency_type', $cart_item);
    }
}
```

**Success**: One integration test passing in CI.

---

### ACTION-007: Add Basic Health Check Endpoint
**Why**: Load balancers and monitoring need to know if the app is healthy.

**Do This**:
```php
// Register endpoint
add_action('rest_api_init', function() {
    register_rest_route('autoship/v1', '/health', [
        'methods' => 'GET',
        'callback' => 'autoship_health_check',
        'permission_callback' => '__return_true',
    ]);
});

function autoship_health_check() {
    global $wpdb;

    $checks = [
        'database' => $wpdb->get_var('SELECT 1') === '1',
        'woocommerce' => class_exists('WooCommerce'),
        'qpilot_configured' => !empty(get_option('autoship_site_id')),
    ];

    $healthy = !in_array(false, $checks, true);

    return new WP_REST_Response([
        'status' => $healthy ? 'healthy' : 'unhealthy',
        'checks' => $checks,
        'version' => Autoship_Version,
        'timestamp' => gmdate('c'),
    ], $healthy ? 200 : 503);
}
```

**Success**: `GET /wp-json/autoship/v1/health` returns status.

---

## Priority 2: This Month

### ACTION-008: Fix Critical PHPCS Ignores in payments.php
**Why**: Payment handling has 79 PHPCS ignores. This is a PCI compliance risk.

**Do This**:
1. List all ignores in payments.php
2. Prioritize by type (InputNotSanitized first)
3. Fix top 20 most critical
4. Add tests for fixed code

**Success**: payments.php PHPCS ignores < 50.

---

### ACTION-009: Implement Structured Logging
**Why**: You cannot troubleshoot what you cannot trace.

**Do This**:
1. Create JSON sink for Logger service
2. Add correlation IDs
3. Update critical paths to use logging
4. Configure log rotation

**Success**: Logs in JSON format with correlation IDs.

---

### ACTION-010: Deprecate Legacy QPilot Client
**Why**: Two API clients causes confusion and bugs.

**Do This**:
1. Add deprecation notice to `src/QPilot/Client.php`
2. Audit all callers (grep for QPilotClient)
3. Create migration guide
4. Update callers one by one

**Success**: Deprecation notices in logs, migration started.

---

## Priority 3: Next Quarter

### ACTION-011: Extract OrderCreationService
**Why**: Order creation is critical business logic in untestable legacy code.

**Do This**:
1. Map order creation flow in scheduled-orders.php
2. Create `app/Services/Orders/OrderCreationService.php`
3. Define interface
4. Write unit tests
5. Create facade wrapping legacy
6. Gradually migrate logic

**Success**: OrderCreationService with 80% coverage.

---

### ACTION-012: Break Up scheduled-orders.php
**Why**: 8,162 lines in one file is unmaintainable.

**Do This**:
1. Map all 205 functions
2. Group by responsibility
3. Create modules:
   - ScheduledOrderModule
   - ScheduledOrderService
   - ScheduledOrderRepository
   - ScheduledOrderController (admin)
4. Move functions one group at a time
5. Maintain facades for compatibility

**Success**: No file > 1000 lines, all with tests.

---

### ACTION-013: Add Background Job Processing
**Why**: Synchronous operations block UI and timeout.

**Do This**:
1. Evaluate Action Scheduler (WooCommerce standard)
2. Integrate with product sync
3. Integrate with bulk operations
4. Add job monitoring

**Success**: Sync operations run in background.

---

## The Non-Negotiables

Before any new feature, these must be true:

```
□ Tests exist for the code being changed
□ CI passes
□ No new PHPCS ignores added
□ Error tracking captures exceptions
□ Logging is structured
□ Deployment is automated
```

---

## What NOT to Do

### Don't Start Here:
- ❌ Full rewrite of legacy code
- ❌ New features without tests
- ❌ Refactoring without integration tests first
- ❌ "Cleanup" that changes behavior
- ❌ Removing PHPCS ignores without fixing underlying issue

### Don't Skip These:
- ✅ Writing tests before changing code
- ✅ Using interfaces for new services
- ✅ Adding logging to new code
- ✅ Following the modern architecture patterns
- ✅ Documenting decisions

---

## Tracking Progress

Create a simple dashboard (can be in GitHub Projects or Notion):

```
Week 1:
├── [ ] ACTION-001: Fix coverage reporting
├── [ ] ACTION-002: Set up CI
├── [ ] ACTION-003: Add Sentry
└── [ ] ACTION-004: PHPCS audit

Week 2:
├── [ ] ACTION-005: Automate deployment
├── [ ] ACTION-006: First integration test
└── [ ] ACTION-007: Health check endpoint

Week 3-4:
├── [ ] ACTION-008: Fix payments.php ignores
├── [ ] ACTION-009: Structured logging
└── [ ] ACTION-010: Deprecate legacy QPilot
```

---

## Final Words

**This is a marathon, not a sprint.**

The codebase took years to get into this state. It won't be fixed in a week. But every action on this list moves you closer to:

- Deploying daily without fear
- Growing the team without chaos
- Sleeping at night without worrying about production

**Start with ACTION-001. Everything else follows.**

---

*Last Updated: December 2024*
*Owner: Technical Leadership*
*Review: Weekly until Phase 1 complete*
