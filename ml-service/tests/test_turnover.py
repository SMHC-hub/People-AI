"""Tests for the turnover prediction model and service."""

from typing import Dict

import pytest

from app.models.turnover import TurnoverModel
from app.schemas import EmployeeFeatures
from app.services.turnover import TurnoverService


class TestTurnoverModel:
    """Tests for the TurnoverModel class."""

    def test_model_initializes_fitted(self, turnover_model: TurnoverModel) -> None:
        """Model should be pre-fitted with demo coefficients."""
        assert turnover_model.is_fitted is True

    def test_predict_returns_score_and_factors(
        self, turnover_model: TurnoverModel, sample_employee_dict: Dict[str, object]
    ) -> None:
        """Predict should return a risk score and contributing factors."""
        risk_score, factors = turnover_model.predict(sample_employee_dict)

        assert isinstance(risk_score, float)
        assert 0.0 <= risk_score <= 1.0
        assert isinstance(factors, dict)
        assert len(factors) > 0

    def test_predict_score_range(
        self, turnover_model: TurnoverModel, sample_employee_dict: Dict[str, object]
    ) -> None:
        """Risk score should be between 0 and 1."""
        risk_score, _ = turnover_model.predict(sample_employee_dict)
        assert 0.0 <= risk_score <= 1.0

    def test_high_absence_increases_risk(
        self, turnover_model: TurnoverModel
    ) -> None:
        """Higher absence rate should increase turnover risk."""
        low_absence = {
            "tenure_months": 24,
            "salary": 65000,
            "absence_rate": 0.05,
            "performance_score": 3.5,
            "department": "engineering",
            "recent_leave_days": 2,
        }
        high_absence = {
            "tenure_months": 24,
            "salary": 65000,
            "absence_rate": 0.50,
            "performance_score": 3.5,
            "department": "engineering",
            "recent_leave_days": 2,
        }

        low_score, _ = turnover_model.predict(low_absence)
        high_score, _ = turnover_model.predict(high_absence)

        assert high_score > low_score

    def test_higher_performance_lowers_risk(
        self, turnover_model: TurnoverModel
    ) -> None:
        """Higher performance score should lower turnover risk."""
        low_perf = {
            "tenure_months": 24,
            "salary": 65000,
            "absence_rate": 0.1,
            "performance_score": 1.5,
            "department": "engineering",
            "recent_leave_days": 2,
        }
        high_perf = {
            "tenure_months": 24,
            "salary": 65000,
            "absence_rate": 0.1,
            "performance_score": 4.5,
            "department": "engineering",
            "recent_leave_days": 2,
        }

        low_perf_score, _ = turnover_model.predict(low_perf)
        high_perf_score, _ = turnover_model.predict(high_perf)

        assert low_perf_score > high_perf_score

    def test_factors_are_sorted_by_importance(
        self, turnover_model: TurnoverModel, sample_employee_dict: Dict[str, object]
    ) -> None:
        """Contributing factors should be sorted by absolute value."""
        _, factors = turnover_model.predict(sample_employee_dict)

        values = list(factors.values())
        abs_values = [abs(v) for v in values]
        assert abs_values == sorted(abs_values, reverse=True)

    def test_train_updates_model(self, turnover_model: TurnoverModel) -> None:
        """Training with data should update the model."""
        training_data = [
            {
                "tenure_months": 12,
                "salary": 50000,
                "absence_rate": 0.1,
                "performance_score": 4.0,
                "department": "engineering",
                "recent_leave_days": 2,
                "turnover": 0,
            },
            {
                "tenure_months": 3,
                "salary": 35000,
                "absence_rate": 0.4,
                "performance_score": 2.0,
                "department": "sales",
                "recent_leave_days": 8,
                "turnover": 1,
            },
        ]

        result = turnover_model.train(training_data)

        assert result["samples_trained"] == 2
        assert "accuracy" in result
        assert turnover_model.is_fitted is True

    def test_train_empty_data(self, turnover_model: TurnoverModel) -> None:
        """Training with empty data should return error."""
        result = turnover_model.train([])
        assert "error" in result

    def test_feature_extraction_unknown_department(
        self, turnover_model: TurnoverModel
    ) -> None:
        """Unknown department should produce zero one-hot encoding."""
        employee = {
            "tenure_months": 24,
            "salary": 65000,
            "absence_rate": 0.1,
            "performance_score": 3.5,
            "department": "unknown_dept",
            "recent_leave_days": 2,
        }

        # Should not raise an error
        risk_score, factors = turnover_model.predict(employee)
        assert 0.0 <= risk_score <= 1.0


class TestTurnoverService:
    """Tests for the TurnoverService class."""

    def test_predict_returns_turnover_prediction(
        self, sample_employee_features: EmployeeFeatures
    ) -> None:
        """Service should return a TurnoverPrediction."""
        service = TurnoverService()
        prediction = service.predict(sample_employee_features)

        assert 0.0 <= prediction.risk_score <= 1.0
        assert prediction.risk_level in ("low", "medium", "high")
        assert isinstance(prediction.factors, dict)

    def test_risk_level_classification(self) -> None:
        """Risk levels should be correctly classified."""
        service = TurnoverService()

        assert service._classify_risk(0.1) == "low"
        assert service._classify_risk(0.29) == "low"
        assert service._classify_risk(0.3) == "medium"
        assert service._classify_risk(0.5) == "medium"
        assert service._classify_risk(0.69) == "medium"
        assert service._classify_risk(0.7) == "high"
        assert service._classify_risk(0.9) == "high"

    def test_batch_prediction(
        self,
        sample_employee_features: EmployeeFeatures,
        high_risk_employee_features: EmployeeFeatures,
    ) -> None:
        """Batch prediction should return results for all employees."""
        service = TurnoverService()
        employees = [sample_employee_features, high_risk_employee_features]

        predictions = service.predict_batch(employees)

        assert len(predictions) == 2
        assert all(0.0 <= p.risk_score <= 1.0 for p in predictions)

    def test_high_risk_employee_has_higher_score(
        self,
        low_risk_employee_features: EmployeeFeatures,
        high_risk_employee_features: EmployeeFeatures,
    ) -> None:
        """High-risk employee should have a higher risk score."""
        service = TurnoverService()

        low_pred = service.predict(low_risk_employee_features)
        high_pred = service.predict(high_risk_employee_features)

        assert high_pred.risk_score > low_pred.risk_score
