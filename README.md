# AI-Powered HR Analytics Platform

[![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![Vue.js](https://img.shields.io/badge/Vue.js-3-4FC08D?style=for-the-badge&logo=vue.js&logoColor=white)](https://vuejs.org)
[![Python](https://img.shields.io/badge/Python-3.11-3776AB?style=for-the-badge&logo=python&logoColor=white)](https://python.org)
[![Docker](https://img.shields.io/badge/Docker-Compose-2496ED?style=for-the-badge&logo=docker&logoColor=white)](https://docker.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=for-the-badge)](LICENSE)
[![CI](https://github.com/kasidit-wansudon/ai-hr-analytics/actions/workflows/ci.yml/badge.svg)](https://github.com/kasidit-wansudon/ai-hr-analytics/actions)

> Enterprise HR Analytics with ML-powered insights — turnover prediction, attendance anomaly detection, and a RAG-based policy chatbot.

---

## Architecture

```
┌─────────────────────────────────────────────────────────────────────┐
│                        Vue 3 Frontend                               │
│              (TypeScript + Tailwind CSS + ECharts)                   │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐ │
│  │Dashboard │ │Employee  │ │Attendance│ │  Leave   │ │AI Chatbot│ │
│  │  View    │ │  CRUD    │ │ Analysis │ │  Mgmt    │ │Interface │ │
│  └────┬─────┘ └────┬─────┘ └────┬─────┘ └────┬─────┘ └────┬─────┘ │
└───────┼────────────┼────────────┼────────────┼────────────┼────────┘
        │            │            │            │            │
        ▼            ▼            ▼            ▼            ▼
┌─────────────────────────────────────────────────────────────────────┐
│                     Laravel 11 REST API                             │
│                     (PHP 8.3 + Redis)                               │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐ │
│  │  Auth    │ │ Employee │ │Attendance│ │  Report  │ │ Chatbot  │ │
│  │  RBAC   │ │Controller│ │Controller│ │Generator │ │ Proxy    │ │
│  └────┬─────┘ └────┬─────┘ └────┬─────┘ └────┬─────┘ └────┬─────┘ │
└───────┼────────────┼────────────┼────────────┼────────────┼────────┘
        │            │            │            │            │
        ▼            ▼            ▼            ▼            ▼
┌────────────────┐  ┌──────────────────────────────────────────────┐
│                │  │          Python ML Service (FastAPI)          │
│     MySQL      │  │  ┌────────────┐ ┌───────────┐ ┌───────────┐ │
│   + Redis      │  │  │  Turnover  │ │  Anomaly  │ │    RAG    │ │
│                │  │  │ Prediction │ │ Detection │ │  Chatbot  │ │
│                │  │  │(scikit-lr) │ │(IsoForest)│ │(ChromaDB) │ │
│                │  │  └────────────┘ └───────────┘ └─────┬─────┘ │
│                │  │                                      │       │
│                │  │                                      ▼       │
│                │  │                                ┌───────────┐ │
│                │  │                                │  OpenAI   │ │
│                │  │                                │    API    │ │
│                │  │                                └───────────┘ │
└────────────────┘  └──────────────────────────────────────────────┘
```

## Features

### Core HR Management
- [x] Employee lifecycle management (hire, transfer, resign)
- [x] Department and position hierarchy
- [x] Attendance tracking and analytics
- [x] Leave management with approval workflow
- [x] Role-based access control (RBAC)
- [x] Multi-tenant architecture (multiple companies)

### AI/ML-Powered Features
- [x] **Turnover Risk Prediction** — Logistic regression model scoring each employee's flight risk
- [x] **Attendance Anomaly Detection** — Isolation Forest detecting irregular patterns
- [x] **Leave Pattern Analysis** — Predictive leave forecasting
- [x] **Natural Language Queries** — "Show me resignations in Q1 by department"
- [x] **RAG Policy Chatbot** — Company handbook Q&A with vector search (ChromaDB + OpenAI)
- [x] **Automated Report Generation** — Monthly HR insights as PDF

### Integrations
- [x] DingTalk / Slack notification integration
- [x] REST API with OpenAPI/Swagger documentation
- [x] Redis-powered queues (Laravel Horizon)

## Tech Stack

| Layer      | Technology                                      |
|------------|------------------------------------------------|
| Frontend   | Vue 3, TypeScript, Tailwind CSS, ECharts, Pinia |
| Backend    | Laravel 11, PHP 8.3, Laravel Horizon            |
| ML Service | FastAPI, scikit-learn, OpenAI, LangChain, ChromaDB |
| Database   | MySQL 8.0, Redis 7                              |
| Infra      | Docker, Docker Compose, GitHub Actions           |

## Quick Start

### Prerequisites
- Docker & Docker Compose
- Git

### 1. Clone and configure

```bash
git clone https://github.com/kasidit-wansudon/ai-hr-analytics.git
cd ai-hr-analytics
cp .env.example .env
```

### 2. Start all services

```bash
docker-compose up -d
```

This starts:
- **Frontend** → http://localhost:3000
- **Laravel API** → http://localhost:8000
- **ML Service** → http://localhost:8001
- **MySQL** → localhost:3306
- **Redis** → localhost:6379

### 3. Initialize the database

```bash
docker-compose exec backend php artisan migrate --seed
```

### 4. (Optional) Configure OpenAI for AI features

```bash
# Edit .env and set:
OPENAI_API_KEY=sk-your-key-here
```

## API Documentation

### Authentication
```
POST   /api/auth/login          # Login, returns JWT
POST   /api/auth/register       # Register new user
POST   /api/auth/logout         # Logout
GET    /api/auth/me             # Current user profile
```

### Employees
```
GET    /api/employees            # List (paginated, filterable)
POST   /api/employees            # Create employee
GET    /api/employees/{id}       # Show details
PUT    /api/employees/{id}       # Update
DELETE /api/employees/{id}       # Soft delete
GET    /api/employees/{id}/risk  # Turnover risk score (ML)
```

### Attendance
```
GET    /api/attendance                # List records
POST   /api/attendance/check-in      # Clock in
POST   /api/attendance/check-out     # Clock out
GET    /api/attendance/anomalies     # ML-detected anomalies
GET    /api/attendance/stats         # Aggregated statistics
```

### Leaves
```
GET    /api/leaves               # List leave requests
POST   /api/leaves               # Submit leave request
PUT    /api/leaves/{id}/approve  # Approve
PUT    /api/leaves/{id}/reject   # Reject
GET    /api/leaves/predictions   # Leave pattern predictions (ML)
```

### Reports
```
GET    /api/reports              # List generated reports
POST   /api/reports/generate     # Trigger report generation
GET    /api/reports/{id}/download # Download PDF
```

### AI/Chatbot
```
POST   /api/chatbot/query       # Natural language HR query
POST   /api/chatbot/policy      # RAG policy Q&A
GET    /api/chatbot/history     # Conversation history
```

## ML Models

### Turnover Risk Prediction
- **Algorithm:** Logistic Regression with cross-validation
- **Features:** tenure, salary percentile, absence rate, performance score, department, recent leave patterns
- **Output:** Risk score 0.0 – 1.0 with contributing factors
- **Retraining:** Monthly via scheduled job

### Attendance Anomaly Detection
- **Algorithm:** Isolation Forest (unsupervised)
- **Features:** check-in time, check-out time, hours worked, day of week, deviation from median
- **Output:** Anomaly flag + anomaly score per attendance record
- **Use case:** Detect buddy punching, irregular patterns, potential burnout

### RAG Policy Chatbot
- **Embedding:** OpenAI text-embedding-3-small
- **Vector Store:** ChromaDB (persistent)
- **LLM:** GPT-4o for answer generation
- **Retrieval:** Top-k similarity search on company policy documents
- **Guardrails:** Scoped to HR policy domain; refuses out-of-scope questions

## Screenshots

> Screenshots coming soon — the app is best experienced by running it locally.

| Dashboard | Employee Analytics | AI Chatbot |
|-----------|-------------------|------------|
| ![Dashboard](docs/screenshots/dashboard.png) | ![Analytics](docs/screenshots/analytics.png) | ![Chatbot](docs/screenshots/chatbot.png) |

## Project Structure

```
ai-hr-analytics/
├── backend/                    # Laravel 11 API
│   ├── app/
│   │   ├── Http/Controllers/   # API controllers
│   │   ├── Models/             # Eloquent models
│   │   ├── Services/           # Business logic
│   │   ├── Jobs/               # Queue jobs
│   │   └── Policies/           # RBAC authorization
│   ├── routes/api.php
│   ├── database/migrations/
│   └── tests/
├── frontend/                   # Vue 3 SPA
│   ├── src/
│   │   ├── views/              # Page components
│   │   ├── components/         # Reusable components
│   │   ├── stores/             # Pinia state management
│   │   └── api/                # API client
│   └── package.json
├── ml-service/                 # Python FastAPI
│   ├── app/
│   │   ├── models/             # ML model definitions
│   │   ├── routers/            # API routes
│   │   ├── services/           # Business logic
│   │   └── rag/                # RAG chatbot
│   ├── requirements.txt
│   └── Dockerfile
├── docker-compose.yml
├── .github/workflows/ci.yml
└── .env.example
```

## Development

### Running tests

```bash
# Backend (Laravel)
docker-compose exec backend php artisan test

# Frontend (Vue)
docker-compose exec frontend npm run test

# ML Service (Python)
docker-compose exec ml-service pytest
```

### Code quality

```bash
# PHP linting
docker-compose exec backend ./vendor/bin/pint

# TypeScript linting
docker-compose exec frontend npm run lint

# Python linting
docker-compose exec ml-service ruff check .
```

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

Please ensure:
- All tests pass
- Code follows existing style conventions
- New features include tests

## License

This project is licensed under the MIT License — see the [LICENSE](LICENSE) file for details.

---

Built with passion for HR technology and AI innovation.
