import client from './client'
import type { ChatMessage } from '@/types'

export async function sendQuery(message: string): Promise<ChatMessage> {
  const { data } = await client.post('/chatbot/query', { question: message, message })
  const content = data?.answer || data?.content || (typeof data === 'string' ? data : JSON.stringify(data))
  return {
    id: `bot-${Date.now()}`,
    content,
    role: 'assistant',
    timestamp: new Date().toISOString(),
    is_policy_question: false,
    citations: data?.citations || [],
  }
}

export async function askPolicy(question: string): Promise<ChatMessage> {
  const { data } = await client.post('/chatbot/policy', { question })
  const content = data?.answer || data?.content || (typeof data === 'string' ? data : JSON.stringify(data))
  return {
    id: `bot-${Date.now()}`,
    content,
    role: 'assistant',
    timestamp: new Date().toISOString(),
    is_policy_question: true,
    citations: data?.citations || [],
  }
}

export async function getHistory(): Promise<ChatMessage[]> {
  const { data } = await client.get('/chatbot/history')
  if (Array.isArray(data)) {
    const list: ChatMessage[] = []
    data.forEach((item: any, idx: number) => {
      if (item.question) {
        list.push({
          id: `hist-q-${idx}`,
          content: item.question,
          role: 'user',
          timestamp: item.timestamp || new Date().toISOString(),
          is_policy_question: false,
        })
      }
      const ans = item.response?.answer || item.response?.content || item.answer || item.content
      if (ans) {
        list.push({
          id: `hist-a-${idx}`,
          content: ans,
          role: 'assistant',
          timestamp: item.timestamp || new Date().toISOString(),
          is_policy_question: false,
        })
      }
    })
    return list
  }
  return []
}
