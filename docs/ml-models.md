# AI-HR Analytics Platform - ML Models Documentation

## Overview

The ML service provides three core models that power the intelligent features of the HR Analytics platform. All models are served via a FastAPI application running on port 8001.

---

## 1. Turnover Prediction Model

### Algorithm

**Gradient Boosted Decision Trees (scikit-learn `GradientBoostingClassifier`)**

A supervised classification model trained on historical employee data to predict the probability that an employee will leave the organization within a defined time horizon (default: 6 months).

### Features Used

| Feature | Type | Description |
|---------|------|-------------|
| `tenure_months` | Numeric | Months since hire date |
| `satisfaction_score` | Numeric | Latest employee satisfaction survey score (1-10) |
| `performance_rating` | Numeric | Most recent performance review rating (1-5) |
| `salary_percentile` | Numeric | Salary relative to department peers (0-100) |
| `overtime_hours_monthly` | Numeric | Average monthly overtime hours (last 3 months) |
| `promotion_last_3_years` | Binary | Whether the employee was promoted in the last 3 years |
| `num_projects` | Numeric | Number of active projects assigned |
| `department` | Categorical | Department (one-hot encoded) |
| `manager_change_recent` | Binary | Whether the employee had a manager change in the last 6 months |
| `remote_work_ratio` | Numeric | Fraction of remote work days (0.0-1.0) |

### Input

```json
{
  "employee_id": 1234,
  "features": {
    "tenure_months": 36,
    "satisfaction_score": 5.2,
    "performance_rating": 3,
    "salary_percentile": 42,
    "overtime_hours_monthly": 18.5,
    "promotion_last_3_years": false,
    "num_projects": 4,
    "department": "Engineering",
    "manager_change_recent": true,
    "remote_work_ratio": 0.6
  }
}
```

### Output

```json
{
  "employee_id": 1234,
  "turnover_probability": 0.73,
  "risk_level": "high",
  "top_risk_factors": [
    {"feature": "satisfaction_score", "importance": 0.31},
    {"feature": "manager_change_recent", "importance": 0.22},
    {"feature": "salary_percentile", "importance": 0.18}
  ],
  "model_version": "1.2.0",
  "prediction_date": "2026-03-12"
}
```

### Training Details

- **Training data**: Historical employee records with known outcomes (stayed/left)
- **Train/test split**: 80/20 stratified split
- **Evaluation metrics**: AUC-ROC, Precision, Recall, F1-score
- **Retraining cadence**: Monthly, triggered when new exit data is available

---

## 2. Anomaly Detection Model

### Algorithm

**Isolation Forest (scikit-learn `IsolationForest`)**

An unsupervised anomaly detection algorithm that identifies unusual patterns in HR metrics. Isolation Forest works by randomly partitioning data and measuring how quickly individual observations become isolated, with anomalies requiring fewer partitions.

### Features Used

| Feature | Type | Description |
|---------|------|-------------|
| `avg_overtime_hours` | Numeric | Department average monthly overtime |
| `turnover_rate` | Numeric | Rolling 3-month turnover rate |
| `satisfaction_trend` | Numeric | Change in satisfaction scores over 3 months |
| `absenteeism_rate` | Numeric | Percentage of unplanned absences |
| `headcount_change` | Numeric | Net headcount change (hires minus departures) |
| `open_positions_ratio` | Numeric | Ratio of open positions to total headcount |
| `training_hours_per_employee` | Numeric | Average training hours per employee |
| `internal_mobility_rate` | Numeric | Rate of internal transfers/promotions |

### Input

```json
{
  "department_id": 5,
  "metrics": {
    "avg_overtime_hours": 32.5,
    "turnover_rate": 0.15,
    "satisfaction_trend": -1.8,
    "absenteeism_rate": 0.12,
    "headcount_change": -8,
    "open_positions_ratio": 0.25,
    "training_hours_per_employee": 2.1,
    "internal_mobility_rate": 0.02
  },
  "period": "2026-02"
}
```

### Output

```json
{
  "department_id": 5,
  "is_anomaly": true,
  "anomaly_score": -0.82,
  "anomalous_metrics": [
    {
      "metric": "avg_overtime_hours",
      "value": 32.5,
      "expected_range": [10.0, 22.0],
      "severity": "high"
    },
    {
      "metric": "satisfaction_trend",
      "value": -1.8,
      "expected_range": [-0.5, 0.5],
      "severity": "high"
    }
  ],
  "detection_date": "2026-03-12"
}
```

### Configuration

- **Contamination parameter**: 0.05 (estimated 5% anomaly rate)
- **Number of estimators**: 200
- **Detection scope**: Run per department on a weekly schedule
- **Alert threshold**: Anomaly score below -0.6 triggers a notification

---

## 3. RAG Chatbot (Retrieval-Augmented Generation)

### Algorithm

**Retrieval-Augmented Generation using LangChain + OpenAI GPT-4 + ChromaDB**

A conversational AI system that answers HR policy questions and provides data-driven insights by combining vector-based document retrieval with large language model generation.

### Architecture

1. **Document Ingestion**: HR policy documents, employee handbooks, and FAQ content are chunked (512 tokens, 50-token overlap) and embedded using OpenAI's `text-embedding-ada-002` model.
2. **Vector Store**: Embeddings are stored in ChromaDB with persistent storage at `/data/chroma`.
3. **Retrieval**: When a user query arrives, it is embedded and the top-k (k=5) most similar document chunks are retrieved via cosine similarity.
4. **Generation**: Retrieved chunks are injected into a prompt template as context, and GPT-4 generates a grounded answer.

### Features Used

| Component | Technology | Purpose |
|-----------|-----------|---------|
| Embedding model | `text-embedding-ada-002` | Convert text to vector representations |
| Vector database | ChromaDB | Store and retrieve document embeddings |
| Language model | GPT-4 (via OpenAI API) | Generate natural language responses |
| Orchestration | LangChain | Chain retrieval and generation steps |

### Input

```json
{
  "message": "What is our company's parental leave policy?",
  "conversation_id": "conv_abc123",
  "user_id": 42
}
```

### Output

```json
{
  "response": "According to the employee handbook (Section 5.3), the company offers 16 weeks of paid parental leave for primary caregivers and 6 weeks for secondary caregivers. Leave can be taken within 12 months of the birth or adoption date. You can find more details in the Benefits section of the HR portal.",
  "sources": [
    {
      "document": "Employee Handbook v3.2",
      "section": "5.3 - Parental Leave",
      "relevance_score": 0.94
    },
    {
      "document": "Benefits FAQ",
      "section": "Parental Leave",
      "relevance_score": 0.87
    }
  ],
  "conversation_id": "conv_abc123"
}
```

### Configuration

- **Chunk size**: 512 tokens
- **Chunk overlap**: 50 tokens
- **Top-k retrieval**: 5 documents
- **Temperature**: 0.2 (low creativity, high factual accuracy)
- **Max tokens**: 1024
- **Persist directory**: `/data/chroma` (Docker volume `ml-data`)

---

## Model Versioning & Monitoring

All models follow semantic versioning. Model artifacts and metadata are stored in the `ml-data` Docker volume.

| Aspect | Details |
|--------|---------|
| Version tracking | Metadata stored alongside model artifacts |
| Performance monitoring | Prediction logs stored for drift detection |
| Retraining triggers | Scheduled (monthly) or on-demand via API |
| Fallback behavior | Returns last known good prediction if model is unavailable |
