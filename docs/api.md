# AI-HR Analytics Platform - API Documentation

## Base URL

```
http://localhost:8000/api
```

## Authentication

All API endpoints (except login and register) require a Bearer token in the `Authorization` header. Tokens are issued via Laravel Sanctum.

```
Authorization: Bearer <token>
```

---

## Endpoints

### Authentication

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/auth/register` | Register a new user |
| POST | `/api/auth/login` | Login and receive an API token |
| POST | `/api/auth/logout` | Revoke the current token |
| GET | `/api/auth/me` | Get the authenticated user profile |

### Employees

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/employees` | List all employees (paginated) |
| POST | `/api/employees` | Create a new employee record |
| GET | `/api/employees/{id}` | Get a single employee by ID |
| PUT | `/api/employees/{id}` | Update an employee record |
| DELETE | `/api/employees/{id}` | Soft-delete an employee record |
| GET | `/api/employees/{id}/history` | Get employment history for an employee |

### Departments

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/departments` | List all departments |
| POST | `/api/departments` | Create a new department |
| GET | `/api/departments/{id}` | Get department details |
| PUT | `/api/departments/{id}` | Update a department |
| GET | `/api/departments/{id}/stats` | Get department-level analytics |

### Analytics & Dashboards

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/analytics/dashboard` | Get dashboard summary metrics |
| GET | `/api/analytics/turnover` | Get turnover analytics and trends |
| GET | `/api/analytics/satisfaction` | Get employee satisfaction metrics |
| GET | `/api/analytics/headcount` | Get headcount trends over time |
| GET | `/api/analytics/department-comparison` | Compare metrics across departments |

### ML Predictions

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/predictions/turnover` | Predict turnover risk for an employee |
| GET | `/api/predictions/turnover/batch` | Get batch turnover predictions |
| GET | `/api/predictions/anomalies` | Detect anomalies in HR metrics |
| GET | `/api/predictions/risk-factors` | Get top risk factors from the model |

### RAG Chatbot

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/chat` | Send a message to the HR AI chatbot |
| GET | `/api/chat/history` | Retrieve chat history for the user |
| DELETE | `/api/chat/history` | Clear chat history |

### Reports

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/reports` | List available reports |
| POST | `/api/reports/generate` | Generate a new report (PDF/CSV) |
| GET | `/api/reports/{id}/download` | Download a generated report |

### Notifications

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/notifications` | List notifications for the user |
| PUT | `/api/notifications/{id}/read` | Mark a notification as read |
| POST | `/api/notifications/settings` | Update notification preferences |

---

## Error Responses

All errors follow a consistent format:

```json
{
  "message": "Human-readable error description",
  "errors": {
    "field": ["Validation error detail"]
  }
}
```

| Status Code | Meaning |
|-------------|---------|
| 400 | Bad Request - invalid parameters |
| 401 | Unauthorized - missing or invalid token |
| 403 | Forbidden - insufficient permissions |
| 404 | Not Found - resource does not exist |
| 422 | Unprocessable Entity - validation failed |
| 500 | Internal Server Error |

---

## Pagination

List endpoints return paginated results:

```json
{
  "data": [...],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 72
  }
}
```

Query parameters: `?page=1&per_page=15`

---

## ML Service (Internal)

The ML service runs on `http://ml-service:8001` and is called internally by the Laravel backend. Direct access is not intended for end users.

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/predict/turnover` | Turnover risk prediction |
| POST | `/detect/anomalies` | Anomaly detection on HR data |
| POST | `/chat/completions` | RAG-powered chatbot response |
| GET | `/health` | Service health check |
