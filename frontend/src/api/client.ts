import axios from 'axios'

const client = axios.create({
  baseURL: '/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
})

client.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

client.interceptors.response.use(
  (response) => {
    const resData = response.data
    // If wrapped in Laravel's { success: true, data: ... }
    if (resData && typeof resData === 'object' && 'success' in resData && 'data' in resData) {
      const payload = resData.data

      // Auth response normalization: { user, token } -> { user, access_token, token_type }
      if (payload && typeof payload === 'object' && payload.token && payload.user) {
        response.data = {
          access_token: payload.token,
          token_type: 'Bearer',
          user: payload.user,
        }
        return response
      }

      // Laravel Pagination normalization: { data: [...], current_page, total, per_page, last_page }
      if (payload && typeof payload === 'object' && Array.isArray(payload.data) && 'current_page' in payload) {
        response.data = {
          items: payload.data,
          total: payload.total,
          page: payload.current_page,
          page_size: payload.per_page,
          total_pages: payload.last_page,
        }
        return response
      }

      // Unwrap standard payload
      if (payload !== undefined && payload !== null) {
        response.data = payload
      }
    }
    return response
  },
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('auth_token')
      if (window.location.pathname !== '/login') {
        window.location.href = '/login'
      }
    }
    return Promise.reject(error)
  }
)

export default client
