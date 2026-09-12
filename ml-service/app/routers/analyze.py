"""Analysis router for anomaly detection and attendance pattern analysis."""

import logging

from fastapi import APIRouter, HTTPException

from app.schemas import (
    AnomalyDetectionRequest,
    AnomalyDetectionResponse,
    PatternAnalysisRequest,
    PatternInsights,
)
from app.services.anomaly import AnomalyService

logger = logging.getLogger(__name__)

router = APIRouter(prefix="/analyze", tags=["analysis"])

# Service instance
anomaly_service = AnomalyService()


@router.post("/anomalies", response_model=AnomalyDetectionResponse)
async def detect_anomalies(
    request: AnomalyDetectionRequest,
) -> AnomalyDetectionResponse:
    """Detect anomalies in attendance records.

    Takes a list of attendance records and returns each record
    annotated with anomaly flags and scores.
    """
    try:
        results = anomaly_service.detect_anomalies(request.records)
        total_anomalies = sum(1 for r in results if r.is_anomaly)
        anomaly_ids = [r.employee_id for r in results if r.is_anomaly]

        return AnomalyDetectionResponse(
            results=results,
            total_anomalies=total_anomalies,
            anomaly_ids=anomaly_ids,
        )
    except Exception as e:
        logger.error(f"Anomaly detection failed: {e}")
        raise HTTPException(
            status_code=500,
            detail=f"Anomaly detection failed: {str(e)}",
        )


@router.post("/patterns", response_model=PatternInsights)
async def analyze_patterns(request: PatternAnalysisRequest) -> PatternInsights:
    """Analyze attendance patterns.

    Takes attendance data and returns pattern insights including
    most late day, average hours, overtime frequency, etc.
    """
    try:
        insights = anomaly_service.analyze_patterns(request.records)
        return PatternInsights(**insights)
    except Exception as e:
        logger.error(f"Pattern analysis failed: {e}")
        raise HTTPException(
            status_code=500,
            detail=f"Pattern analysis failed: {str(e)}",
        )
