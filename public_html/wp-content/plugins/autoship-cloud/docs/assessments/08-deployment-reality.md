# The Harsh Truth About WordPress Plugin Deployment

> **Reality Check**: Manual WordPress SVN deployment is a nightmare that actively prevents daily deployments and introduces significant risk.

---

## Current Deployment Process (The Pain)

### Step-by-Step Nightmare

```
1. Develop and test locally
2. Push to Git (GitHub/GitLab)
3. Manually export to clean directory (exclude .git, tests, node_modules, etc.)
4. Connect to WordPress SVN (svn checkout or update)
5. Manually copy files to SVN trunk
6. Carefully manage assets/ folder separately
7. Update readme.txt with changelog
8. Review changes with svn status
9. Add new files with svn add
10. Remove deleted files with svn delete
11. Commit to SVN trunk
12. Tag new version in SVN
13. Wait 5-30 minutes for WordPress.org to update
14. Verify on WordPress.org
15. Pray nothing went wrong
```

### What Can Go Wrong (And Does)

| Problem | Frequency | Impact |
|---------|-----------|--------|
| Forgot to add a new file | Common | Plugin crashes on update |
| Forgot to remove a deleted file | Common | Bloat, confusion |
| Committed wrong version | Occasional | Users get broken update |
| SVN conflict | Occasional | Deployment blocked |
| Forgot to update readme.txt | Common | Wrong info on WordPress.org |
| Committed vendor/ or node_modules | Occasional | Massive bloat |
| Forgot to update assets/ | Common | Old screenshots/icons |
| SVN timeout during large commit | Occasional | Partial deployment |
| Mistyped tag version | Occasional | Version confusion |

### Why This Blocks Daily Deployment

1. **Manual Process = Human Error**
   - Each step is an opportunity for mistake
   - No automation = no reliability
   - No rollback mechanism

2. **Slow Feedback Loop**
   - 5-30 minute wait for WordPress.org
   - Cannot test in production environment
   - Users are the first to find issues

3. **No Staging Environment**
   - Cannot test actual WordPress.org deployment
   - Must commit to live before knowing if it works
   - Rollback means another full deployment

4. **SVN vs Git Mismatch**
   - Development in Git
   - Deployment in SVN
   - Manual synchronization required
   - History management is painful

---

## The Psychological Tax

```
Developer Experience:
├── Anxiety before every release
├── Fear of breaking production
├── Time wasted on manual steps
├── Context switching (Git → SVN)
├── Deployment avoidance behavior
└── Pressure leads to shortcuts and mistakes
```

---

## What Other Plugins Do (Automation Options)

### Option 1: GitHub Actions to WordPress SVN

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
          VERSION: ${{ github.event.release.tag_name }}
```

**Benefits**:
- Triggered by GitHub release
- Automated file synchronization
- Automatic version tagging
- No manual SVN interaction

**Still Required**:
- Manual release creation in GitHub
- Proper version in plugin header
- Updated readme.txt

### Option 2: Dedicated Deploy Script

```bash
#!/bin/bash
# deploy-to-wordpress.sh

set -e

# Configuration
PLUGIN_SLUG="autoship-cloud"
SVN_REPO="https://plugins.svn.wordpress.org/$PLUGIN_SLUG"
SVN_USER="your-username"
BUILD_DIR="./build"
SVN_DIR="./svn"

# Clean and build
echo "Building plugin..."
rm -rf $BUILD_DIR
mkdir -p $BUILD_DIR

# Copy files (excluding dev files)
rsync -av --exclude-from='.distignore' . $BUILD_DIR/

# Checkout SVN
echo "Checking out SVN..."
svn checkout $SVN_REPO/trunk $SVN_DIR/trunk --username $SVN_USER

# Sync files
echo "Syncing files..."
rsync -av --delete $BUILD_DIR/ $SVN_DIR/trunk/

# SVN operations
cd $SVN_DIR/trunk
svn status | grep '^!' | awk '{print $2}' | xargs -I% svn delete %
svn status | grep '^?' | awk '{print $2}' | xargs -I% svn add %

# Commit
echo "Committing..."
svn commit -m "Deploy version $VERSION" --username $SVN_USER

# Tag
echo "Tagging..."
svn copy $SVN_REPO/trunk $SVN_REPO/tags/$VERSION -m "Tag version $VERSION"

echo "Done!"
```

### Option 3: Composer Deploy Package

```json
{
  "scripts": {
    "build": [
      "rm -rf build",
      "mkdir build",
      "rsync -av --exclude-from='.distignore' . build/"
    ],
    "deploy": [
      "@build",
      "./scripts/deploy-to-wordpress.sh"
    ]
  }
}
```

---

## Recommended Deployment Pipeline

### Phase 1: GitHub Release Flow

```
Developer                    GitHub                     WordPress.org
    │                           │                             │
    ├── Push to main ──────────>│                             │
    │                           │                             │
    ├── Create Release ────────>│                             │
    │                           ├── Trigger GitHub Action ───>│
    │                           │                             │
    │                           ├── Build & Test              │
    │                           │                             │
    │                           ├── Deploy to SVN ───────────>│
    │                           │                             │
    │                           ├── Verify Deployment ───────>│
    │                           │                             │
    │<── Notification ──────────┤                             │
    │                           │                             │
```

### Phase 2: Full CI/CD Pipeline

```yaml
name: CI/CD Pipeline

on:
  push:
    branches: [main, develop]
  pull_request:
    branches: [main]
  release:
    types: [published]

jobs:
  # Run on every push
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.1'
      - name: Install dependencies
        run: composer install
      - name: Run compliance
        run: composer compliance
      - name: Run tests
        run: composer test

  # Run on release only
  deploy:
    needs: test
    if: github.event_name == 'release'
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4

      # Deploy to staging first (if available)
      - name: Deploy to Staging
        run: ./scripts/deploy-staging.sh
        env:
          STAGING_HOST: ${{ secrets.STAGING_HOST }}

      # Run E2E tests on staging
      - name: Run E2E Tests
        run: npx playwright test
        env:
          BASE_URL: ${{ secrets.STAGING_URL }}

      # Deploy to WordPress.org
      - name: Deploy to WordPress.org
        uses: 10up/action-wordpress-plugin-deploy@stable
        env:
          SVN_PASSWORD: ${{ secrets.SVN_PASSWORD }}
          SVN_USERNAME: ${{ secrets.SVN_USERNAME }}

      # Notify
      - name: Notify Slack
        uses: 8398a7/action-slack@v3
        with:
          status: ${{ job.status }}
          fields: repo,message,commit,author
```

---

## .distignore File (What NOT to Deploy)

```
# .distignore - Files to exclude from WordPress.org deployment

# Development
.git
.github
.gitignore
.gitattributes
.distignore

# IDE
.idea
.vscode
*.sublime-project
*.sublime-workspace

# Testing
tests
phpunit.xml
phpunit.xml.dist
.phpunit.cache
.phpunit.result.cache
codecov.yml

# Build tools
node_modules
package.json
package-lock.json
composer.json
composer.lock
Gruntfile.js
gulpfile.js
webpack.config.js

# Documentation (keep docs in plugin, but not dev docs)
docs/assessments
docs/plan.md
docs/tasks.md
CLAUDE.md
CONTRIBUTING.md

# Source files (if compiled)
src/scss
*.scss

# Misc
.DS_Store
Thumbs.db
*.log
*.tmp
.env
.env.*
```

---

## Version Management

### Current Pain

```php
// Version defined in multiple places:
// 1. autoship.php header
// 2. Autoship_Version constant
// 3. readme.txt Stable tag
// 4. package.json (if exists)
// 5. composer.json (if versioned)
```

### Solution: Single Source of Truth

```bash
#!/bin/bash
# scripts/bump-version.sh

NEW_VERSION=$1

# Update all version locations
sed -i "s/Version: .*/Version: $NEW_VERSION/" autoship.php
sed -i "s/define( 'Autoship_Version', '.*' )/define( 'Autoship_Version', '$NEW_VERSION' )/" autoship.php
sed -i "s/Stable tag: .*/Stable tag: $NEW_VERSION/" readme.txt

git add autoship.php readme.txt
git commit -m "Bump version to $NEW_VERSION"
git tag "v$NEW_VERSION"
git push origin main --tags
```

---

## Rollback Strategy

### Current State: No Rollback

```
Problem: Bad release goes to all users immediately
Options:
├── Push another release quickly (risky)
├── Ask users to manually rollback
└── Wait for WordPress.org support (slow)
```

### Target State: Safe Rollback

```yaml
# Rollback workflow
name: Rollback

on:
  workflow_dispatch:
    inputs:
      version:
        description: 'Version to rollback to'
        required: true

jobs:
  rollback:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
        with:
          ref: v${{ github.event.inputs.version }}

      - name: Deploy previous version
        uses: 10up/action-wordpress-plugin-deploy@stable
        env:
          SVN_PASSWORD: ${{ secrets.SVN_PASSWORD }}
          SVN_USERNAME: ${{ secrets.SVN_USERNAME }}
          VERSION: ${{ github.event.inputs.version }}-hotfix
```

---

## Pre-Deployment Checklist

```markdown
## Release Checklist

### Before Release
- [ ] All tests passing
- [ ] PHPCS compliance passing
- [ ] Version updated in autoship.php
- [ ] Version updated in readme.txt
- [ ] Changelog updated in readme.txt
- [ ] No debug code (var_dump, console.log)
- [ ] No development dependencies in build
- [ ] E2E tests passing

### Release
- [ ] Create GitHub release with tag
- [ ] Verify GitHub Action triggered
- [ ] Wait for deployment completion
- [ ] Verify on WordPress.org plugin page
- [ ] Test fresh install from WordPress.org
- [ ] Test update from previous version

### After Release
- [ ] Monitor error tracking (Sentry)
- [ ] Monitor support forums
- [ ] Update documentation if needed
- [ ] Announce in changelog/blog
```

---

## Migration Path

### Week 1: Automation Setup
- [ ] Create `.distignore` file
- [ ] Set up GitHub Secrets for SVN credentials
- [ ] Create GitHub Action for deployment
- [ ] Test with minor version release

### Week 2: Pipeline Improvement
- [ ] Add version bump script
- [ ] Add pre-deployment checks
- [ ] Add rollback workflow
- [ ] Document process

### Month 2: Full Automation
- [ ] Integrate E2E tests into pipeline
- [ ] Add staging deployment step
- [ ] Add Slack notifications
- [ ] Remove all manual SVN interaction

---

## Cost of Manual Deployment

| Factor | Manual | Automated |
|--------|--------|-----------|
| Time per release | 30-60 min | 5 min (create release) |
| Error rate | ~10% | ~1% |
| Stress level | High | Low |
| Deployment frequency | Weekly at best | Daily possible |
| Rollback time | 30+ min | 5 min |
| Documentation needed | Extensive | Minimal |

**Annual Cost** (assuming bi-weekly releases):
- Manual: 26 releases × 45 min = 19.5 hours/year on deployment alone
- Automated: 26 releases × 5 min = 2 hours/year

**Hidden Costs of Manual**:
- Context switching
- Deployment anxiety
- Incident response time
- Knowledge concentration (only one person knows how)

---

*Last Updated: December 2024*
*Review: After automation is implemented*
