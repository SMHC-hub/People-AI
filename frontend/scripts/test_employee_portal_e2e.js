import puppeteer from 'puppeteer-core'
import fs from 'fs'
import path from 'path'

const CHROME_PATH = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe'
const SCREENSHOT_DIR = 'C:\\Users\\AI\\.gemini\\antigravity-ide\\brain\\f4912954-8e8b-46a4-a3e2-c81d7d0cb6d5\\scratch\\browser_screenshots'

if (!fs.existsSync(SCREENSHOT_DIR)) {
  fs.mkdirSync(SCREENSHOT_DIR, { recursive: true })
}

async function runE2E() {
  console.log('🚀 Launching Chrome via Puppeteer-core...')
  const browser = await puppeteer.launch({
    executablePath: CHROME_PATH,
    headless: 'new',
    defaultViewport: { width: 1440, height: 960 },
    args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-gpu']
  })

  const page = await browser.newPage()

  page.on('console', msg => {
    if (msg.type() === 'error') {
      console.log('🔴 Console Error:', msg.text())
    }
  })

  try {
    // 1. Visit Login Page & Verify Persona Switcher
    console.log('👉 Navigating to http://localhost:3000/login...')
    await page.goto('http://localhost:3000/login', { waitUntil: 'networkidle2' })
    await new Promise(r => setTimeout(r, 600))
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, '01_login_clean_personas.png') })
    console.log('📸 Captured 01_login_clean_personas.png')

    // Verify HR Manager & Analyst are NOT in DOM
    const hrManagerBtn = await page.$('button::-p-text(HR Manager)')
    const hrAnalystBtn = await page.$('button::-p-text(HR Analyst)')
    console.log('🔍 HR Manager button exists:', !!hrManagerBtn)
    console.log('🔍 HR Analyst button exists:', !!hrAnalystBtn)

    // 2. Click Employee Portal button
    console.log('👉 Clicking "Employee Portal" button...')
    const empBtn = await page.$('button::-p-text(Employee Portal)')
    if (empBtn) {
      await empBtn.click()
      await new Promise(r => setTimeout(r, 300))
    }

    console.log('👉 Submitting login form as Employee...')
    const submitBtn = await page.$('button[type="submit"]')
    if (submitBtn) {
      await submitBtn.click()
      await page.waitForNavigation({ waitUntil: 'networkidle2' }).catch(() => {})
      await new Promise(r => setTimeout(r, 1200))
    }

    console.log('👉 Current URL after employee login:', page.url())
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, '02_employee_portal_dashboard.png') })
    console.log('📸 Captured 02_employee_portal_dashboard.png')

    // 3. Test Clock In / Out
    const clockInBtn = await page.$('button::-p-text(Clock In)')
    if (clockInBtn) {
      console.log('👉 Clicking Clock In button...')
      await clockInBtn.click()
      await new Promise(r => setTimeout(r, 1000))
    }

    // 4. Navigate to My Attendance
    console.log('👉 Navigating to http://localhost:3000/portal/attendance...')
    await page.goto('http://localhost:3000/portal/attendance', { waitUntil: 'networkidle2' })
    await new Promise(r => setTimeout(r, 1000))
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, '03_employee_attendance.png') })
    console.log('📸 Captured 03_employee_attendance.png')

    // 5. Navigate to My Leaves
    console.log('👉 Navigating to http://localhost:3000/portal/leaves...')
    await page.goto('http://localhost:3000/portal/leaves', { waitUntil: 'networkidle2' })
    await new Promise(r => setTimeout(r, 1000))
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, '04_employee_leaves.png') })
    console.log('📸 Captured 04_employee_leaves.png')

    // Open Request Leave modal
    console.log('👉 Opening Request Leave modal...')
    const clickedLeave = await page.evaluate(() => {
      const btns = Array.from(document.querySelectorAll('button'))
      const btn = btns.find(b => b.textContent && b.textContent.includes('Request Leave'))
      if (btn) {
        btn.click()
        return true
      }
      return false
    })
    console.log('👉 Clicked Request Leave button:', clickedLeave)
    await new Promise(r => setTimeout(r, 800))
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, '05_employee_leave_modal.png') })
    console.log('📸 Captured 05_employee_leave_modal.png')

    // Close leave modal
    await page.evaluate(() => {
      const btns = Array.from(document.querySelectorAll('button'))
      const cancelBtn = btns.find(b => b.textContent && b.textContent.includes('Cancel'))
      if (cancelBtn) cancelBtn.click()
    })
    await new Promise(r => setTimeout(r, 300))

    // 6. Navigate to My Payslips
    console.log('👉 Navigating to http://localhost:3000/portal/payrolls...')
    await page.goto('http://localhost:3000/portal/payrolls', { waitUntil: 'networkidle2' })
    await new Promise(r => setTimeout(r, 1000))
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, '06_employee_payrolls.png') })
    console.log('📸 Captured 06_employee_payrolls.png')

    // Open Payslip modal
    console.log('👉 Opening Payslip detail modal...')
    const clickedPayslip = await page.evaluate(() => {
      const btns = Array.from(document.querySelectorAll('button'))
      const btn = btns.find(b => b.textContent && b.textContent.includes('View Payslip'))
      if (btn) {
        btn.click()
        return true
      }
      return false
    })
    console.log('👉 Clicked View Payslip button:', clickedPayslip)
    await new Promise(r => setTimeout(r, 800))
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, '07_employee_payslip_modal.png') })
    console.log('📸 Captured 07_employee_payslip_modal.png')

    // Close payslip modal
    await page.evaluate(() => {
      const btns = Array.from(document.querySelectorAll('button'))
      const closeBtn = btns.find(b => b.textContent && b.textContent.includes('Close'))
      if (closeBtn) closeBtn.click()
    })
    await new Promise(r => setTimeout(r, 300))

    // 7. Navigate to My Performance
    console.log('👉 Navigating to http://localhost:3000/portal/performance...')
    await page.goto('http://localhost:3000/portal/performance', { waitUntil: 'networkidle2' })
    await new Promise(r => setTimeout(r, 1000))
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, '08_employee_performance.png') })
    console.log('📸 Captured 08_employee_performance.png')

    // 8. Navigate to My Profile
    console.log('👉 Navigating to http://localhost:3000/portal/profile...')
    await page.goto('http://localhost:3000/portal/profile', { waitUntil: 'networkidle2' })
    await new Promise(r => setTimeout(r, 1000))
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, '09_employee_profile.png') })
    console.log('📸 Captured 09_employee_profile.png')

    // 9. Sign Out and test Admin Login
    console.log('👉 Signing out and clearing session...')
    await page.evaluate(() => {
      localStorage.clear()
    })
    await page.goto('http://localhost:3000/login', { waitUntil: 'networkidle2' })
    await new Promise(r => setTimeout(r, 1000))

    // 10. Login as Admin
    console.log('👉 Logging in as Admin...')
    await page.evaluate(() => {
      const emailInput = document.querySelector('#email')
      const passInput = document.querySelector('#password')
      if (emailInput) {
        emailInput.value = 'admin@hranalytics.com'
        emailInput.dispatchEvent(new Event('input', { bubbles: true }))
      }
      if (passInput) {
        passInput.value = 'password'
        passInput.dispatchEvent(new Event('input', { bubbles: true }))
      }
      const form = document.querySelector('form')
      if (form) {
        form.requestSubmit()
      }
    })

    await page.waitForFunction(() => window.location.pathname === '/' || window.location.pathname === '/dashboard', { timeout: 10000 }).catch(e => console.log('Wait error:', e.message))
    await new Promise(r => setTimeout(r, 1500))

    console.log('👉 Current URL after admin login:', page.url())
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, '10_admin_dashboard_restored.png') })
    console.log('📸 Captured 10_admin_dashboard_restored.png')

  } catch (err) {
    console.error('❌ Error during E2E test:', err)
  } finally {
    await browser.close()
    console.log('🏁 E2E Test completed.')
  }
}

runE2E()
