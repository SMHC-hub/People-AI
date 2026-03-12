"""FastAPI application for AI-Powered HR Analytics ML Service."""

import logging
from contextlib import asynccontextmanager
from typing import AsyncGenerator

from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware

from app.config import settings
from app.routers import analyze, chat, predict
from app.schemas import HealthResponse

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s - %(name)s - %(levelname)s - %(message)s",
)
logger = logging.getLogger(__name__)

# Track model initialization state
models_loaded = False


@asynccontextmanager
async def lifespan(app: FastAPI) -> AsyncGenerator[None, None]:
    """Application lifespan handler for startup and shutdown events."""
    global models_loaded

    logger.info("Starting HR Analytics ML Service...")

    # Initialize models on startup
    try:
        # The models are initialized when the routers are imported,
        # since services instantiate models at module level.
        # This startup event verifies they loaded correctly.
        from app.routers.predict import turnover_service
        from app.routers.analyze import anomaly_service
        from app.routers.chat import rag_chatbot

        logger.info("Turnover model initialized: %s", turnover_service.model.is_fitted)
        logger.info("Anomaly model initialized: %s", anomaly_service.model.is_fitted)
        logger.info("RAG chatbot initialized successfully")

        models_loaded = True
        logger.info("All models loaded successfully.")
    except Exception as e:
        logger.error(f"Failed to initialize models: {e}")
        models_loaded = False

    yield

    # Shutdown
    logger.info("Shutting down HR Analytics ML Service...")


# Create FastAPI app
app = FastAPI(
    title="AI HR Analytics - ML Service",
    description=(
        "Machine learning service for the AI-Powered HR Analytics Platform. "
        "Provides turnover prediction, anomaly detection, NLP query processing, "
        "and RAG-based policy Q&A."
    ),
    version="1.0.0",
    lifespan=lifespan,
)

# Configure CORS
app.add_middleware(
    CORSMiddleware,
    allow_origins=settings.CORS_ORIGINS,
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Include routers
app.include_router(predict.router)
app.include_router(analyze.router)
app.include_router(chat.router)


@app.get("/health", response_model=HealthResponse, tags=["health"])
async def health_check() -> HealthResponse:
    """Health check endpoint.

    Returns the service status, version, and whether ML models
    are loaded and ready.
    """
    return HealthResponse(
        status="healthy",
        version="1.0.0",
        models_loaded=models_loaded,
    )
