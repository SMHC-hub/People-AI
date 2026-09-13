export interface User {
  id: number
  email: string
  name?: string
  full_name?: string
  role: 'admin' | 'hr_manager' | 'hr_analyst' | 'manager' | 'employee'
  organization_id?: number
  is_active?: boolean
  created_at?: string
  employee?: {
    id?: number
    employee_code?: string
    position?: {
      title?: string
    }
    department?: {
      name?: string
    }
  }
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

export interface Location {
  id: number
  name: string
  city: string
  country: string
  timezone: string
}

export interface Organization {
  id: number
  name: string
  domain: string
  plan: string
  settings?: Record<string, any>
}

export interface Employee {
  id: number
  employee_id?: string
  employee_code?: string
  first_name: string
  last_name: string
  email: string
  phone?: string
  department: any
  position: any
  location?: any
  status: 'active' | 'inactive' | 'on_leave' | 'terminated'
  hire_date: string
  manager_id?: number | null
  salary?: number
  location_id?: number
  job_satisfaction?: number
  performance_score?: number
  years_at_company?: number
  years_since_last_promotion?: number
  overtime_hours_avg?: number
  attrition_risk_score?: number
  created_at?: string
  updated_at?: string
}

export interface Attendance {
  id: number
  employee_id: number
  employee_name?: string
  employee?: {
    id: number
    first_name: string
    last_name: string
    full_name?: string
    employee_code?: string
    department?: {
      id: number
      name: string
    }
  }
  date: string
  check_in: string | null
  check_out: string | null
  status: string
  hours_worked: number | null
  is_anomaly: boolean
  anomaly_score?: number
  anomaly_reason?: string | null
}

export interface DailyAttendanceTrend {
  date: string
  attendance_rate: number
  anomaly_count: number
  total: number
}

export interface AttendanceStats {
  period?: {
    from: string
    to: string
  }
  total_records?: number
  total_employees?: number
  present?: number
  late?: number
  absent?: number
  anomalies?: number
  average_hours_worked?: number
  attendance_rate: number
  present_today?: number
  absent_today?: number
  late_today?: number
  anomaly_count?: number
  daily_trend?: DailyAttendanceTrend[]
}

export interface Leave {
  id: number
  employee_id: number
  employee_name?: string
  leave_type?: 'annual' | 'sick' | 'personal' | 'maternity' | 'paternity' | 'unpaid' | string
  type?: string
  start_date: string
  end_date: string
  days: number
  reason: string
  status: 'pending' | 'approved' | 'rejected'
  approved_by?: number | null
  created_at?: string
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

export interface Payroll {
  id: number
  employee_id: number
  employee?: {
    id: number
    employee_id: string
    first_name: string
    last_name: string
    department: string
    position: string
  }
  month?: string
  pay_period?: string
  base_salary?: number
  gross_pay?: number
  net_pay?: number
  net_salary?: number
  overtime_hours?: number
  overtime_pay?: number
  bonus?: number
  deductions: number
  payment_method?: string
  is_anomaly?: boolean
  anomaly_type?: string
  anomaly_score?: number
  anomaly_explanation?: string
  review_status?: 'pending' | 'reviewed' | 'escalated'
  created_at?: string
}

export interface PayrollStats {
  total_payrolls_period: number
  total_payout: number
  avg_net_salary: number
  total_overtime_pay: number
  total_anomalies: number
  pending_reviews: number
}

export interface PerformanceReview {
  id: number
  employee_id: number
  reviewer_id?: number
  employee?: {
    id: number
    first_name: string
    last_name: string
    department: string
    position: string
  }
  reviewer?: {
    id: number
    full_name: string
  }
  review_period: string
  rating: number
  goals_met?: number
  promotion_recommended?: boolean
  notes?: string
  feedback?: string
  review_date?: string
  created_at?: string
}

export type Performance = PerformanceReview

export interface PerformanceStats {
  total_reviews: number
  avg_rating: number
  promotion_recommendation_rate: number
  rating_distribution: Record<string, number>
}

export interface WorkforceStats {
  total_employees: number
  active_employees: number
  on_leave_employees: number
  high_risk_count: number
  medium_risk_count: number
  low_risk_count: number
  overall_health_score: number
  avg_turnover_risk: number
  avg_job_satisfaction: number
  departments?: { name: string; headcount: number; high_risk_count: number }[]
}

export interface DepartmentRisk {
  department: string
  employee_count: number
  high_risk_count: number
  risk_percentage: number
  avg_satisfaction: number
  avg_years_promotion: number
}

export interface WorkforceInsight {
  id: string
  type: 'warning' | 'alert' | 'positive' | 'info'
  title: string
  description: string
  recommendation: string
  impact_level: 'critical' | 'high' | 'medium' | 'low'
  affected_department?: string
}

export interface ModelVersion {
  id: number
  model_name: string
  model_type: string
  version: string
  algorithm: string
  accuracy: number
  f1_score: number
  roc_auc: number
  status: 'active' | 'candidate' | 'archived'
  feature_importance: Record<string, number>
  training_data_summary?: Record<string, any>
  created_at: string
}

export interface AuditLog {
  id: number
  user_id?: number
  user_name?: string
  user_email?: string
  action: string
  entity_type: string
  entity_id?: number
  ip_address: string
  details?: Record<string, any>
  created_at: string
}

export interface Report {
  id: number
  title: string
  type: 'attendance' | 'turnover' | 'leave' | 'performance' | 'department' | 'payroll'
  status: 'generating' | 'ready' | 'failed'
  date_range_start: string
  date_range_end: string
  generated_by: number
  file_url: string | null
  created_at: string
}

export interface Citation {
  document_title: string
  source_type: string
  relevance_score: number
  content_snippet: string
}

export interface ChatMessage {
  id: string
  content: string
  role: 'user' | 'assistant'
  timestamp: string
  is_policy_question?: boolean
  citations?: Citation[]
}

export interface RiskFactor {
  name: string
  impact: number
  description: string
}

export interface RiskScore {
  employee_id: number
  score: number
  level: 'low' | 'medium' | 'high' | 'critical'
  factors: (string | RiskFactor)[]
  shap_values?: Record<string, number>
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

export interface LeaveCategoryBalance {
  total: number
  used: number
  remaining: number
}

export interface LeaveBalances {
  annual: LeaveCategoryBalance
  sick: LeaveCategoryBalance
  personal: LeaveCategoryBalance
  pending_count: number
}

export interface PortalDashboardData {
  employee: Employee
  today_attendance: Attendance | null
  attendance_stats: {
    days_present: number
    total_hours: number
    punctuality_rate: number
  }
  recent_attendance: Attendance[]
  leave_balances: LeaveBalances
  recent_leaves: Leave[]
  latest_payslip: Payroll | null
  latest_performance: PerformanceReview | null
}

