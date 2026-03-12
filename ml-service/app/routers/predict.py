"""Prediction router for turnover risk and leave forecasting."""

import logging
from typing import List

import numpy as np
from fastapi import APIRouter, HTTPException

from app.schemas import (
    EmployeeFeatures,
    LeaveHistory,
    LeavePrediction,
    TurnoverPrediction,
)
from app.services.turnover import TurnoverService

logger = logging.getLogger(__name__)

router = APIRouter(prefix="/predict", tags=["predictions"])

# Service instances (initialized on module load)
turnover_service = TurnoverService()


@router.post("/turnover", response_model=TurnoverPrediction)
async def predict_turnover(employee: EmployeeFeatures) -> TurnoverPrediction:
    """Predict turnover risk for an employee.

    Takes employee features and returns a risk score between 0-1
    along with the top contributing factors.
    """
    try:
        prediction = turnover_service.predict(employee)
        return prediction
    except Exception as e:
        logger.error(f"Turnover prediction failed: {e}")
        raise HTTPException(
            status_code=500,
            detail=f"Prediction failed: {str(e)}",
        )


@router.post("/leave", response_model=LeavePrediction)
async def predict_leave(history: LeaveHistory) -> LeavePrediction:
    """Predict leave days for the next month.

    Takes an employee's monthly leave history and uses simple
    time-series forecasting (weighted moving average) to predict
    next month's leave days.
    """
    try:
        counts = history.monthly_leave_counts

        if not counts:
            return LeavePrediction(predicted_days=0.0, confidence=0.5)

        # Use weighted moving average for prediction
        # More recent months get higher weight
        arr = np.array(counts, dtype=float)
        n = len(arr)

        if n == 1:
            return LeavePrediction(
                predicted_days=round(float(arr[0]), 1),
                confidence=0.4,
            )

        # Exponentially decaying weights (recent months matter more)
        weights = np.array([0.5 ** (n - 1 - i) for i in range(n)])
        weights = weights / weights.sum()

        predicted = float(np.dot(arr, weights))

        # Confidence based on data consistency and amount
        std = float(np.std(arr))
        mean = float(np.mean(arr))
        cv = std / mean if mean > 0 else 1.0  # coefficient of variation

        # More data and less variation -> higher confidence
        data_confidence = min(n / 12.0, 1.0)  # max out at 12 months
        stability_confidence = max(1.0 - cv, 0.2)
        confidence = round((data_confidence * 0.4 + stability_confidence * 0.6), 2)
        confidence = min(max(confidence, 0.1), 0.95)

        return LeavePrediction(
            predicted_days=round(max(predicted, 0.0), 1),
            confidence=confidence,
        )
    except Exception as e:
        logger.error(f"Leave prediction failed: {e}")
        raise HTTPException(
            status_code=500,
            detail=f"Prediction failed: {str(e)}",
        )
