# PeopleAI REST API Specification

All backend endpoints are prefixed with `/api` and served via Laravel 11. Authentication uses bearer tokens issued by Laravel Sanctum.

---

## 1. Authentication & Tenant Identity

### `POST /api/auth/login`
Authenticates user and returns Bearer token.
* **Request**:
```json
{
  "email": "admin@hranalytics.com",
  "password": "password"
}
```
* **Response**:
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "email": "admin@hranalytics.com",
      "full_name": "System Administrator",
      "role": "admin",
      "organization_id": 1
    },
    "token": "1|sanctum_token_string"
  }
}
```

---

## 2. Workforce Intelligence & Heatmaps

### `GET /api/workforce/stats`
Returns aggregated workforce health metrics, active headcount, risk counts, and satisfaction.

### `GET /api/workforce/heatmap`
Returns department-level breakdown: headcount, high-risk employee count, risk percentage, average satisfaction, and average promotion latency.

### `GET /api/workforce/insights`
Returns live AI workforce insight cards synthesized from SHAP factor decomposition and department distributions.

---

## 3. Payroll Intelligence & Anomaly Audit

### `GET /api/payrolls`
Paginated payroll records with filtering by `month`, `is_anomaly`, and `review_status`.

### `GET /api/payrolls/stats`
Returns total monthly payout, average net salary, total overtime disbursed, and anomaly count.

### `GET /api/payrolls/anomalies`
Returns all flagged payroll anomalies for a specified month.

### `POST /api/payrolls/{id}/review`
Updates human audit review status on a flagged payroll anomaly.
* **Request**:
```json
{
  "review_status": "reviewed",
  "notes": "Overtime justified due to Q1 production release."
}
```

---

## 4. Performance & Appraisal Analytics

### `GET /api/performances`
Returns paginated quarterly performance reviews with filters for `review_period` and `promotion_recommended`.

### `GET /api/performances/stats`
Returns appraisal completion count, average rating (out of 5.0), promotion recommendation rate, and bell curve rating distribution.

---

## 5. MLOps Lifecycle & Governance

### `GET /api/mlops/models`
Returns registered model versions, algorithms, accuracy, and deployment stages.

### `GET /api/mlops/metrics`
Returns combined MLOps metrics: Population Stability Index (PSI) drift telemetry, benchmark comparisons, and active champion models.

### `POST /api/mlops/retrain`
Triggers asynchronous model retraining pipeline.

### `POST /api/mlops/models/{id}/promote`
Promotes a candidate model version to active production champion.

---

## 6. Audit Trail & Enterprise Settings

### `GET /api/audit-logs`
Returns tamper-evident audit logs of administrative actions, payroll approvals, and model executions.

### `GET /api/settings`
Returns enterprise tenant configuration, active user counts, and ML service health status.

### `PUT /api/settings`
Updates organization parameters (e.g. legal name, corporate domain).

---

## 7. AI HR Chatbot (RAG Assistant)

### `POST /api/chatbot/query`
General workforce inquiry.

### `POST /api/chatbot/policy`
Document-grounded RAG query evaluated against company handbooks with verifiable citations.
* **Request**:
```json
{
  "question": "What is the annual education budget per employee?"
}
```
* **Response**:
```json
{
  "id": "msg-12345",
  "content": "According to the Professional Development Policy, each full-time employee is eligible for up to $2,500 annually for tuition reimbursement and approved professional certifications.",
  "role": "assistant",
  "timestamp": "2026-09-12T20:30:00Z",
  "citations": [
    {
      "document_title": "Professional Development Policy",
      "source_type": "hr_policy",
      "relevance_score": 0.94,
      "content_snippet": "Section 3.2: Full-time employees may request up to $2,500 per calendar year..."
    }
  ]
}
```
