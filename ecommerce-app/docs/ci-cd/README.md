# CI/CD Documentation - Group 4

This directory contains all CI/CD pipeline documentation and configurations for the multi-tenant e-commerce platform.

## 📁 Structure

```
docs/ci-cd/
├── README.md (this file)
├── overview.md - Pipeline overview and architecture
├── setup-guide.md - Setup and configuration instructions
├── team-contributions/
│   └── zakariae.md - Testing strategy & PHPUnit configuration ✅
└── troubleshooting.md - Common issues and solutions
```

## 👥 Team Members & Contributions

### Completed
- **Zakariae** ✅ - Testing Strategy & PHPUnit Configuration

### To Be Documented
- **Abdessamad** - Pipeline Configuration & GitLab Setup
- **Hamza** - Code Quality Tools (Laravel Pint, PHPStan)
- **Imane** - Database & Environment Setup
- **Omayma** - Documentation & Deployment Guide

> **Note**: Team members should follow the [Documentation Guide](../DOCUMENTATION_GUIDE.md) to document their contributions.

## 🚀 Quick Start

1. Read [overview.md](./overview.md) for pipeline architecture
2. Follow [setup-guide.md](./setup-guide.md) for configuration
3. Check [Zakariae's contribution](./team-contributions/zakariae.md) for testing details
4. Refer to [troubleshooting.md](./troubleshooting.md) if issues arise
5. **Team members**: See [Documentation Guide](../DOCUMENTATION_GUIDE.md) to add your contribution

## 📋 Pipeline Stages

1. **Build** - Dependencies installation & asset compilation
2. **Test** - Automated testing with PostgreSQL
3. **Quality** - Code style & static analysis
4. **Deploy** - Staging deployment (manual trigger)

## 🔗 Related Files

- [.gitlab-ci.yml](../../.gitlab-ci.yml) - Main pipeline configuration
- [.env.testing](../../.env.testing) - Test environment variables
- [phpstan.neon](../../phpstan.neon) - Static analysis config
- [tests/](../../tests/) - Test suite
