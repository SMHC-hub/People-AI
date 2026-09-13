"""Application configuration using pydantic-settings."""

from typing import List

from pydantic_settings import BaseSettings


class Settings(BaseSettings):
    """Application settings loaded from environment variables."""

    GROQ_API_KEY: str = ""
    OPENAI_API_KEY: str = ""
    LLM_MODEL: str = "openai/gpt-oss-120b"
    GROQ_BASE_URL: str = "https://api.groq.com/openai/v1"
    CHROMA_PERSIST_DIR: str = "./chroma_data"
    MODEL_DIR: str = "./models"
    DATABASE_URL: str = "postgresql://postgres:postgres@localhost:5432/hr_analytics"
    CORS_ORIGINS: List[str] = [
        "http://localhost:3000",
        "http://localhost:5173",
        "http://localhost:8000",
    ]

    model_config = {
        "env_file": ".env",
        "env_file_encoding": "utf-8",
        "case_sensitive": True,
    }


settings = Settings()
