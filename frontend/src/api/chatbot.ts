import client from './client'
import type { ChatMessage } from '@/types'

export async function sendQuery(message: string): Promise<ChatMessage> {
  const { data } = await client.post('/chatbot/query', { question: message, message })
  return {
    id: `bot-${Date.now()}`,
    content: data?.answer || (typeof data === 'string' ? data : JSON.stringify(data)),
    role: 'assistant',
    timestamp: new Date().toISOString(),
    is_policy_question: false,
  }
}

export async function askPolicy(question: string): Promise<ChatMessage> {
  const { data } = await client.post('/chatbot/policy', { question })
  return {
    id: `bot-${Date.now()}`,
    content: data?.answer || (typeof data === 'string' ? data : JSON.stringify(data)),
    role: 'assistant',
    timestamp: new Date().toISOString(),
    is_policy_question: true,
  }
}

export async function getHistory(): Promise<ChatMessage[]> {
  const { data } = await client.get('/chatbot/history')
  if (Array.isArray(data)) {
    return data.map((item: any, idx: number) => ({
      id: `hist-${idx}`,
      content: item.content || item.response?.answer || item.question || '',
      role: item.role || 'assistant',
      timestamp: item.timestamp || new Date().toISOString(),
      is_policy_question: false,
    }))
  }
  return []
}
