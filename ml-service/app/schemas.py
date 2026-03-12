"""Pydantic models for all request/response types."""

from typing import Dict, List, Optional

from pydantic import BaseModel, Field


# --- Turnover Prediction ---


class EmployeeFeatures(BaseModel):
    """Input features for turnover prediction."""

    tenure_months: int = Field(..., ge=0, description="Months of employment")
    salary: float = Field(..., gt=0, description="Annual salary")
    absence_rate: float = Field(
        ..., ge=0.0, le=1.0, description="Absence rate (0-1)"
    )
    performance_score: float = Field(
        ..., ge=0.0, le=5.0, description="Performance score (0-5)"
    )
    department: str = Field(..., description="Department name")
    recent_leave_days: int = Field(
        ..., ge=0, description="Leave days taken in last 3 months"
    )


class TurnoverPrediction(BaseModel):
    """Output for turnover prediction."""

    risk_score: float = Field(..., ge=0.0, le=1.0, description="Turnover risk score")
    factors: Dict[str, float] = Field(
        ..., description="Top contributing factors with weights"
    )
    risk_level: str = Field(
        ..., description="Risk level: low, medium, or high"
    )


# --- Attendance / Anomaly Detection ---


class AttendanceRecord(BaseModel):
    """Single attendance record."""

    employee_id: str = Field(..., description="Employee identifier")
    date: str = Field(..., description="Date in YYYY-MM-DD format")
    check_in: str = Field(..., description="Check-in time in HH:MM format")
    check_out: str = Field(..., description="Check-out time in HH:MM format")
    hours_worked: float = Field(..., ge=0, description="Total hours worked")


class AnomalyResult(BaseModel):
    """Attendance record with anomaly detection result."""

    employee_id: str
    date: str
    check_in: str
    check_out: str
    hours_worked: float
    is_anomaly: bool = Field(..., description="Whether this record is anomalous")
    anomaly_score: float = Field(
        ..., description="Anomaly score (lower = more anomalous)"
    )


class AnomalyDetectionRequest(BaseModel):
    """Request for anomaly detection."""

    records: List[AttendanceRecord]


class AnomalyDetectionResponse(BaseModel):
    """Response for anomaly detection."""

    results: List[AnomalyResult]
    total_anomalies: int


class PatternAnalysisRequest(BaseModel):
    """Request for attendance pattern analysis."""

    records: List[AttendanceRecord]


class PatternInsights(BaseModel):
    """Attendance pattern insights."""

    most_late_day: str = Field(..., description="Day of week with most late arrivals")
    avg_hours_per_day: float = Field(..., description="Average hours worked per day")
    avg_check_in_time: str = Field(..., description="Average check-in time")
    avg_check_out_time: str = Field(..., description="Average check-out time")
    overtime_frequency: float = Field(
        ..., description="Fraction of days with overtime (>8h)"
    )
    undertime_frequency: float = Field(
        ..., description="Fraction of days with undertime (<7h)"
    )
    total_records_analyzed: int


# --- Chat / NLP ---


class ChatQuery(BaseModel):
    """Natural language HR query."""

    question: str = Field(..., description="Natural language question")
    context_type: Optional[str] = Field(
        default="general",
        description="Context type: general, attendance, turnover, policy",
    )


class ChatResponse(BaseModel):
    """Response for chat queries."""

    answer: str = Field(..., description="Natural language answer")
    sources: List[str] = Field(
        default_factory=list, description="Source references"
    )
    suggested_sql: Optional[str] = Field(
        default=None, description="Suggested SQL query"
    )


class ChatHistoryEntry(BaseModel):
    """A single chat history entry."""

    role: str = Field(..., description="Role: user or assistant")
    content: str = Field(..., description="Message content")


class ChatHistoryResponse(BaseModel):
    """Response for chat history."""

    history: List[ChatHistoryEntry]
    total_entries: int


# --- Leave Prediction ---


class LeaveHistory(BaseModel):
    """Employee leave history for prediction."""

    employee_id: str = Field(..., description="Employee identifier")
    monthly_leave_counts: List[int] = Field(
        ..., description="List of monthly leave day counts (recent months)"
    )


class LeavePrediction(BaseModel):
    """Predicted leave days."""

    predicted_days: float = Field(
        ..., ge=0, description="Predicted leave days for next month"
    )
    confidence: float = Field(
        ..., ge=0.0, le=1.0, description="Prediction confidence"
    )


# --- Health ---


class HealthResponse(BaseModel):
    """Health check response."""

    status: str
    version: str
    models_loaded: bool
