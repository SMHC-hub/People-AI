"""Payroll anomaly detection router."""

from typing import Any, Dict, List
from fastapi import APIRouter, HTTPException, status
from pydantic import BaseModel, Field

from app.services.payroll_anomaly import PayrollAnomalyEngine

router = APIRouter(prefix="/payroll", tags=["payroll"])
payroll_engine = PayrollAnomalyEngine()


class PayrollRecord(BaseModel):
    """Payroll record schema for anomaly detection."""
    id: int = 1
    employee_id: int = 1
    employee_name: str = ""
    pay_period: str = "2026-08"
    base_salary: float = 6500.0
    bonus: float = 0.0
    overtime_pay: float = 0.0
    deductions: float = 1170.0
    net_salary: float = 5330.0
    is_anomaly: bool = False
    anomaly_severity: str = "low"
    anomaly_type: str = ""
    anomaly_explanation: str = ""
    status: str = "processed"


class PayrollAnomalyRequest(BaseModel):
    """Request schema for payroll anomaly detection."""
    records: List[PayrollRecord] = Field(default_factory=list)


class PayrollAnomalyResponse(BaseModel):
    """Response schema containing detected payroll anomalies."""
    success: bool = True
    total_analyzed: int
    anomalies_detected: int
    anomalies: List[Dict[str, Any]]


@router.post("/anomaly", response_model=PayrollAnomalyResponse)
async def detect_payroll_anomalies(request: PayrollAnomalyRequest) -> Dict[str, Any]:
    """Detect compensation anomalies across a batch of payroll records."""
    records_dict = [r.model_dump() for r in request.records]
    anomalies = payroll_engine.detect_anomalies(records_dict)

    return {
        "success": True,
        "total_analyzed": len(request.records),
        "anomalies_detected": len(anomalies),
        "anomalies": anomalies,
    }
