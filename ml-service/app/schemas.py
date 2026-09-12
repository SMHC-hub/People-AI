"""Pydantic models for all request/response types."""

from collections import defaultdict
from datetime import datetime
from typing import Any, Dict, List, Optional, Union

from pydantic import BaseModel, Field, model_validator


# --- Turnover Prediction ---


class EmployeeFeatures(BaseModel):
    """Input features for turnover prediction."""

    tenure_months: int = Field(default=12, ge=0, description="Months of employment")
    salary: float = Field(..., gt=0, description="Annual salary")
    absence_rate: float = Field(
        default=0.05, ge=0.0, le=1.0, description="Absence rate (0-1)"
    )
    performance_score: float = Field(
        default=3.5, ge=0.0, le=5.0, description="Performance score (0-5)"
    )
    department: str = Field(default="general", description="Department name")
    recent_leave_days: int = Field(
        default=2, ge=0, description="Leave days taken in last 3 months"
    )
    # Optional fields passed when called with raw records
    employee_id: Optional[Union[int, str]] = None
    position: Optional[str] = None
    hire_date: Optional[str] = None
    attendance_records: Optional[List[Dict[str, Any]]] = None
    leave_records: Optional[List[Dict[str, Any]]] = None

    @model_validator(mode="before")
    @classmethod
    def populate_defaults_from_records(cls, data: Any) -> Any:
        if isinstance(data, dict):
            # Calculate tenure_months from hire_date if not explicitly given
            if data.get("tenure_months") is None:
                hire_date = data.get("hire_date")
                if hire_date:
                    try:
                        h_dt = datetime.strptime(str(hire_date)[:10], "%Y-%m-%d")
                        now = datetime.now()
                        months = (now.year - h_dt.year) * 12 + (now.month - h_dt.month)
                        data["tenure_months"] = max(months, 1)
                    except Exception:
                        data["tenure_months"] = 12
                else:
                    data["tenure_months"] = 12

            # Calculate absence_rate from attendance_records if not given
            if data.get("absence_rate") is None:
                attendances = data.get("attendance_records") or []
                if attendances:
                    absent_count = sum(1 for a in attendances if isinstance(a, dict) and a.get("status") == "absent")
                    data["absence_rate"] = round(absent_count / max(len(attendances), 1), 4)
                else:
                    data["absence_rate"] = 0.05

            # Calculate recent_leave_days from leave_records if not given
            if data.get("recent_leave_days") is None:
                leaves = data.get("leave_records") or []
                if leaves:
                    days_sum = sum(int(l.get("days", 1)) for l in leaves if isinstance(l, dict) and l.get("status") == "approved")
                    data["recent_leave_days"] = max(days_sum, 0)
                else:
                    data["recent_leave_days"] = 2

            if data.get("performance_score") is None:
                data["performance_score"] = 3.5

            if not data.get("department"):
                data["department"] = "general"
        return data


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

    records: List[AttendanceRecord] = Field(default_factory=list)
    attendance_records: Optional[List[Dict[str, Any]]] = None
    tenant_id: Optional[int] = None

    @model_validator(mode="before")
    @classmethod
    def normalize_records(cls, data: Any) -> Any:
        if isinstance(data, dict):
            raw_records = data.get("records") or data.get("attendance_records") or []
            normalized = []
            for r in raw_records:
                if isinstance(r, dict):
                    emp_id = str(r.get("employee_id", "1"))
                    date_str = str(r.get("date", "2024-01-01"))[:10]
                    raw_in = str(r.get("check_in") or "09:00")
                    raw_out = str(r.get("check_out") or "17:00")
                    # Parse HH:MM from datetime or time string
                    c_in = raw_in.split("T")[-1].split(" ")[-1][:5] if ":" in raw_in else "09:00"
                    c_out = raw_out.split("T")[-1].split(" ")[-1][:5] if ":" in raw_out else "17:00"
                    hours = float(r.get("hours_worked") or 8.0)
                    normalized.append({
                        "employee_id": emp_id,
                        "date": date_str,
                        "check_in": c_in,
                        "check_out": c_out,
                        "hours_worked": max(hours, 0.0),
                    })
                else:
                    normalized.append(r)
            data["records"] = normalized
        return data


class AnomalyDetectionResponse(BaseModel):
    """Response for anomaly detection."""

    results: List[AnomalyResult]
    total_anomalies: int
    anomaly_ids: List[Union[int, str]] = Field(default_factory=list)


class PatternAnalysisRequest(BaseModel):
    """Request for attendance pattern analysis."""

    records: List[AttendanceRecord] = Field(default_factory=list)


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

    question: str = Field(default="", description="Natural language question")
    message: Optional[str] = None
    context_type: Optional[str] = Field(
        default="general",
        description="Context type: general, attendance, turnover, policy",
    )
    context: Optional[Any] = None
    tenant_id: Optional[int] = None
    user_role: Optional[str] = None

    @model_validator(mode="before")
    @classmethod
    def resolve_question(cls, data: Any) -> Any:
        if isinstance(data, dict):
            q = data.get("question") or data.get("message") or ""
            data["question"] = q
        return data


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

    employee_id: str = Field(default="all", description="Employee identifier")
    monthly_leave_counts: List[int] = Field(
        default_factory=lambda: [2, 1, 3, 2, 4],
        description="List of monthly leave day counts (recent months)",
    )
    historical_data: Optional[List[Dict[str, Any]]] = None
    department_id: Optional[int] = None
    period: Optional[str] = None
    tenant_id: Optional[int] = None

    @model_validator(mode="before")
    @classmethod
    def normalize_history(cls, data: Any) -> Any:
        if isinstance(data, dict):
            if not data.get("monthly_leave_counts"):
                raw_hist = data.get("historical_data") or []
                if raw_hist:
                    month_counts = defaultdict(int)
                    for item in raw_hist:
                        if isinstance(item, dict):
                            s_date = str(item.get("start_date", ""))[:7]
                            days = int(item.get("days", 1))
                            month_counts[s_date] += days
                    counts = list(month_counts.values())
                    data["monthly_leave_counts"] = counts if counts else [2, 3, 1, 4]
                else:
                    data["monthly_leave_counts"] = [2, 1, 3, 2, 4]
            if not data.get("employee_id"):
                data["employee_id"] = "all"
        return data


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
