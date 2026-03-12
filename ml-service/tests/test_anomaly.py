"""Tests for the anomaly detection model and service."""

from typing import List

import pytest

from app.models.anomaly import AnomalyModel
from app.schemas import AttendanceRecord
from app.services.anomaly import AnomalyService


class TestAnomalyModel:
    """Tests for the AnomalyModel class."""

    def test_model_initializes_fitted(self, anomaly_model: AnomalyModel) -> None:
        """Model should be pre-fitted with demo data."""
        assert anomaly_model.is_fitted is True

    def test_detect_returns_results(self, anomaly_model: AnomalyModel) -> None:
        """Detection should return results for each input record."""
        records = [
            {
                "employee_id": "EMP001",
                "date": "2024-01-15",
                "check_in": "09:00",
                "check_out": "17:30",
                "hours_worked": 8.5,
            },
            {
                "employee_id": "EMP002",
                "date": "2024-01-15",
                "check_in": "09:05",
                "check_out": "17:35",
                "hours_worked": 8.5,
            },
        ]

        results = anomaly_model.detect(records)

        assert len(results) == 2
        for record, is_anomaly, score in results:
            assert isinstance(is_anomaly, bool)
            assert isinstance(score, float)

    def test_normal_records_are_not_anomalous(
        self, anomaly_model: AnomalyModel
    ) -> None:
        """Normal attendance records should not be flagged as anomalies."""
        normal_records = [
            {
                "employee_id": f"EMP{i:03d}",
                "date": "2024-01-15",
                "check_in": "09:00",
                "check_out": "17:30",
                "hours_worked": 8.5,
            }
            for i in range(10)
        ]

        results = anomaly_model.detect(normal_records)

        # Most normal records should not be anomalous
        anomaly_count = sum(1 for _, is_anomaly, _ in results if is_anomaly)
        assert anomaly_count < len(normal_records) * 0.5  # Less than 50% anomalies

    def test_extreme_records_are_anomalous(
        self, anomaly_model: AnomalyModel
    ) -> None:
        """Extreme attendance patterns should be flagged as anomalies."""
        extreme_records = [
            {
                "employee_id": "EMP001",
                "date": "2024-01-15",
                "check_in": "03:00",
                "check_out": "23:00",
                "hours_worked": 20.0,
            },
        ]

        results = anomaly_model.detect(extreme_records)

        # This extreme record should be anomalous
        _, is_anomaly, score = results[0]
        assert is_anomaly is True

    def test_detect_empty_records(self, anomaly_model: AnomalyModel) -> None:
        """Detection with empty input should return empty results."""
        results = anomaly_model.detect([])
        assert results == []

    def test_time_to_decimal(self) -> None:
        """Time conversion should be correct."""
        assert AnomalyModel._time_to_decimal("09:00") == 9.0
        assert AnomalyModel._time_to_decimal("09:30") == 9.5
        assert AnomalyModel._time_to_decimal("17:45") == 17.75
        assert AnomalyModel._time_to_decimal("00:00") == 0.0

    def test_time_to_decimal_invalid(self) -> None:
        """Invalid time strings should return default value."""
        assert AnomalyModel._time_to_decimal("invalid") == 9.0
        assert AnomalyModel._time_to_decimal("") == 9.0


class TestAnomalyService:
    """Tests for the AnomalyService class."""

    def test_detect_anomalies_returns_results(
        self, sample_attendance_records: List[AttendanceRecord]
    ) -> None:
        """Service should return AnomalyResult for each record."""
        service = AnomalyService()
        results = service.detect_anomalies(sample_attendance_records)

        assert len(results) == len(sample_attendance_records)
        for result in results:
            assert hasattr(result, "is_anomaly")
            assert hasattr(result, "anomaly_score")
            assert isinstance(result.is_anomaly, bool)

    def test_detect_anomalies_empty_input(self) -> None:
        """Empty input should return empty results."""
        service = AnomalyService()
        results = service.detect_anomalies([])
        assert results == []

    def test_detect_anomalies_preserves_record_data(
        self, sample_attendance_records: List[AttendanceRecord]
    ) -> None:
        """Original record data should be preserved in results."""
        service = AnomalyService()
        results = service.detect_anomalies(sample_attendance_records)

        for result, original in zip(results, sample_attendance_records):
            assert result.employee_id == original.employee_id
            assert result.date == original.date
            assert result.check_in == original.check_in
            assert result.check_out == original.check_out
            assert result.hours_worked == original.hours_worked

    def test_analyze_patterns_returns_insights(
        self, sample_attendance_records: List[AttendanceRecord]
    ) -> None:
        """Pattern analysis should return expected insight fields."""
        service = AnomalyService()
        insights = service.analyze_patterns(sample_attendance_records)

        assert "most_late_day" in insights
        assert "avg_hours_per_day" in insights
        assert "avg_check_in_time" in insights
        assert "avg_check_out_time" in insights
        assert "overtime_frequency" in insights
        assert "undertime_frequency" in insights
        assert "total_records_analyzed" in insights
        assert insights["total_records_analyzed"] == len(sample_attendance_records)

    def test_analyze_patterns_empty_input(self) -> None:
        """Empty input should return default pattern values."""
        service = AnomalyService()
        insights = service.analyze_patterns([])

        assert insights["total_records_analyzed"] == 0
        assert insights["most_late_day"] == "N/A"

    def test_analyze_patterns_overtime_detection(self) -> None:
        """Should correctly detect overtime frequency."""
        service = AnomalyService()
        records = [
            AttendanceRecord(
                employee_id="EMP001",
                date="2024-01-15",
                check_in="09:00",
                check_out="18:30",
                hours_worked=9.5,  # overtime
            ),
            AttendanceRecord(
                employee_id="EMP001",
                date="2024-01-16",
                check_in="09:00",
                check_out="17:00",
                hours_worked=8.0,  # not overtime
            ),
        ]

        insights = service.analyze_patterns(records)

        assert insights["overtime_frequency"] == 0.5  # 1 out of 2

    def test_anomalous_records_flagged(
        self, anomalous_attendance_records: List[AttendanceRecord]
    ) -> None:
        """Records with extreme values should be flagged as anomalies."""
        service = AnomalyService()
        results = service.detect_anomalies(anomalous_attendance_records)

        # At least one of the extreme records should be flagged
        anomaly_flags = [r.is_anomaly for r in results]
        assert any(anomaly_flags), "Expected at least one anomaly to be detected"
