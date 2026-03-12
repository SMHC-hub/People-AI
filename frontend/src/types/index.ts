export interface User {
  id: number
  email: string
  full_name: string
  role: 'admin' | 'manager' | 'employee'
  is_active: boolean
  created_at: string
}

export interface Department {
  id: number
  name: string
  manager_id: number | null
  description: string
}

export interface Position {
  id: number
  title: string
  department_id: number
  level: string
}

export interface Employee {
  id: number
  employee_id: string
  first_name: string
  last_name: string
  email: string
  phone: string
  department: string
  position: string
  status: 'active' | 'inactive' | 'on_leave' | 'terminated'
  hire_date: string
  manager_id: number | null
  salary: number
  created_at: string
  updated_at: string
}

export interface Attendance {
  id: number
  employee_id: number
  employee_name: string
  date: string
  check_in: string | null
  check_out: string | null
  status: 'present' | 'absent' | 'late' | 'half_day'
  hours_worked: number | null
  is_anomaly: boolean
  anomaly_reason: string | null
}

export interface AttendanceStats {
  total_employees: number
  present_today: number
  absent_today: number
  late_today: number
  attendance_rate: number
  anomaly_count: number
}

export interface Leave {
  id: number
  employee_id: number
  employee_name: string
  leave_type: 'annual' | 'sick' | 'personal' | 'maternity' | 'paternity' | 'unpaid'
  start_date: string
  end_date: string
  days: number
  reason: string
  status: 'pending' | 'approved' | 'rejected'
  approved_by: number | null
  created_at: string
}

export interface LeaveBalance {
  annual: number
  sick: number
  personal: number
  total_used: number
  total_remaining: number
}

export interface LeavePrediction {
  employee_id: number
  employee_name: string
  predicted_leave_date: string
  confidence: number
  reason: string
}

export interface Report {
  id: number
  title: string
  type: 'attendance' | 'turnover' | 'leave' | 'performance' | 'department'
  status: 'generating' | 'ready' | 'failed'
  date_range_start: string
  date_range_end: string
  generated_by: number
  file_url: string | null
  created_at: string
}

export interface ChatMessage {
  id: string
  content: string
  role: 'user' | 'assistant'
  timestamp: string
  is_policy_question: boolean
}

export interface RiskScore {
  employee_id: number
  score: number
  level: 'low' | 'medium' | 'high' | 'critical'
  factors: string[]
  last_updated: string
}

export interface PaginatedResponse<T> {
  items: T[]
  total: number
  page: number
  page_size: number
  total_pages: number
}

export interface LoginRequest {
  email: string
  password: string
}

export interface LoginResponse {
  access_token: string
  token_type: string
  user: User
}
