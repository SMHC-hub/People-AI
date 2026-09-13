/**
 * Reusable formatting utilities for PeopleAI HR SaaS.
 * Enforces human-readable business presentation and prevents raw API / DB leaks.
 */

const MONTHS_SHORT = [
  'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
  'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec',
]

/**
 * Formats an ISO string, date string, or Date into "11 Sep 2026".
 * Handles pure dates ("2026-09-11") and full ISO timestamps safely without timezone shifts.
 */
export function formatDate(value: string | Date | null | undefined): string {
  if (!value) return '-'

  try {
    if (typeof value === 'string') {
      const trimmed = value.trim()
      // Match YYYY-MM-DD pattern directly to avoid UTC timezone day-shift bugs
      const match = trimmed.match(/^(\d{4})-(\d{2})-(\d{2})/)
      if (match) {
        const year = match[1]
        const monthIndex = parseInt(match[2], 10) - 1
        const day = parseInt(match[3], 10)
        const month = MONTHS_SHORT[monthIndex] || match[2]
        return `${day} ${month} ${year}`
      }
    }

    const d = typeof value === 'string' ? new Date(value) : value
    if (isNaN(d.getTime())) return '-'

    const day = d.getDate()
    const month = MONTHS_SHORT[d.getMonth()]
    const year = d.getFullYear()
    return `${day} ${month} ${year}`
  } catch {
    return '-'
  }
}

/**
 * Formats a timestamp into "11 Sep 2026, 08:53".
 */
export function formatDateTime(value: string | Date | null | undefined): string {
  if (!value) return '-'
  const datePart = formatDate(value)
  const timePart = formatTime(value)
  if (timePart === '--:--') return datePart
  return `${datePart}, ${timePart}`
}

/**
 * Formats a timestamp into simple 24-hour time "08:53".
 * Strips seconds, milliseconds, and timezone offsets.
 */
export function formatTime(value: string | Date | null | undefined): string {
  if (!value) return '--:--'

  try {
    if (typeof value === 'string') {
      const trimmed = value.trim()
      // If time part exists (e.g., "2026-09-11T08:53:20.000000Z" or "08:53:00")
      const timeMatch = trimmed.match(/(?:T|\s)?(\d{2}):(\d{2})(?::\d{2})?/)
      if (timeMatch) {
        return `${timeMatch[1]}:${timeMatch[2]}`
      }
    }

    const d = typeof value === 'string' ? new Date(value) : value
    if (isNaN(d.getTime())) return '--:--'

    const hours = String(d.getHours()).padStart(2, '0')
    const minutes = String(d.getMinutes()).padStart(2, '0')
    return `${hours}:${minutes}`
  } catch {
    return '--:--'
  }
}

/**
 * Formats hours into human-readable duration like "8h 35m" or "7h 00m".
 */
export function formatDuration(
  hoursWorked: number | string | null | undefined,
  checkIn?: string | null,
  checkOut?: string | null
): string {
  if (hoursWorked !== null && hoursWorked !== undefined && hoursWorked !== '') {
    const num = typeof hoursWorked === 'string' ? parseFloat(hoursWorked) : hoursWorked
    if (!isNaN(num) && num > 0) {
      const h = Math.floor(num)
      const m = Math.round((num - h) * 60)
      return `${h}h ${m < 10 ? '0' : ''}${m}m`
    }
  }

  if (checkIn && checkOut) {
    try {
      const start = new Date(checkIn).getTime()
      const end = new Date(checkOut).getTime()
      if (!isNaN(start) && !isNaN(end) && end > start) {
        const diffMinutes = Math.floor((end - start) / 60000)
        const h = Math.floor(diffMinutes / 60)
        const m = diffMinutes % 60
        return `${h}h ${m < 10 ? '0' : ''}${m}m`
      }
    } catch {
      // ignore
    }
  }

  return '-'
}

/**
 * Formats status strings into clean HR terminology: "Present", "Late", "Early Departure", "Absent", "Leave"
 */
export function formatStatus(status: string | null | undefined): string {
  if (!status) return 'Present'
  const normalized = status.toLowerCase().trim()

  switch (normalized) {
    case 'present':
      return 'Present'
    case 'late':
      return 'Late'
    case 'absent':
      return 'Absent'
    case 'early_departure':
    case 'early departure':
      return 'Early Departure'
    case 'half_day':
    case 'half day':
      return 'Half Day'
    case 'leave':
    case 'on_leave':
    case 'annual_leave':
    case 'sick_leave':
      return 'Leave'
    default:
      return normalized.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase())
  }
}

/**
 * Humanizes anomaly reasons so HR users see clean plain English instead of model features/scores.
 */
export function humanizeAnomalyReason(
  reason?: string | null,
  status?: string | null,
  hoursWorked?: number | string | null
): string {
  if (reason && reason.trim()) {
    const r = reason.toLowerCase()
    if (r.includes('early') || r.includes('departure')) return 'Early departure detected'
    if (r.includes('late') || r.includes('check_in') || r.includes('arrival')) return 'Unusual arrival time detected'
    if (r.includes('duration') || r.includes('hours')) return 'Irregular working hours detected'
    if (r.includes('absent') || r.includes('missing')) return 'Unscheduled absence detected'
    if (r.includes('weekend') || r.includes('holiday')) return 'Off-cycle activity detected'
    // If reason is already human-readable and doesn't look like code/numbers
    if (!r.includes('_') && !r.includes('=') && !r.includes('<') && !r.includes('>')) {
      return reason
    }
  }

  const s = (status || '').toLowerCase()
  if (s.includes('early')) return 'Early departure detected'
  if (s.includes('late')) return 'Late check-in outside standard buffer'

  const numHours = typeof hoursWorked === 'string' ? parseFloat(hoursWorked) : hoursWorked
  if (numHours && numHours < 5) return 'Working duration significantly below schedule'

  return 'Unusual attendance pattern detected'
}

/**
 * Formats a Date object or string into YYYY-MM-DD for native HTML date inputs.
 */
export function toInputDateFormat(d: Date | string): string {
  const date = typeof d === 'string' ? new Date(d) : d
  if (isNaN(date.getTime())) return ''
  const y = date.getFullYear()
  const m = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  return `${y}-${m}-${day}`
}
