"""Turnover prediction model using LogisticRegression."""

from typing import Any, Dict, List, Optional, Tuple

import numpy as np
from sklearn.linear_model import LogisticRegression


# Feature names used by the model
FEATURE_NAMES: List[str] = [
    "tenure_months",
    "salary_normalized",
    "absence_rate",
    "performance_score",
    "recent_leave_days",
    "dept_engineering",
    "dept_sales",
    "dept_hr",
    "dept_marketing",
    "dept_finance",
    "dept_operations",
]


class TurnoverModel:
    """Turnover risk prediction model using LogisticRegression.

    Includes pre-fitted demo coefficients so the model works without
    training on real data.
    """

    def __init__(self) -> None:
        self.model: LogisticRegression = LogisticRegression(max_iter=1000)
        self.is_fitted: bool = False
        self.feature_names: List[str] = FEATURE_NAMES
        self._initialize_demo_model()

    def _initialize_demo_model(self) -> None:
        """Initialize model with pre-fitted demo coefficients.

        These coefficients represent realistic turnover risk factors:
        - Higher absence rate -> higher risk
        - Lower performance -> higher risk
        - Lower tenure -> higher risk
        - Higher recent leave -> higher risk
        - Salary has moderate negative effect (higher salary -> lower risk)
        """
        # Create a minimal synthetic dataset to fit the model shape
        n_features = len(self.feature_names)
        # We need at least 2 samples with different classes
        X_dummy = np.zeros((2, n_features))
        X_dummy[0] = [12, 0.5, 0.1, 4.0, 2, 1, 0, 0, 0, 0, 0]
        X_dummy[1] = [6, 0.3, 0.4, 2.0, 8, 0, 1, 0, 0, 0, 0]
        y_dummy = np.array([0, 1])

        self.model.fit(X_dummy, y_dummy)

        # Override with realistic demo coefficients
        self.model.coef_ = np.array([[
            -0.02,   # tenure_months: longer tenure -> lower risk
            -0.8,    # salary_normalized: higher salary -> lower risk
            2.5,     # absence_rate: higher absence -> higher risk
            -0.6,    # performance_score: better performance -> lower risk
            0.15,    # recent_leave_days: more leave -> higher risk
            -0.1,    # dept_engineering
            0.3,     # dept_sales
            -0.05,   # dept_hr
            0.2,     # dept_marketing
            -0.15,   # dept_finance
            0.1,     # dept_operations
        ]])
        self.model.intercept_ = np.array([0.5])
        self.is_fitted = True

    def train(self, data: List[Dict[str, Any]]) -> Dict[str, Any]:
        """Train the model on employee records.

        Args:
            data: List of employee records with features and 'turnover' label.

        Returns:
            Training metrics.
        """
        if not data:
            return {"error": "No training data provided"}

        X, y = self._prepare_training_data(data)
        self.model.fit(X, y)
        self.is_fitted = True

        # Compute training accuracy
        predictions = self.model.predict(X)
        accuracy = float(np.mean(predictions == y))

        return {
            "samples_trained": len(data),
            "accuracy": accuracy,
            "features_used": self.feature_names,
        }

    def predict(self, employee_features: Dict[str, Any]) -> Tuple[float, Dict[str, float]]:
        """Predict turnover risk for a single employee.

        Args:
            employee_features: Dictionary with employee feature values.

        Returns:
            Tuple of (risk_score, top_factors).
        """
        if not self.is_fitted:
            raise RuntimeError("Model has not been fitted. Call train() first.")

        feature_vector = self._extract_features(employee_features)
        X = np.array([feature_vector])

        # Get probability of turnover (class 1)
        risk_score = float(self.model.predict_proba(X)[0][1])

        # Compute feature contributions
        factors = self._compute_factors(feature_vector)

        return risk_score, factors

    def _extract_features(self, employee: Dict[str, Any]) -> List[float]:
        """Extract feature vector from employee dictionary."""
        department = employee.get("department", "").lower()

        # Normalize salary to 0-1 range (assuming salary range 20k-200k)
        salary = employee.get("salary", 50000)
        salary_normalized = min(max((salary - 20000) / 180000, 0.0), 1.0)

        # One-hot encode department
        dept_map = {
            "engineering": [1, 0, 0, 0, 0, 0],
            "sales": [0, 1, 0, 0, 0, 0],
            "hr": [0, 0, 1, 0, 0, 0],
            "marketing": [0, 0, 0, 1, 0, 0],
            "finance": [0, 0, 0, 0, 1, 0],
            "operations": [0, 0, 0, 0, 0, 1],
        }
        dept_encoded = dept_map.get(department, [0, 0, 0, 0, 0, 0])

        return [
            float(employee.get("tenure_months", 0)),
            salary_normalized,
            float(employee.get("absence_rate", 0)),
            float(employee.get("performance_score", 3.0)),
            float(employee.get("recent_leave_days", 0)),
        ] + dept_encoded

    def _compute_factors(self, feature_vector: List[float]) -> Dict[str, float]:
        """Compute top contributing factors for the prediction.

        Returns the factors sorted by absolute contribution.
        """
        coefficients = self.model.coef_[0]
        contributions: Dict[str, float] = {}

        for name, coef, val in zip(self.feature_names, coefficients, feature_vector):
            # Skip zero-valued department dummies
            if name.startswith("dept_") and val == 0.0:
                continue
            contributions[name] = round(float(coef * val), 4)

        # Sort by absolute contribution and keep top 5
        sorted_factors = dict(
            sorted(contributions.items(), key=lambda x: abs(x[1]), reverse=True)[:5]
        )

        return sorted_factors

    def _prepare_training_data(
        self, data: List[Dict[str, Any]]
    ) -> Tuple[np.ndarray, np.ndarray]:
        """Prepare training data from raw employee records."""
        X_list: List[List[float]] = []
        y_list: List[int] = []

        for record in data:
            features = self._extract_features(record)
            X_list.append(features)
            y_list.append(int(record.get("turnover", 0)))

        return np.array(X_list), np.array(y_list)
