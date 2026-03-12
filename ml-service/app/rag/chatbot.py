"""RAG chatbot for HR policy question answering."""

import logging
from typing import Any, Dict, List, Optional

from app.config import settings
from app.rag.documents import SAMPLE_DOCUMENTS

logger = logging.getLogger(__name__)


class RAGChatbot:
    """RAG-based chatbot for HR policy Q&A.

    Uses ChromaDB for document storage and retrieval, and OpenAI GPT-4o
    for answer generation. Falls back to keyword-based search and mock
    answers when API keys are not configured.
    """

    def __init__(self) -> None:
        self._collection: Optional[Any] = None
        self._openai_client: Optional[Any] = None
        self._documents: List[Dict[str, Any]] = []
        self._conversation_history: List[Dict[str, str]] = []
        self._initialize()

    def _initialize(self) -> None:
        """Initialize ChromaDB collection and OpenAI client."""
        # Initialize ChromaDB
        try:
            import chromadb

            client = chromadb.Client()
            self._collection = client.get_or_create_collection(
                name="hr_policies",
                metadata={"hnsw:space": "cosine"},
            )
            logger.info("ChromaDB collection initialized successfully.")
        except Exception as e:
            logger.warning(f"Failed to initialize ChromaDB: {e}")
            self._collection = None

        # Initialize OpenAI client
        if settings.OPENAI_API_KEY:
            try:
                from openai import OpenAI

                self._openai_client = OpenAI(api_key=settings.OPENAI_API_KEY)
                logger.info("OpenAI client initialized successfully.")
            except Exception as e:
                logger.warning(f"Failed to initialize OpenAI client: {e}")
                self._openai_client = None

        # Load sample documents on startup
        self.ingest_documents(SAMPLE_DOCUMENTS)

    def ingest_documents(self, documents: List[Dict[str, Any]]) -> int:
        """Embed and store documents in ChromaDB.

        Args:
            documents: List of document dicts with id, title, content, metadata.

        Returns:
            Number of documents ingested.
        """
        self._documents = documents
        count = 0

        if self._collection is None:
            logger.warning("ChromaDB not available; documents stored in memory only.")
            return len(documents)

        for doc in documents:
            # Split content into chunks for better retrieval
            chunks = self._chunk_text(doc["content"], chunk_size=500, overlap=50)

            for i, chunk in enumerate(chunks):
                chunk_id = f"{doc['id']}-chunk-{i}"
                metadata = {
                    "source_id": doc["id"],
                    "title": doc["title"],
                    "chunk_index": i,
                    **{k: str(v) for k, v in doc.get("metadata", {}).items()},
                }

                try:
                    self._collection.upsert(
                        ids=[chunk_id],
                        documents=[chunk],
                        metadatas=[metadata],
                    )
                    count += 1
                except Exception as e:
                    logger.warning(f"Failed to ingest chunk {chunk_id}: {e}")

        logger.info(f"Ingested {count} chunks from {len(documents)} documents.")
        return count

    async def query(self, question: str) -> Dict[str, Any]:
        """Answer a question using retrieval-augmented generation.

        Pipeline:
        1. Embed the question
        2. Search ChromaDB for top-k similar chunks
        3. Build prompt with context
        4. Call OpenAI GPT-4o for answer
        5. Return answer + source references

        Args:
            question: The user's question.

        Returns:
            Dict with answer, sources, and suggested_sql.
        """
        # Store question in history
        self._conversation_history.append({"role": "user", "content": question})

        # Step 1-2: Retrieve relevant chunks
        context_chunks, sources = self._retrieve(question)

        # Step 3-4: Generate answer
        if self._openai_client is not None:
            answer = await self._generate_answer(question, context_chunks)
        else:
            answer = self._fallback_answer(question, context_chunks)

        # Store answer in history
        self._conversation_history.append({"role": "assistant", "content": answer})

        return {
            "answer": answer,
            "sources": sources,
            "suggested_sql": None,
        }

    def get_history(self) -> List[Dict[str, str]]:
        """Return the conversation history.

        Returns:
            List of role/content dicts.
        """
        return list(self._conversation_history)

    def _retrieve(self, question: str, top_k: int = 3) -> tuple[List[str], List[str]]:
        """Retrieve relevant document chunks.

        Args:
            question: The user's question.
            top_k: Number of top results to return.

        Returns:
            Tuple of (context_chunks, source_titles).
        """
        if self._collection is not None:
            try:
                results = self._collection.query(
                    query_texts=[question],
                    n_results=top_k,
                )

                chunks = results.get("documents", [[]])[0]
                metadatas = results.get("metadatas", [[]])[0]

                sources = list(set(
                    m.get("title", "Unknown")
                    for m in metadatas
                    if isinstance(m, dict)
                ))

                return chunks, sources
            except Exception as e:
                logger.warning(f"ChromaDB query failed: {e}")

        # Fallback: keyword-based search through in-memory documents
        return self._keyword_search(question)

    def _keyword_search(
        self, question: str, top_k: int = 3
    ) -> tuple[List[str], List[str]]:
        """Fallback keyword-based search through documents."""
        question_lower = question.lower()
        scored: List[tuple[float, str, str]] = []

        for doc in self._documents:
            content = doc["content"]
            title = doc["title"]

            # Simple keyword scoring
            words = question_lower.split()
            score = sum(
                1 for word in words
                if len(word) > 3 and word in content.lower()
            )

            if score > 0:
                scored.append((score, content, title))

        # Sort by score descending
        scored.sort(key=lambda x: x[0], reverse=True)

        chunks = [item[1][:500] for item in scored[:top_k]]
        sources = list(set(item[2] for item in scored[:top_k]))

        if not chunks:
            # Return all documents as context if no keyword match
            chunks = [doc["content"][:500] for doc in self._documents[:top_k]]
            sources = [doc["title"] for doc in self._documents[:top_k]]

        return chunks, sources

    async def _generate_answer(
        self, question: str, context_chunks: List[str]
    ) -> str:
        """Generate an answer using OpenAI GPT-4o.

        Args:
            question: The user's question.
            context_chunks: Retrieved document chunks for context.

        Returns:
            Generated answer string.
        """
        context = "\n\n---\n\n".join(context_chunks)

        system_prompt = (
            "You are an HR policy assistant. Answer questions based on the provided "
            "policy documents. If the answer is not found in the context, say so clearly. "
            "Be concise and cite the relevant policy section when possible."
        )

        user_prompt = (
            f"Context from HR policy documents:\n\n{context}\n\n"
            f"Question: {question}\n\n"
            "Please answer based on the policy documents above."
        )

        try:
            response = self._openai_client.chat.completions.create(
                model="gpt-4o",
                messages=[
                    {"role": "system", "content": system_prompt},
                    {"role": "user", "content": user_prompt},
                ],
                temperature=0.2,
                max_tokens=500,
            )
            return response.choices[0].message.content or "Unable to generate answer."
        except Exception as e:
            logger.error(f"OpenAI API error: {e}")
            return self._fallback_answer(question, context_chunks)

    def _fallback_answer(
        self, question: str, context_chunks: List[str]
    ) -> str:
        """Generate a fallback answer without OpenAI.

        Provides simple keyword-based answers from the policy documents.
        """
        question_lower = question.lower()

        # Try to find relevant information from context
        if "annual leave" in question_lower or "vacation" in question_lower:
            return (
                "According to the Company Leave Policy, all full-time employees are "
                "entitled to 20 days of paid annual leave per calendar year. Annual leave "
                "accrues at 1.67 days per month. Up to 5 unused days can be carried "
                "forward to the next year. Leave requests must be submitted at least "
                "5 business days in advance."
            )

        if "sick leave" in question_lower or "sick day" in question_lower:
            return (
                "Per the Company Leave Policy, employees are entitled to 12 days of "
                "paid sick leave per year. A medical certificate is required for sick "
                "leave exceeding 2 consecutive days. Employees must notify their manager "
                "within 1 hour of their scheduled start time."
            )

        if "parental" in question_lower or "maternity" in question_lower or "paternity" in question_lower:
            return (
                "The Company Leave Policy provides 16 weeks of paid parental leave for "
                "primary caregivers and 4 weeks for secondary caregivers. Parental leave "
                "must be taken within 12 months of the child's birth or adoption, with "
                "at least 4 weeks notice required."
            )

        if "working hours" in question_lower or "work hours" in question_lower:
            return (
                "Standard working hours are 9:00 AM to 5:30 PM, Monday through Friday, "
                "totaling 40 hours per week. A 30-minute lunch break is included. "
                "Flexible working arrangements may be approved by department heads."
            )

        if "remote" in question_lower or "work from home" in question_lower:
            return (
                "Eligible employees may work remotely up to 2 days per week with manager "
                "approval. Remote work days must be agreed upon in advance. Employees "
                "must be available during core hours (10:00 AM - 3:00 PM) while remote."
            )

        if "overtime" in question_lower:
            return (
                "Overtime requires prior approval from the department head. Non-exempt "
                "employees are compensated at 1.5x hourly rate. Overtime exceeding "
                "10 hours per week requires HR department approval. Compensatory time "
                "off may be offered as an alternative."
            )

        if "tardiness" in question_lower or "late" in question_lower:
            return (
                "Arriving more than 15 minutes after scheduled start time is considered "
                "tardy. Three instances in a calendar month trigger a verbal warning. "
                "Habitual tardiness (5+ per month) may affect performance reviews."
            )

        if "disciplin" in question_lower:
            return (
                "The company follows progressive discipline: verbal warning, written "
                "warning, final written warning, then termination. Severe violations "
                "may result in immediate termination. Employees may appeal through HR."
            )

        if "conduct" in question_lower or "behavior" in question_lower:
            return (
                "The Code of Conduct requires professional behavior at all times. "
                "Harassment and discrimination are strictly prohibited. Confidentiality "
                "must be maintained, and conflicts of interest must be disclosed."
            )

        # Generic fallback with context snippet
        if context_chunks:
            snippet = context_chunks[0][:300].strip()
            return (
                f"Based on the HR policy documents, here is relevant information: "
                f"{snippet}... (Note: Running in demo mode without OpenAI API key. "
                f"Full answers require OPENAI_API_KEY to be configured.)"
            )

        return (
            "I can help answer questions about company policies including leave, "
            "attendance, and code of conduct. Please try asking about a specific "
            "policy topic. (Running in demo mode without OPENAI_API_KEY.)"
        )

    @staticmethod
    def _chunk_text(
        text: str, chunk_size: int = 500, overlap: int = 50
    ) -> List[str]:
        """Split text into overlapping chunks.

        Args:
            text: The text to chunk.
            chunk_size: Maximum characters per chunk.
            overlap: Number of overlapping characters between chunks.

        Returns:
            List of text chunks.
        """
        chunks: List[str] = []
        start = 0
        text = text.strip()

        while start < len(text):
            end = start + chunk_size

            # Try to break at a sentence boundary
            if end < len(text):
                # Look for period, newline, or other sentence boundary
                boundary = text.rfind(".", start, end)
                if boundary == -1:
                    boundary = text.rfind("\n", start, end)
                if boundary > start:
                    end = boundary + 1

            chunk = text[start:end].strip()
            if chunk:
                chunks.append(chunk)

            start = end - overlap

        return chunks
