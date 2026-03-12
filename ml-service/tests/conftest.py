"""Pytest fixtures for ML service tests."""

from typing import Dict, List

import pytest
from fastapi.testclient import TestClient

from app.main import app
from app.models.anomaly import AnomalyModel
from app.models.turnover import TurnoverModel
from app.schemas import AttendanceRecord, EmployeeFeatures


@pytest.fixture
def client() -> TestClient:
    """Create a FastAPI test client."""
    return TestClient(app)


@pytest.fixture
def turnover_model() -> TurnoverModel:
    """Create a TurnoverModel instance with demo parameters."""
    return TurnoverModel()


@pytest.fixture
def anomaly_model() -> AnomalyModel:
    """Create an AnomalyModel instance with demo parameters."""
    return AnomalyModel()


@pytest.fixture
def sample_employee_features() -> EmployeeFeatures:
    """Sample employee features for testing."""
    return EmployeeFeatures(
        tenure_months=24,
        salary=65000.0,
        absence_rate=0.15,
        performance_score=3.5,
        department="engineering",
        recent_leave_days=3,
    )


@pytest.fixture
def high_risk_employee_features() -> EmployeeFeatures:
    """Employee features that should indicate high turnover risk."""
    return EmployeeFeatures(
        tenure_months=3,
        salary=35000.0,
        absence_rate=0.45,
        performance_score=1.5,
        department="sales",
        recent_leave_days=10,
    )


@pytest.fixture
def low_risk_employee_features() -> EmployeeFeatures:
    """Employee features that should indicate low turnover risk."""
    return EmployeeFeatures(
        tenure_months=60,
        salary=120000.0,
        absence_rate=0.02,
        performance_score=4.8,
        department="engineering",
        recent_leave_days=1,
    )


@pytest.fixture
def sample_employee_dict() -> Dict[str, object]:
    """Sample employee as a dictionary for model-level testing."""
    return {
        "tenure_months": 24,
        "salary": 65000.0,
        "absence_rate": 0.15,
        "performance_score": 3.5,
        "department": "engineering",
        "recent_leave_days": 3,
    }


@pytest.fixture
def sample_attendance_records() -> List[AttendanceRecord]:
    """Sample attendance records for testing."""
    return [
        AttendanceRecord(
            employee_id="EMP001",
            date="2024-01-15",
            check_in="09:00",
            check_out="17:30",
            hours_worked=8.5,
        ),
        AttendanceRecord(
            employee_id="EMP001",
            date="2024-01-16",
            check_in="08:55",
            check_out="17:25",
            hours_worked=8.5,
        ),
        AttendanceRecord(
            employee_id="EMP002",
            date="2024-01-15",
            check_in="09:10",
            check_out="17:40",
            hours_worked=8.5,
        ),
        AttendanceRecord(
            employee_id="EMP002",
            date="2024-01-16",
            check_in="09:05",
            check_out="17:35",
            hours_worked=8.5,
        ),
    ]


@pytest.fixture
def anomalous_attendance_records() -> List[AttendanceRecord]:
    """Attendance records with anomalies for testing."""
    return [
        # Normal records
        AttendanceRecord(
            employee_id="EMP001",
            date="2024-01-15",
            check_in="09:00",
            check_out="17:30",
            hours_worked=8.5,
        ),
        AttendanceRecord(
            employee_id="EMP001",
            date="2024-01-16",
            check_in="08:55",
            check_out="17:25",
            hours_worked=8.5,
        ),
        # Anomalous: very late arrival, very short hours
        AttendanceRecord(
            employee_id="EMP003",
            date="2024-01-17",
            check_in="14:00",
            check_out="15:00",
            hours_worked=1.0,
        ),
        # Anomalous: extremely long hours
        AttendanceRecord(
            employee_id="EMP004",
            date="2024-01-18",
            check_in="05:00",
            check_out="23:00",
            hours_worked=18.0,
        ),
    ]
