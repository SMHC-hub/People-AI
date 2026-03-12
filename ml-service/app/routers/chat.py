"""Chat router for NLP queries and RAG-based policy Q&A."""

import logging

from fastapi import APIRouter, HTTPException

from app.rag.chatbot import RAGChatbot
from app.schemas import (
    ChatHistoryEntry,
    ChatHistoryResponse,
    ChatQuery,
    ChatResponse,
)
from app.services.nlp_query import NLPQueryService

logger = logging.getLogger(__name__)

router = APIRouter(prefix="/chat", tags=["chat"])

# Service instances
nlp_service = NLPQueryService()
rag_chatbot = RAGChatbot()


@router.post("/query", response_model=ChatResponse)
async def chat_query(query: ChatQuery) -> ChatResponse:
    """Process a natural language HR query.

    Interprets the question, generates a structured response with
    suggested SQL query for data retrieval.

    Example: "Show me resignations in Q1 by department"
    """
    try:
        result = await nlp_service.interpret_query(
            question=query.question,
            context_type=query.context_type or "general",
        )

        return ChatResponse(
            answer=result["answer"],
            sources=result.get("sources", []),
            suggested_sql=result.get("suggested_sql"),
        )
    except Exception as e:
        logger.error(f"Chat query failed: {e}")
        raise HTTPException(
            status_code=500,
            detail=f"Query processing failed: {str(e)}",
        )


@router.post("/policy", response_model=ChatResponse)
async def chat_policy(query: ChatQuery) -> ChatResponse:
    """Answer HR policy questions using RAG.

    Uses retrieval-augmented generation to find relevant policy
    sections and generate accurate answers with source references.

    Example: "How many sick days do I get per year?"
    """
    try:
        result = await rag_chatbot.query(query.question)

        return ChatResponse(
            answer=result["answer"],
            sources=result.get("sources", []),
            suggested_sql=result.get("suggested_sql"),
        )
    except Exception as e:
        logger.error(f"Policy query failed: {e}")
        raise HTTPException(
            status_code=500,
            detail=f"Policy query failed: {str(e)}",
        )


@router.get("/history", response_model=ChatHistoryResponse)
async def get_chat_history() -> ChatHistoryResponse:
    """Return recent conversation history.

    Returns the in-memory conversation history for the current
    demo session.
    """
    history = rag_chatbot.get_history()

    entries = [
        ChatHistoryEntry(role=entry["role"], content=entry["content"])
        for entry in history
    ]

    return ChatHistoryResponse(
        history=entries,
        total_entries=len(entries),
    )
