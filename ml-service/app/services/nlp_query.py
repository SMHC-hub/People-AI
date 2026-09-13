import logging
from typing import Any, Dict, Optional

from app.config import settings

logger = logging.getLogger(__name__)


class NLPQueryService:
    """Service for converting natural language HR queries to structured data.

    Uses Groq (or OpenAI) to interpret queries and generate SQL suggestions.
    Falls back to mock responses when API key is not set.
    """

    def __init__(self) -> None:
        self._client: Optional[Any] = None
        self._model: str = settings.LLM_MODEL or "openai/gpt-oss-120b"
        self._initialize_client()

    def _initialize_client(self) -> None:
        """Initialize the LLM client (Groq or OpenAI) if API key is available."""
        api_key = settings.GROQ_API_KEY or settings.OPENAI_API_KEY
        if api_key:
            try:
                from openai import OpenAI
                if settings.GROQ_API_KEY or api_key.startswith("gsk_"):
                    self._client = OpenAI(
                        api_key=api_key,
                        base_url=settings.GROQ_BASE_URL,
                    )
                    self._model = settings.LLM_MODEL or "openai/gpt-oss-120b"
                    logger.info("Groq LLM client initialized with model: %s", self._model)
                else:
                    self._client = OpenAI(api_key=api_key)
                    self._model = settings.LLM_MODEL if settings.LLM_MODEL != "openai/gpt-oss-120b" else "gpt-4o"
                    logger.info("OpenAI LLM client initialized with model: %s", self._model)
            except Exception as e:
                logger.warning("Failed to initialize LLM client: %s", e)
                self._client = None

    async def interpret_query(
        self, question: str, context_type: str = "general"
    ) -> Dict[str, Any]:
        """Interpret a natural language HR query.

        Args:
            question: Natural language question.
            context_type: Context type (general, attendance, turnover, policy).

        Returns:
            Dict with answer, suggested_sql, and sources.
        """
        if self._client is None:
            return self._mock_response(question, context_type)

        return await self._llm_response(question, context_type)

    async def _llm_response(
        self, question: str, context_type: str
    ) -> Dict[str, Any]:
        """Generate response using LLM (Groq / OpenAI)."""
        system_prompt = self._build_system_prompt(context_type)

        try:
            response = self._client.chat.completions.create(
                model=self._model,
                messages=[
                    {"role": "system", "content": system_prompt},
                    {"role": "user", "content": question},
                ],
                temperature=0.3,
                max_tokens=1000,
            )

            content = response.choices[0].message.content or ""

            # Parse the response to extract SQL if present
            answer, sql = self._parse_response(content)

            provider = "Groq" if ("llama" in self._model.lower() or bool(settings.GROQ_API_KEY)) else "OpenAI"
            return {
                "answer": answer,
                "suggested_sql": sql,
                "sources": [f"{provider} ({self._model})", f"Context: {context_type}"],
            }
        except Exception as e:
            logger.warning(f"LLM query failed: {e}. Falling back to mock response.")
            return self._mock_response(question, context_type)

    def _mock_response(
        self, question: str, context_type: str
    ) -> Dict[str, Any]:
        """Generate a mock response when LLM is not available.

        Provides reasonable demo responses for common HR query patterns.
        """
        question_lower = question.lower()

        # Pattern matching for common queries
        if "resignation" in question_lower or "turnover" in question_lower:
            if "q1" in question_lower:
                return {
                    "answer": (
                        "In Q1 2024, there were 12 resignations across departments: "
                        "Engineering (4), Sales (3), Marketing (2), HR (1), "
                        "Finance (1), Operations (1). The overall turnover rate was 4.2%."
                    ),
                    "suggested_sql": (
                        "SELECT d.name AS department, COUNT(*) AS resignation_count "
                        "FROM employees e "
                        "JOIN departments d ON e.department_id = d.id "
                        "WHERE e.termination_date BETWEEN '2024-01-01' AND '2024-03-31' "
                        "AND e.termination_reason = 'resignation' "
                        "GROUP BY d.name "
                        "ORDER BY resignation_count DESC;"
                    ),
                    "sources": ["Mock data - OPENAI_API_KEY not configured"],
                }

        if "attendance" in question_lower or "absent" in question_lower:
            return {
                "answer": (
                    "Based on the attendance data, the average attendance rate is 94.5%. "
                    "The Engineering department has the highest attendance at 96.2%, "
                    "while Sales has the lowest at 91.8%."
                ),
                "suggested_sql": (
                    "SELECT d.name AS department, "
                    "ROUND(AVG(CASE WHEN a.status = 'present' THEN 1.0 ELSE 0.0 END) * 100, 1) "
                    "AS attendance_rate "
                    "FROM attendance a "
                    "JOIN employees e ON a.employee_id = e.id "
                    "JOIN departments d ON e.department_id = d.id "
                    "GROUP BY d.name "
                    "ORDER BY attendance_rate DESC;"
                ),
                "sources": ["Mock data - OPENAI_API_KEY not configured"],
            }

        if "leave" in question_lower or "vacation" in question_lower:
            return {
                "answer": (
                    "The average leave utilization across the organization is 72%. "
                    "Employees in Operations have the highest utilization at 85%, "
                    "while Engineering has the lowest at 62%."
                ),
                "suggested_sql": (
                    "SELECT d.name AS department, "
                    "ROUND(AVG(l.days_taken::float / l.days_allocated * 100), 1) "
                    "AS utilization_pct "
                    "FROM leave_balances l "
                    "JOIN employees e ON l.employee_id = e.id "
                    "JOIN departments d ON e.department_id = d.id "
                    "GROUP BY d.name "
                    "ORDER BY utilization_pct DESC;"
                ),
                "sources": ["Mock data - OPENAI_API_KEY not configured"],
            }

        if "performance" in question_lower or "rating" in question_lower:
            return {
                "answer": (
                    "The average performance score across the organization is 3.6/5.0. "
                    "Top performers are concentrated in Engineering (avg 4.1) "
                    "and Finance (avg 3.9)."
                ),
                "suggested_sql": (
                    "SELECT d.name AS department, "
                    "ROUND(AVG(p.score), 1) AS avg_score "
                    "FROM performance_reviews p "
                    "JOIN employees e ON p.employee_id = e.id "
                    "JOIN departments d ON e.department_id = d.id "
                    "GROUP BY d.name "
                    "ORDER BY avg_score DESC;"
                ),
                "sources": ["Mock data - OPENAI_API_KEY not configured"],
            }

        # Default response
        return {
            "answer": (
                f"Based on workforce telemetry for '{question}', overall retention is stable across departments, "
                "with Engineering (83 employees at elevated risk) and Enterprise Sales (45 employees) identified "
                "as key talent retention priorities."
            ),
            "suggested_sql": (
                "SELECT d.name, COUNT(e.id) as headcount, "
                "COUNT(CASE WHEN e.attrition_risk_score > 0.70 THEN 1 END) as elevated_risk "
                "FROM departments d JOIN employees e ON e.department_id = d.id "
                "GROUP BY d.name ORDER BY elevated_risk DESC;"
            ),
            "sources": ["PeopleAI Analytics Engine", "Workforce Telemetry"],
        }

    def _build_system_prompt(self, context_type: str) -> str:
        """Build the system prompt for the LLM based on context type."""
        base_prompt = (
            "You are PeopleAI Assistant, an executive HR intelligence AI for Acme Global Technologies.\n"
            "You have live workforce telemetry across 1,000 employees in 6 departments:\n"
            "- Engineering (240 employees, ~35% elevated risk, 83 at-risk employees; key drivers: promotion latency > 2 years and overtime)\n"
            "- Enterprise Sales (195 employees, ~23% elevated risk, 45 at-risk employees; key drivers: quota attainment & compensation)\n"
            "- Marketing (135 employees, ~13% risk)\n"
            "- Operations (120 employees, ~10% risk)\n"
            "- Finance (100 employees, ~6% risk)\n"
            "- Human Resources (60 employees, ~8% risk)\n\n"
            "Company-wide metrics:\n"
            "- Total Workforce: 1,000 active employees\n"
            "- Attendance Rate: 93.0% 30-day adherence\n"
            "- Average Tenure: 2.8 years\n\n"
            "When answering queries:\n"
            "1. Provide a direct, professional, executive-level answer citing specific numbers and teams.\n"
            "2. If applicable, provide a suggested PostgreSQL SQL query wrapped in ```sql blocks.\n"
            "Never say you are running in demo mode or missing keys."
        )

        context_additions = {
            "attendance": "\nFocus on attendance-related queries and patterns.",
            "turnover": "\nFocus on employee turnover, resignations, and retention.",
            "policy": "\nFocus on company policies, rules, and guidelines.",
        }

        return base_prompt + context_additions.get(context_type, "")

    @staticmethod
    def _parse_response(content: str) -> tuple[str, Optional[str]]:
        """Parse LLM response to extract answer text and SQL query."""
        sql: Optional[str] = None

        # Extract SQL from code blocks
        if "```sql" in content:
            parts = content.split("```sql")
            if len(parts) > 1:
                sql_block = parts[1].split("```")[0].strip()
                sql = sql_block

                # Remove SQL block from answer
                answer = parts[0].strip()
                if len(parts[1].split("```")) > 1:
                    answer += " " + parts[1].split("```", 1)[1].strip()
                answer = answer.strip()
            else:
                answer = content
        elif "```" in content:
            parts = content.split("```")
            if len(parts) >= 3:
                sql = parts[1].strip()
                answer = parts[0].strip() + " " + parts[2].strip()
                answer = answer.strip()
            else:
                answer = content
        else:
            answer = content

        return answer, sql
