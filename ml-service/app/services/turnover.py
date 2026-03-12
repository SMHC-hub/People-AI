"""Turnover prediction service wrapping the TurnoverModel."""

from typing import Any, Dict, List

from app.models.turnover import TurnoverModel
from app.schemas import EmployeeFeatures, TurnoverPrediction


class TurnoverService:
    """Service for turnover prediction with feature preprocessing."""

    def __init__(self) -> None:
        self.model = TurnoverModel()

    def predict(self, employee: EmployeeFeatures) -> TurnoverPrediction:
        """Predict turnover risk for a single employee.

        Handles preprocessing: salary normalization, department encoding,
        and tenure computation are done inside the model.

        Args:
            employee: Employee features.

        Returns:
            TurnoverPrediction with risk score, factors, and risk level.
        """
        features = self._preprocess(employee)
        risk_score, factors = self.model.predict(features)
        risk_level = self._classify_risk(risk_score)

        return TurnoverPrediction(
            risk_score=round(risk_score, 4),
            factors=factors,
            risk_level=risk_level,
        )

    def predict_batch(
        self, employees: List[EmployeeFeatures]
    ) -> List[TurnoverPrediction]:
        """Predict turnover risk for multiple employees.

        Args:
            employees: List of employee features.

        Returns:
            List of TurnoverPrediction results.
        """
        return [self.predict(emp) for emp in employees]

    def _preprocess(self, employee: EmployeeFeatures) -> Dict[str, Any]:
        """Preprocess employee features for the model.

        Normalizes salary, encodes department, and passes through
        other numerical features.
        """
        return {
            "tenure_months": employee.tenure_months,
            "salary": employee.salary,
            "absence_rate": employee.absence_rate,
            "performance_score": employee.performance_score,
            "department": employee.department.lower().strip(),
            "recent_leave_days": employee.recent_leave_days,
        }

    @staticmethod
    def _classify_risk(risk_score: float) -> str:
        """Classify risk score into low/medium/high."""
        if risk_score < 0.3:
            return "low"
        elif risk_score < 0.7:
            return "medium"
        else:
            return "high"
