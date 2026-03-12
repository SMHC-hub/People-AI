import client from './client'
import type { LoginRequest, LoginResponse, User } from '@/types'

export async function login(credentials: LoginRequest): Promise<LoginResponse> {
  const { data } = await client.post<LoginResponse>('/auth/login', credentials)
  return data
}

export async function register(payload: {
  email: string
  password: string
  full_name: string
}): Promise<User> {
  const { data } = await client.post<User>('/auth/register', payload)
  return data
}

export async function logout(): Promise<void> {
  await client.post('/auth/logout')
}

export async function getMe(): Promise<User> {
  const { data } = await client.get<User>('/auth/me')
  return data
}
