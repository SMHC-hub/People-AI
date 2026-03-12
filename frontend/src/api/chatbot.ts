import client from './client'
import type { ChatMessage } from '@/types'

export async function sendQuery(message: string): Promise<ChatMessage> {
  const { data } = await client.post<ChatMessage>('/chatbot/query', { message })
  return data
}

export async function askPolicy(question: string): Promise<ChatMessage> {
  const { data } = await client.post<ChatMessage>('/chatbot/policy', { question })
  return data
}

export async function getHistory(): Promise<ChatMessage[]> {
  const { data } = await client.get<ChatMessage[]>('/chatbot/history')
  return data
}
