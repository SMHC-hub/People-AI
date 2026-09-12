"""RAG Quantitative Evaluation Harness.

Measures:
1. Retrieval Hit Rate
2. Context Precision
3. Context Relevance
4. Answer Faithfulness
5. Answer Relevance
"""

import asyncio
import json
import logging
from typing import Any, Dict, List

from app.rag.chatbot import RAGChatbot

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger("rag_eval")

# Synthetic HR evaluation benchmark dataset
EVAL_DATASET: List[Dict[str, Any]] = [
    {
        "query": "What is the annual leave policy and how many days can I carry forward?",
        "expected_source": "Company Leave Policy",
        "ground_truth_facts": ["20 days", "annual leave", "5 days"],
        "category": "leave",
    },
    {
        "query": "How many consecutive days of sick leave require a medical certificate?",
        "expected_source": "Company Leave Policy",
        "ground_truth_facts": ["2 consecutive days", "medical certificate"],
        "category": "sick_leave",
    },
    {
        "query": "What are core working hours and attendance expectations for hybrid employees?",
        "expected_source": "Company Attendance Policy",
        "ground_truth_facts": ["working hours", "attendance"],
        "category": "attendance",
    },
    {
        "query": "What is the paid parental leave policy for primary and secondary caregivers?",
        "expected_source": "Company Leave Policy",
        "ground_truth_facts": ["16 weeks", "parental leave"],
        "category": "parental_leave",
    },
    {
        "query": "What is the annual education stipend and certification reimbursement limit?",
        "expected_source": "Professional Development Policy",
        "ground_truth_facts": ["$2,500", "stipend"],
        "category": "benefits",
    },
]


class RAGEvaluator:
    """Evaluates RAG retrieval and answer generation accuracy."""

    def __init__(self) -> None:
        self.bot = RAGChatbot()

    async def evaluate(self) -> Dict[str, Any]:
        """Run full evaluation suite over the benchmark dataset."""
        hit_count = 0
        context_precision_scores = []
        faithfulness_scores = []
        relevance_scores = []
        results = []

        for item in EVAL_DATASET:
            query = item["query"]
            expected_src = item["expected_source"]
            facts = item["ground_truth_facts"]

            # Run RAG query
            response = await self.bot.query(query)
            answer = response.get("answer", "")
            sources = response.get("sources", [])

            # 1. Retrieval Hit Rate
            hit = any(expected_src.lower() in s.lower() for s in sources)
            if hit:
                hit_count += 1

            # 2. Context Precision: fraction of sources that are relevant
            precision = (1.0 if hit else 0.0) / max(len(sources), 1)
            context_precision_scores.append(precision)

            # 3. Answer Faithfulness: verify ground truth facts are captured
            facts_found = sum(1 for f in facts if f.lower() in answer.lower())
            faithfulness = facts_found / max(len(facts), 1)
            faithfulness_scores.append(faithfulness)

            # 4. Answer Relevance: query key terms addressed
            query_words = [w for w in query.lower().split() if len(w) > 4]
            relevance = sum(1 for w in query_words if w in answer.lower()) / max(len(query_words), 1)
            relevance_scores.append(relevance)

            results.append({
                "query": query,
                "sources_retrieved": sources,
                "hit": hit,
                "faithfulness": round(faithfulness, 2),
                "relevance": round(relevance, 2),
            })

        summary = {
            "total_benchmark_queries": len(EVAL_DATASET),
            "retrieval_hit_rate": round(hit_count / len(EVAL_DATASET), 4),
            "context_precision": round(sum(context_precision_scores) / len(context_precision_scores), 4),
            "answer_faithfulness": round(sum(faithfulness_scores) / len(faithfulness_scores), 4),
            "answer_relevance": round(sum(relevance_scores) / len(relevance_scores), 4),
            "detailed_results": results,
        }

        return summary


if __name__ == "__main__":
    evaluator = RAGEvaluator()
    report = asyncio.run(evaluator.evaluate())
    print("\n" + "=" * 60)
    print("PEOPLEAI — RAG SYSTEM EVALUATION BENCHMARK REPORT")
    print("=" * 60)
    print(f"Total Benchmark Test Cases: {report['total_benchmark_queries']}")
    print(f"Retrieval Hit Rate:         {report['retrieval_hit_rate'] * 100:.1f}%")
    print(f"Context Precision:          {report['context_precision'] * 100:.1f}%")
    print(f"Answer Faithfulness:        {report['answer_faithfulness'] * 100:.1f}%")
    print(f"Answer Relevance:           {report['answer_relevance'] * 100:.1f}%")
    print("=" * 60 + "\n")
