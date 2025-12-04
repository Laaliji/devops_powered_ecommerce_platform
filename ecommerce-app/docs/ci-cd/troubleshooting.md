# Troubleshooting CI/CD Pipeline

Common issues and solutions for the GitLab CI/CD pipeline.

## Pipeline Failures

### Build Stage Fails

#### Issue: Composer Install Timeout
```
Error: The process has been signaled with signal "9"
```

**Solution**:
```bash
# Increase memory limit in .gitlab-ci.yml
before_script:
  - export COMPOSER_MEMORY_LIMIT=-1
```

#### Issue: NPM Install Fails
```
Error: EACCES: permission denied
```

**Solution**:
```bash
# Use npm ci instead of npm install
- npm ci --cache .npm --prefer-offline
```

### Test Stage Fails

#### Issue: Database Connection Refused
```
SQLSTATE[08006] Connection refused
```

**Solution**:
1. Check PostgreSQL service is running
2. Verify `DB_HOST=postgres` in `.env.testing`
3. Ensure service name matches in `.gitlab-ci.yml`

#### Issue: Tests Pass Locally but Fail in CI
**Possible Causes**:
- SQLite vs PostgreSQL differences
- Missing environment variables
- Timezone issues

**Solution**:
```bash
# Run tests with PostgreSQL locally
DB_CONNECTION=pgsql php artisan test
```

### Quality Stage Fails

#### Issue: Code Style Violations
```
Laravel Pint found 15 style violations
```

**Solution**:
```bash
# Auto-fix locally
composer pint

# Commit fixes
git add .
git commit -m "style: Fix code style violations"
```

#### Issue: PHPStan Errors
```
Found 5 errors
```

**Solution**:
1. Run locally: `composer phpstan`
2. Fix reported issues
3. Add to `phpstan.neon` if false positive

## Performance Issues

### Slow Pipeline

**Symptoms**: Pipeline takes > 10 minutes

**Solutions**:
1. Check cache is working
2. Use `--parallel` for tests
3. Optimize dependencies

### Cache Not Working

**Check**:
```yaml
cache:
  key: ${CI_COMMIT_REF_SLUG}
  paths:
    - vendor/
    - node_modules/
```

**Solution**: Ensure paths exist and are writable

## Environment Issues

### Missing Environment Variables

**Error**: `APP_KEY is not set`

**Solution**:
1. Go to GitLab → Settings → CI/CD → Variables
2. Add `APP_KEY` (run `php artisan key:generate --show`)
3. Mark as protected and masked

## Getting Help

1. Check [Zakariae's testing docs](./team-contributions/zakariae.md)
2. Review [setup guide](./setup-guide.md)
3. Check GitLab pipeline logs
4. Ask team members
