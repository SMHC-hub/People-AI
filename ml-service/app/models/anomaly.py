"""Anomaly detection model for attendance records using IsolationForest."""

from typing import Any, Dict, List, Tuple

import numpy as np
from sklearn.ensemble import IsolationForest


class AnomalyModel:
    """Anomaly detection model for attendance records.

    Uses IsolationForest with pre-fitted demo parameters so it works
    without training data.
    """

    def __init__(self) -> None:
        self.model: IsolationForest = IsolationForest(
            n_estimators=100,
            contamination=0.1,
            random_state=42,
        )
        self.is_fitted: bool = False
        self._initialize_demo_model()

    def _initialize_demo_model(self) -> None:
        """Initialize with demo data so the model works out of the box.

        Fits on synthetic normal attendance patterns so it can detect
        anomalies in new data.
        """
        rng = np.random.RandomState(42)

        # Generate synthetic normal attendance data
        # Features: [check_in_hour, check_out_hour, hours_worked, day_of_week]
        n_samples = 200

        # Normal check-in: around 9:00 (std 0.5h)
        check_in = rng.normal(9.0, 0.5, n_samples)
        # Normal check-out: around 17:30 (std 0.5h)
        check_out = rng.normal(17.5, 0.5, n_samples)
        # Normal hours: around 8.5 (std 0.5h)
        hours = check_out - check_in
        # Day of week: 0-4 (weekdays)
        day_of_week = rng.randint(0, 5, n_samples).astype(float)

        X_train = np.column_stack([check_in, check_out, hours, day_of_week])
        self.model.fit(X_train)
        self.is_fitted = True

    def detect(
        self, attendance_records: List[Dict[str, Any]]
    ) -> List[Tuple[Dict[str, Any], bool, float]]:
        """Detect anomalies in attendance records.

        Args:
            attendance_records: List of attendance record dicts with keys:
                - employee_id, date, check_in, check_out, hours_worked

        Returns:
            List of tuples: (original_record, is_anomaly, anomaly_score)
        """
        if not attendance_records:
            return []

        if not self.is_fitted:
            raise RuntimeError("Model has not been fitted.")

        # Extract features from records
        features = self._extract_features(attendance_records)
        X = np.array(features)

        # Predict: -1 = anomaly, 1 = normal
        predictions = self.model.predict(X)
        # Anomaly scores: lower (more negative) = more anomalous
        scores = self.model.decision_function(X)

        results: List[Tuple[Dict[str, Any], bool, float]] = []
        for record, pred, score in zip(attendance_records, predictions, scores):
            is_anomaly = bool(pred == -1)
            results.append((record, is_anomaly, float(score)))

        return results

    def _extract_features(
        self, records: List[Dict[str, Any]]
    ) -> List[List[float]]:
        """Extract numerical features from attendance records.

        Features:
        - check_in_hour (decimal)
        - check_out_hour (decimal)
        - hours_worked
        - day_of_week (0=Mon, 6=Sun)
        """
        features: List[List[float]] = []

        for record in records:
            check_in_hour = self._time_to_decimal(record.get("check_in", "09:00"))
            check_out_hour = self._time_to_decimal(record.get("check_out", "17:00"))
            hours_worked = float(record.get("hours_worked", 8.0))
            day_of_week = self._date_to_day_of_week(record.get("date", "2024-01-01"))

            features.append([
                check_in_hour,
                check_out_hour,
                hours_worked,
                day_of_week,
            ])

        return features

    @staticmethod
    def _time_to_decimal(time_str: str) -> float:
        """Convert HH:MM time string to decimal hours."""
        try:
            parts = time_str.split(":")
            hours = int(parts[0])
            minutes = int(parts[1]) if len(parts) > 1 else 0
            return hours + minutes / 60.0
        except (ValueError, IndexError):
            return 9.0  # Default to 9:00

    @staticmethod
    def _date_to_day_of_week(date_str: str) -> float:
        """Convert YYYY-MM-DD date string to day of week (0=Mon)."""
        try:
            from datetime import datetime
            dt = datetime.strptime(date_str, "%Y-%m-%d")
            return float(dt.weekday())
        except (ValueError, TypeError):
            return 0.0  # Default to Monday
