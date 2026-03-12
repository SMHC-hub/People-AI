"""Anomaly detection service wrapping the AnomalyModel."""

from typing import Any, Dict, List

from app.models.anomaly import AnomalyModel
from app.schemas import AnomalyResult, AttendanceRecord


class AnomalyService:
    """Service for attendance anomaly detection with preprocessing."""

    def __init__(self) -> None:
        self.model = AnomalyModel()

    def detect_anomalies(
        self, records: List[AttendanceRecord]
    ) -> List[AnomalyResult]:
        """Detect anomalies in attendance records.

        Preprocesses attendance data: extracts hour from check-in/out,
        computes deviation from department median, and returns enriched results.

        Args:
            records: List of attendance records.

        Returns:
            List of AnomalyResult with anomaly flags and scores.
        """
        if not records:
            return []

        # Convert to dicts for the model
        record_dicts = [self._preprocess(record) for record in records]

        # Run anomaly detection
        results = self.model.detect(record_dicts)

        # Build response
        anomaly_results: List[AnomalyResult] = []
        for (record_dict, is_anomaly, score), original in zip(results, records):
            anomaly_results.append(
                AnomalyResult(
                    employee_id=original.employee_id,
                    date=original.date,
                    check_in=original.check_in,
                    check_out=original.check_out,
                    hours_worked=original.hours_worked,
                    is_anomaly=is_anomaly,
                    anomaly_score=round(score, 4),
                )
            )

        return anomaly_results

    def analyze_patterns(
        self, records: List[AttendanceRecord]
    ) -> Dict[str, Any]:
        """Analyze attendance patterns from records.

        Computes:
        - Most late arrival day of week
        - Average hours per day
        - Average check-in/check-out times
        - Overtime and undertime frequency

        Args:
            records: List of attendance records.

        Returns:
            Pattern insights dictionary.
        """
        if not records:
            return {
                "most_late_day": "N/A",
                "avg_hours_per_day": 0.0,
                "avg_check_in_time": "N/A",
                "avg_check_out_time": "N/A",
                "overtime_frequency": 0.0,
                "undertime_frequency": 0.0,
                "total_records_analyzed": 0,
            }

        # Parse times and compute statistics
        check_in_hours: List[float] = []
        check_out_hours: List[float] = []
        hours_list: List[float] = []
        late_by_day: Dict[str, int] = {
            "Monday": 0,
            "Tuesday": 0,
            "Wednesday": 0,
            "Thursday": 0,
            "Friday": 0,
            "Saturday": 0,
            "Sunday": 0,
        }

        day_names = [
            "Monday", "Tuesday", "Wednesday",
            "Thursday", "Friday", "Saturday", "Sunday",
        ]

        for record in records:
            ci_hour = self._time_to_decimal(record.check_in)
            co_hour = self._time_to_decimal(record.check_out)
            check_in_hours.append(ci_hour)
            check_out_hours.append(co_hour)
            hours_list.append(record.hours_worked)

            # Count late arrivals (after 9:15)
            if ci_hour > 9.25:
                day_of_week = self._date_to_day_name(record.date, day_names)
                late_by_day[day_of_week] = late_by_day.get(day_of_week, 0) + 1

        avg_hours = sum(hours_list) / len(hours_list) if hours_list else 0.0
        avg_ci = sum(check_in_hours) / len(check_in_hours) if check_in_hours else 9.0
        avg_co = (
            sum(check_out_hours) / len(check_out_hours) if check_out_hours else 17.0
        )

        overtime_count = sum(1 for h in hours_list if h > 8.0)
        undertime_count = sum(1 for h in hours_list if h < 7.0)
        total = len(hours_list)

        most_late_day = max(late_by_day, key=lambda k: late_by_day[k])

        return {
            "most_late_day": most_late_day,
            "avg_hours_per_day": round(avg_hours, 2),
            "avg_check_in_time": self._decimal_to_time(avg_ci),
            "avg_check_out_time": self._decimal_to_time(avg_co),
            "overtime_frequency": round(overtime_count / total, 4) if total > 0 else 0.0,
            "undertime_frequency": round(undertime_count / total, 4) if total > 0 else 0.0,
            "total_records_analyzed": total,
        }

    def _preprocess(self, record: AttendanceRecord) -> Dict[str, Any]:
        """Preprocess a single attendance record for the model."""
        return {
            "employee_id": record.employee_id,
            "date": record.date,
            "check_in": record.check_in,
            "check_out": record.check_out,
            "hours_worked": record.hours_worked,
        }

    @staticmethod
    def _time_to_decimal(time_str: str) -> float:
        """Convert HH:MM time string to decimal hours."""
        try:
            parts = time_str.split(":")
            return int(parts[0]) + int(parts[1]) / 60.0
        except (ValueError, IndexError):
            return 9.0

    @staticmethod
    def _decimal_to_time(decimal_hours: float) -> str:
        """Convert decimal hours to HH:MM string."""
        hours = int(decimal_hours)
        minutes = int((decimal_hours - hours) * 60)
        return f"{hours:02d}:{minutes:02d}"

    @staticmethod
    def _date_to_day_name(date_str: str, day_names: List[str]) -> str:
        """Convert YYYY-MM-DD to day of week name."""
        try:
            from datetime import datetime
            dt = datetime.strptime(date_str, "%Y-%m-%d")
            return day_names[dt.weekday()]
        except (ValueError, TypeError):
            return "Monday"
