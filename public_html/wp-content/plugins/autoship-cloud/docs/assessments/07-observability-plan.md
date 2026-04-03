# Observability & Traceability Plan

> **Goal**: Enable enterprise-grade monitoring, logging, tracing, and health checks to support daily deployments, rapid troubleshooting, and operational excellence.

---

## Current Observability State: 2/10

| Capability | Status | Score |
|------------|--------|-------|
| Logging | Basic file logging exists | 3/10 |
| Structured Logging | None | 0/10 |
| Request Tracing | None | 0/10 |
| Metrics | None | 0/10 |
| Health Checks | None | 0/10 |
| Alerting | None | 0/10 |
| Error Tracking | None | 0/10 |
| Audit Logging | QuickLinks only | 4/10 |
| Feature Flags | FeatureManager exists | 5/10 |

---

## What We Need for Enterprise Readiness

### 1. Structured Logging

**Current State**:
- `app/Services/Logging/Logger.php` exists
- File-based logging via `FileSink`
- No JSON format
- No correlation IDs
- No log levels properly structured

**Target State**:
```json
{
  "timestamp": "2024-12-13T10:30:45.123Z",
  "level": "ERROR",
  "message": "Order creation failed",
  "correlation_id": "abc-123-def-456",
  "request_id": "req-789",
  "user_id": 12345,
  "context": {
    "order_id": 67890,
    "error_code": "PAYMENT_DECLINED",
    "gateway": "stripe"
  },
  "source": {
    "file": "OrderCreationService.php",
    "line": 145,
    "class": "OrderCreationService",
    "method": "create"
  },
  "environment": {
    "plugin_version": "2.10.5",
    "wp_version": "6.8.2",
    "wc_version": "10.0.4",
    "php_version": "8.1"
  }
}
```

**Implementation Plan**:

1. **Extend Logger Service**
   ```php
   // app/Services/Logging/JsonSink.php
   class JsonSink implements SinkInterface {
       public function log(string $level, string $message, array $context = []): void {
           $entry = [
               'timestamp' => gmdate('c'),
               'level' => $level,
               'message' => $message,
               'correlation_id' => $this->getCorrelationId(),
               'context' => $context,
               'source' => $this->getSource(),
               'environment' => $this->getEnvironment(),
           ];
           // Write to file or ship to log aggregator
       }
   }
   ```

2. **Add Correlation IDs**
   ```php
   // Generate on request start, pass through all operations
   class CorrelationIdMiddleware {
       public static function generate(): string {
           return wp_generate_uuid4();
       }

       public static function get(): string {
           static $id = null;
           return $id ??= self::generate();
       }
   }
   ```

3. **Add Log Shipping**
   - Option 1: File-based for log aggregator pickup
   - Option 2: Direct to CloudWatch, Datadog, etc.
   - Option 3: WordPress REST endpoint for log retrieval

---

### 2. Request Tracing

**Purpose**: Follow a request through all systems for debugging

**Implementation**:

```php
// app/Services/Tracing/TraceContext.php
class TraceContext {
    private string $traceId;
    private string $spanId;
    private array $spans = [];

    public function startSpan(string $name): Span {
        $span = new Span($name, $this->traceId, $this->spanId);
        $this->spans[] = $span;
        return $span;
    }

    public function endSpan(Span $span): void {
        $span->end();
        Logger::trace('Span completed', [
            'trace_id' => $this->traceId,
            'span_name' => $span->getName(),
            'duration_ms' => $span->getDurationMs(),
        ]);
    }
}
```

**Usage**:
```php
// In QPilot API client
public function createOrder(CreateOrderRequest $request): OrderResponse {
    $span = TraceContext::startSpan('qpilot.create_order');
    try {
        $response = $this->httpClient->post('/orders', $request);
        $span->setAttribute('status', 'success');
        return $response;
    } catch (Exception $e) {
        $span->setAttribute('status', 'error');
        $span->setAttribute('error', $e->getMessage());
        throw $e;
    } finally {
        TraceContext::endSpan($span);
    }
}
```

---

### 3. Health Checks

**Current State**: `src/api-health.php` exists but basic

**Target State**: Enterprise-ready health endpoints

```php
// app/Services/Health/HealthCheckService.php
class HealthCheckService {
    private array $checks = [];

    public function register(string $name, callable $check): void {
        $this->checks[$name] = $check;
    }

    public function run(): HealthReport {
        $results = [];
        foreach ($this->checks as $name => $check) {
            $start = microtime(true);
            try {
                $result = $check();
                $results[$name] = [
                    'status' => $result ? 'healthy' : 'unhealthy',
                    'duration_ms' => (microtime(true) - $start) * 1000,
                ];
            } catch (Exception $e) {
                $results[$name] = [
                    'status' => 'error',
                    'error' => $e->getMessage(),
                    'duration_ms' => (microtime(true) - $start) * 1000,
                ];
            }
        }
        return new HealthReport($results);
    }
}
```

**Health Check Endpoints**:

| Endpoint | Purpose | Frequency |
|----------|---------|-----------|
| `/autoship/health` | Basic liveness | Every 10s |
| `/autoship/health/ready` | Readiness with dependencies | Every 30s |
| `/autoship/health/detailed` | Full diagnostic (admin only) | On demand |

**Checks to Implement**:

```php
// Register health checks
$health->register('database', function() {
    global $wpdb;
    return $wpdb->get_var('SELECT 1') === '1';
});

$health->register('qpilot_api', function() {
    return QPilotHealthCheck::ping();
});

$health->register('payment_gateway', function() {
    return PaymentGatewayHealthCheck::verify();
});

$health->register('disk_space', function() {
    return disk_free_space('/') > 100 * 1024 * 1024; // 100MB
});

$health->register('memory', function() {
    return memory_get_usage() < ini_get('memory_limit') * 0.8;
});

$health->register('scheduled_orders_queue', function() {
    return ScheduledOrderQueue::isHealthy();
});
```

**Health Response Format**:

```json
{
  "status": "healthy",
  "timestamp": "2024-12-13T10:30:45Z",
  "version": "2.10.5",
  "checks": {
    "database": {
      "status": "healthy",
      "duration_ms": 2.3
    },
    "qpilot_api": {
      "status": "healthy",
      "duration_ms": 145.2,
      "details": {
        "endpoint": "https://api.qpilot.cloud",
        "response_time_ms": 143
      }
    },
    "payment_gateway": {
      "status": "healthy",
      "duration_ms": 89.5
    }
  },
  "environment": {
    "php_version": "8.1.0",
    "wp_version": "6.8.2",
    "wc_version": "10.0.4",
    "memory_usage_mb": 45,
    "memory_limit_mb": 256
  }
}
```

---

### 4. Feature Flags from Central Headquarters

**Current State**: `FeatureManager.php` with hardcoded flags

**Target State**: Remote-controlled feature flags

**Option 1: LaunchDarkly / Split.io Integration**

```php
// app/Services/FeatureFlags/RemoteFeatureManager.php
class RemoteFeatureManager implements FeatureFlagInterface {
    private LaunchDarklyClient $client;
    private array $cache = [];

    public function isEnabled(string $feature, array $context = []): bool {
        $cacheKey = $feature . ':' . md5(serialize($context));

        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $value = $this->client->variation($feature, $context, false);
        $this->cache[$cacheKey] = $value;

        Logger::debug('Feature flag evaluated', [
            'feature' => $feature,
            'enabled' => $value,
            'context' => $context,
        ]);

        return $value;
    }
}
```

**Option 2: WordPress-based Remote Flags**

```php
// Fetch flags from central server
class CentralFeatureManager {
    private const ENDPOINT = 'https://central.autoship.cloud/api/features';
    private const CACHE_TTL = 300; // 5 minutes

    public function syncFlags(): void {
        $response = wp_remote_get(self::ENDPOINT, [
            'headers' => ['Authorization' => 'Bearer ' . $this->getApiKey()],
        ]);

        if (!is_wp_error($response)) {
            $flags = json_decode(wp_remote_retrieve_body($response), true);
            set_transient('autoship_feature_flags', $flags, self::CACHE_TTL);
            Logger::info('Feature flags synced', ['count' => count($flags)]);
        }
    }

    public function isEnabled(string $feature): bool {
        $flags = get_transient('autoship_feature_flags') ?: [];
        return $flags[$feature]['enabled'] ?? false;
    }
}
```

**Feature Flag Categories**:

| Category | Examples |
|----------|----------|
| Feature Rollout | `new_checkout_flow`, `quicklinks_v2` |
| Kill Switches | `disable_qpilot_sync`, `maintenance_mode` |
| Experiments | `ab_test_pricing`, `new_ui_variant` |
| Operational | `enable_debug_logging`, `verbose_errors` |

---

### 5. Metrics Collection

**Implementation**:

```php
// app/Services/Metrics/MetricsCollector.php
class MetricsCollector {
    public function increment(string $metric, array $tags = []): void {
        // Record counter metric
    }

    public function gauge(string $metric, float $value, array $tags = []): void {
        // Record gauge metric
    }

    public function histogram(string $metric, float $value, array $tags = []): void {
        // Record histogram metric
    }

    public function timing(string $metric, float $durationMs, array $tags = []): void {
        // Record timing metric
    }
}
```

**Key Metrics to Track**:

| Metric | Type | Tags |
|--------|------|------|
| `autoship.orders.created` | Counter | status, source |
| `autoship.orders.processed` | Counter | status, gateway |
| `autoship.api.request_duration` | Histogram | endpoint, status |
| `autoship.api.errors` | Counter | endpoint, error_type |
| `autoship.sync.products` | Counter | status |
| `autoship.sync.duration` | Histogram | operation |
| `autoship.quicklinks.actions` | Counter | action_type, status |
| `autoship.rate_limit.hits` | Counter | endpoint |

---

### 6. Error Tracking (Sentry/Bugsnag)

**Implementation**:

```php
// app/Services/ErrorTracking/SentryErrorHandler.php
class SentryErrorHandler {
    public static function init(): void {
        \Sentry\init([
            'dsn' => get_option('autoship_sentry_dsn'),
            'environment' => wp_get_environment_type(),
            'release' => Autoship_Version,
            'traces_sample_rate' => 0.1,
        ]);

        \Sentry\configureScope(function (\Sentry\State\Scope $scope): void {
            $scope->setTag('plugin', 'autoship-cloud');
            $scope->setTag('wp_version', get_bloginfo('version'));
            $scope->setTag('wc_version', WC_VERSION ?? 'unknown');
        });
    }

    public static function captureException(Throwable $e, array $context = []): void {
        \Sentry\withScope(function (\Sentry\State\Scope $scope) use ($e, $context): void {
            $scope->setExtras($context);
            \Sentry\captureException($e);
        });
    }
}
```

---

### 7. Audit Logging

**Current State**: QuickLinks has audit logging

**Target State**: Comprehensive audit logging for all admin actions

```php
// app/Services/Audit/AuditService.php
class AuditService {
    public function log(string $action, array $data = []): void {
        global $wpdb;

        $wpdb->insert('wp_autoship_audit_log', [
            'action' => $action,
            'user_id' => get_current_user_id(),
            'ip_address' => $this->getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'data' => wp_json_encode($data),
            'created_at' => current_time('mysql'),
        ]);

        Logger::info('Audit: ' . $action, $data);
    }
}
```

**Actions to Audit**:

| Action | Data |
|--------|------|
| `settings.updated` | old_values, new_values, field |
| `product.synced` | product_id, status |
| `order.created` | order_id, customer_id |
| `order.modified` | order_id, changes |
| `payment.processed` | order_id, gateway, status |
| `user.login` | user_id, ip |
| `api.accessed` | endpoint, user_id |

---

## Implementation Roadmap

### Phase 1: Foundation (Week 1-2)

- [ ] Add JSON structured logging option
- [ ] Add correlation ID generation
- [ ] Implement basic health check endpoint
- [ ] Add Sentry/error tracking integration

### Phase 2: Tracing (Week 3-4)

- [ ] Add request tracing for QPilot API calls
- [ ] Add tracing for database operations
- [ ] Add tracing for WooCommerce hooks
- [ ] Create trace visualization tooling

### Phase 3: Metrics (Month 2)

- [ ] Implement metrics collection
- [ ] Add key business metrics
- [ ] Add operational metrics
- [ ] Set up dashboards

### Phase 4: Enterprise Features (Month 3)

- [ ] Implement remote feature flags
- [ ] Add comprehensive audit logging
- [ ] Create alerting rules
- [ ] Set up on-call integration

---

## Observability Dashboard

```
┌─────────────────────────────────────────────────────────────────────┐
│                    AUTOSHIP CLOUD DASHBOARD                          │
├─────────────────────────────────────────────────────────────────────┤
│  Health Status: ● HEALTHY                    Last Check: 10s ago    │
├─────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  Orders Today     API Latency      Error Rate      Active Users     │
│  ┌──────────┐    ┌──────────┐    ┌──────────┐    ┌──────────┐      │
│  │   156    │    │  145ms   │    │   0.1%   │    │    42    │      │
│  │  +12%    │    │  avg     │    │   ↓      │    │  online  │      │
│  └──────────┘    └──────────┘    └──────────┘    └──────────┘      │
│                                                                      │
│  ─────────────────────────────────────────────────────────────────  │
│                                                                      │
│  Recent Errors (Last Hour)                                          │
│  ├── PaymentDeclinedException (3)                                   │
│  ├── QPilotTimeoutException (1)                                     │
│  └── ValidationException (2)                                        │
│                                                                      │
│  Feature Flags                                                       │
│  ├── quicklinks_v2: ● Enabled                                       │
│  ├── new_checkout: ● Enabled (50% rollout)                          │
│  └── debug_mode: ○ Disabled                                         │
│                                                                      │
└─────────────────────────────────────────────────────────────────────┘
```

---

*Last Updated: December 2024*
*Review Frequency: Monthly*
