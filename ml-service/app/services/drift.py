"""MLOps Data Drift Detection and Model Monitoring Service.

Implements Population Stability Index (PSI) calculation and feature drift tracking.
"""

import logging
from typing import Any, Dict, List
import numpy as np

logger = logging.getLogger(__name__)


def calculate_psi(
    expected: np.ndarray, actual: np.ndarray, num_buckets: int = 10
) -> float:
    """Calculate the Population Stability Index (PSI) between two continuous distributions.

    PSI Formula:
        PSI = sum((Actual% - Expected%) * ln(Actual% / Expected%))

    Interpretation:
        PSI < 0.10: No significant distribution change (Healthy)
        0.10 <= PSI < 0.25: Moderate drift (Warning)
        PSI >= 0.25: Significant distribution shift (Drift Detected - Retraining Required)
    """
    if len(expected) == 0 or len(actual) == 0:
        return 0.0

    # Determine quantile bins based on reference distribution
    percentiles = np.linspace(0, 100, num_buckets + 1)
    bins = np.percentile(expected, percentiles)
    bins[0] -= 1e-5
    bins[-1] += 1e-5

    # Bucket counts
    expected_counts = np.histogram(expected, bins=bins)[0]
    actual_counts = np.histogram(actual, bins=bins)[0]

    # Convert to fractions with Laplace smoothing to avoid division by zero
    expected_pct = (expected_counts + 1e-4) / (np.sum(expected_counts) + 1e-4 * num_buckets)
    actual_pct = (actual_counts + 1e-4) / (np.sum(actual_counts) + 1e-4 * num_buckets)

    # Compute PSI
    psi_val = np.sum((actual_pct - expected_pct) * np.log(actual_pct / expected_pct))
    return float(np.round(max(psi_val, 0.0), 4))


class DriftMonitorService:
    """Enterprise MLOps feature drift and model performance monitor."""

    def __init__(self) -> None:
        np.random.seed(42)
        # Baseline reference distributions (training dataset)
        self.ref_salary = np.random.normal(78000, 22000, 1000).clip(35000, 220000)
        self.ref_satisfaction = np.random.choice([1, 2, 3, 4, 5], 1000, p=[0.12, 0.18, 0.35, 0.23, 0.12])
        self.ref_stagnation = np.random.exponential(1.8, 1000).clip(0, 8.0)
        self.ref_absence = np.random.exponential(0.06, 1000).clip(0, 0.45)

    def analyze_drift(self, current_data: Dict[str, List[float]] = None) -> Dict[str, Any]:
        """Analyze PSI drift for all monitored production features."""
        np.random.seed(101)

        # Current production samples (with realistic subtle drift)
        cur_salary = current_data.get("salary") if current_data else np.random.normal(81000, 23000, 600).clip(35000, 220000)
        cur_satisfaction = current_data.get("satisfaction") if current_data else np.random.choice([1, 2, 3, 4, 5], 600, p=[0.14, 0.22, 0.32, 0.21, 0.11])
        cur_stagnation = current_data.get("stagnation") if current_data else np.random.exponential(2.4, 600).clip(0, 8.5) # Warning drift
        cur_absence = current_data.get("absence") if current_data else np.random.exponential(0.07, 600).clip(0, 0.45)

        features = [
            {
                "feature": "MonthlyIncome / Salary",
                "psi": calculate_psi(self.ref_salary, np.array(cur_salary)),
                "reference_mean": float(np.round(np.mean(self.ref_salary), 2)),
                "current_mean": float(np.round(np.mean(cur_salary), 2)),
            },
            {
                "feature": "Job Satisfaction",
                "psi": calculate_psi(self.ref_satisfaction, np.array(cur_satisfaction)),
                "reference_mean": float(np.round(np.mean(self.ref_satisfaction), 2)),
                "current_mean": float(np.round(np.mean(cur_satisfaction), 2)),
            },
            {
                "feature": "Years Since Promotion",
                "psi": calculate_psi(self.ref_stagnation, np.array(cur_stagnation)),
                "reference_mean": float(np.round(np.mean(self.ref_stagnation), 2)),
                "current_mean": float(np.round(np.mean(cur_stagnation), 2)),
            },
            {
                "feature": "Absence Frequency",
                "psi": calculate_psi(self.ref_absence, np.array(cur_absence)),
                "reference_mean": float(np.round(np.mean(self.ref_absence), 3)),
                "current_mean": float(np.round(np.mean(cur_absence), 3)),
            },
        ]

        for f in features:
            psi = f["psi"]
            if psi < 0.10:
                f["status"] = "healthy"
                f["action"] = "Distribution stable within normal operating tolerance."
            elif psi < 0.25:
                f["status"] = "warning"
                f["action"] = "Moderate distribution deviation observed. Monitor incoming prediction batches."
            else:
                f["status"] = "drift_detected"
                f["action"] = "Significant shift detected. Automated retraining pipeline recommended."

        overall_max_psi = max(f["psi"] for f in features)

        return {
            "overall_status": "healthy" if overall_max_psi < 0.10 else ("warning" if overall_max_psi < 0.25 else "drift_detected"),
            "max_psi": overall_max_psi,
            "monitored_features_count": len(features),
            "features": features,
            "retraining_recommended": overall_max_psi >= 0.25,
        }
