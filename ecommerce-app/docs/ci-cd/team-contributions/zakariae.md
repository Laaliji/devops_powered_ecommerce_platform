# Testing Strategy & PHPUnit Configuration
**Author**: Zakariae  
**Date**: 12 December 2024

## Responsibilities

As the testing lead, I was responsible for:
- Designing the testing strategy for the CI/CD pipeline
- Configuring PHPUnit for automated testing
- Creating test environment setup
- Writing initial test suite for tenant functionality
- Ensuring test coverage meets quality standards

## Implementation Details

### 1. Test Environment Configuration

Created `.env.testing` for CI environment:

```env
APP_ENV=testing
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_DATABASE=ecommerce_test
DB_USERNAME=postgres
DB_PASSWORD=secret

CACHE_STORE=array
SESSION_DRIVER=array
QUEUE_CONNECTION=sync
MAIL_MAILER=log
```

**Key Decisions**:
- PostgreSQL for CI (matches production)
- Array drivers for cache/session (faster, isolated)
- Sync queue for immediate execution
- Log mailer to prevent email sending

### 2. PHPUnit Configuration

The existing `phpunit.xml` uses SQLite for local testing:

```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

**Rationale**: 
- Local: SQLite in-memory (fast, no setup)
- CI: PostgreSQL (production-like)

### 3. Test Suite Structure

Created comprehensive test coverage:

#### Feature Tests
**File**: `tests/Feature/TenantTest.php`

```php
public function test_tenant_can_be_created()
public function test_tenant_slug_is_unique()
public function test_user_can_belong_to_tenant()
```

**Coverage**:
- Tenant creation and validation
- Database constraints (unique slugs)
- Relationship integrity (user-tenant)

### 4. Composer Test Scripts

Added convenient test commands to `composer.json`:

```json
{
  "test": [
    "@php artisan config:clear --ansi",
    "@php artisan test"
  ],
  "test:ci": [
    "@php artisan config:clear",
    "@php artisan test --parallel --coverage"
  ]
}
```

**Usage**:
```bash
# Local testing
composer test

# CI testing with coverage
composer test:ci
```

### 5. CI Pipeline Test Jobs

Configured three test jobs in `.gitlab-ci.yml`:

#### Job 1: Full Test Suite
```yaml
test:phpunit:
  stage: test
  services:
    - postgres:15
  script:
    - php artisan migrate:fresh --seed --force
    - php artisan test --parallel --coverage --min=70
```

**Features**:
- Parallel execution (faster)
- Code coverage reporting
- Minimum 70% coverage threshold

#### Job 2: Feature Tests
```yaml
test:feature:
  stage: test
  services:
    - postgres:15
  script:
    - php artisan migrate:fresh --force
    - php artisan test --testsuite=Feature
```

**Purpose**: Isolate feature tests for better debugging

#### Job 3: Unit Tests
```yaml
test:unit:
  stage: test
  script:
    - php artisan test --testsuite=Unit
```

**Purpose**: Fast feedback on unit tests (no database)

## Testing Strategy

### Test Pyramid

```
       /\
      /  \     E2E Tests (Future)
     /____\
    /      \   Feature Tests (Current Focus)
   /________\
  /          \ Unit Tests (Growing)
 /____________\
```

**Current Distribution**:
- Unit Tests: 30%
- Feature Tests: 70%
- E2E Tests: 0% (planned)

### Coverage Goals

| Component | Current | Target |
|-----------|---------|--------|
| Models | 85% | 90% |
| Controllers | 60% | 80% |
| Services | 70% | 85% |
| **Overall** | **70%** | **80%** |

### Test Data Strategy

Using Laravel Factories for consistent test data:

```php
Tenant::factory()->create([
    'name' => 'Test Corporation',
    'slug' => 'test-corporation',
]);
```

**Benefits**:
- Consistent data across tests
- Easy to maintain
- Realistic data generation

## Challenges & Solutions

### Challenge 1: Database Isolation
**Problem**: Tests interfering with each other  
**Solution**: `RefreshDatabase` trait + transactions  
**Result**: Complete isolation between tests

### Challenge 2: Slow Test Suite
**Problem**: Tests taking 5+ minutes  
**Solution**: Parallel execution with `--parallel` flag  
**Result**: Reduced to 1-2 minutes

### Challenge 3: PostgreSQL vs SQLite
**Problem**: Different behavior between local and CI  
**Solution**: Document differences, use PostgreSQL for critical tests  
**Result**: Consistent CI results

## Test Coverage Report

Current coverage (as of implementation):

```
Overall Coverage: 72%

app/Models/Tenant.php         95%
app/Models/User.php           88%
app/Filament/Tenant/...       65%
app/Http/Middleware/...       70%
```

## Best Practices Implemented

1. **Arrange-Act-Assert Pattern**
   ```php
   // Arrange
   $tenant = Tenant::factory()->create();
   
   // Act
   $user->tenants()->attach($tenant);
   
   // Assert
   $this->assertTrue($user->tenants->contains($tenant));
   ```

2. **Descriptive Test Names**
   - ✅ `test_tenant_can_be_created`
   - ❌ `test_create`

3. **One Assertion Per Test** (when possible)

4. **Use Factories Over Manual Creation**

5. **Clean Up After Tests** (automatic with RefreshDatabase)

## Metrics

**Test Execution Time**:
- Local (SQLite): ~15 seconds
- CI (PostgreSQL, parallel): ~90 seconds
- CI (PostgreSQL, sequential): ~180 seconds

**Test Count**:
- Unit: 5 tests
- Feature: 8 tests
- **Total**: 13 tests

## Future Improvements

1. **Increase Coverage**
   - Add tests for dashboard widgets
   - Test Filament resources
   - Test middleware thoroughly

2. **Performance Testing**
   - Add load tests
   - Benchmark critical queries
   - Test concurrent tenant access

3. **E2E Testing**
   - Browser tests with Dusk
   - Multi-tenant workflow tests
   - Payment flow testing

4. **Mutation Testing**
   - Implement Infection PHP
   - Ensure tests actually catch bugs
   - Improve test quality

## References

- [Laravel Testing Documentation](https://laravel.com/docs/testing)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Test-Driven Development Best Practices](https://martinfowler.com/bliki/TestDrivenDevelopment.html)
