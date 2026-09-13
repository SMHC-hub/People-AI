import puppeteer from 'puppeteer-core'
import fs from 'fs'
import path from 'path'

const CHROME_PATH = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe'
const SCREENSHOT_DIR = 'C:\\Users\\AI\\.gemini\\antigravity-ide\\brain\\f4912954-8e8b-46a4-a3e2-c81d7d0cb6d5\\scratch\\browser_screenshots'

if (!fs.existsSync(SCREENSHOT_DIR)) {
  fs.mkdirSync(SCREENSHOT_DIR, { recursive: true })
}

async function runAudit() {
  console.log('🚀 Launching Chrome via Puppeteer-core...')
  const browser = await puppeteer.launch({
    executablePath: CHROME_PATH,
    headless: 'new',
    defaultViewport: { width: 1440, height: 900 },
    args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-gpu']
  })

  const page = await browser.newPage()
  const consoleErrors = []

  page.on('console', msg => {
    if (msg.type() === 'error') {
      consoleErrors.push(msg.text())
      console.log('🔴 Browser Console Error:', msg.text())
    }
  })

  try {
    // 1. Navigate to Login page
    console.log('👉 Navigating to http://localhost:3000/login...')
    await page.goto('http://localhost:3000/login', { waitUntil: 'networkidle2' })
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, '01_login.png') })
    console.log('📸 Captured 01_login.png')

    // Click demo admin button to log in quickly
    const adminBtn = await page.$('button::-p-text(Admin (Full Access))')
    if (adminBtn) {
      console.log('👉 Clicking Admin (Full Access)...')
      await adminBtn.click()
      await new Promise(r => setTimeout(r, 300))
    }

    const submitBtn = await page.$('button[type="submit"]')
    if (submitBtn) {
      console.log('👉 Submitting login form...')
      await submitBtn.click()
      await page.waitForNavigation({ waitUntil: 'networkidle2' }).catch(() => {})
      await new Promise(r => setTimeout(r, 1000))
    }

    // 2. Dashboard
    console.log('👉 Checking Dashboard / ...')
    await page.goto('http://localhost:3000/', { waitUntil: 'networkidle2' })
    await new Promise(r => setTimeout(r, 800))
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, '02_dashboard.png') })
    console.log('📸 Captured 02_dashboard.png')

    // 3. Workforce Risk
    console.log('👉 Checking Workforce Risk /workforce ...')
    await page.goto('http://localhost:3000/workforce', { waitUntil: 'networkidle2' })
    await new Promise(r => setTimeout(r, 800))
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, '03_workforce.png') })
    console.log('📸 Captured 03_workforce.png')

    // 4. Employees List
    console.log('👉 Checking Employees /employees ...')
    await page.goto('http://localhost:3000/employees', { waitUntil: 'networkidle2' })
    await new Promise(r => setTimeout(r, 800))
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, '04_employees.png') })
    console.log('📸 Captured 04_employees.png')

    // 5. Attendance Page
    console.log('👉 Checking Attendance /attendance ...')
    await page.goto('http://localhost:3000/attendance', { waitUntil: 'networkidle2' })
    await new Promise(r => setTimeout(r, 1000))
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, '05_attendance.png') })
    console.log('📸 Captured 05_attendance.png')

    // Test clicking Anomalies only in Attendance
    const anomalyCheckbox = await page.$('input[type="checkbox"]')
    if (anomalyCheckbox) {
      console.log('👉 Toggling Anomalies Only checkbox...')
      await anomalyCheckbox.click()
      await new Promise(r => setTimeout(r, 600))
      await page.screenshot({ path: path.join(SCREENSHOT_DIR, '05_attendance_anomalies.png') })
      console.log('📸 Captured 05_attendance_anomalies.png')
    }

    // Click an attendance table row to open detail modal
    const firstRow = await page.$('tbody tr')
    if (firstRow) {
      console.log('👉 Clicking attendance table row to open modal...')
      await firstRow.click()
      await new Promise(r => setTimeout(r, 500))
      await page.screenshot({ path: path.join(SCREENSHOT_DIR, '05_attendance_modal.png') })
      console.log('📸 Captured 05_attendance_modal.png')
      // Close modal
      const doneBtn = await page.$('button::-p-text(Done)')
      if (doneBtn) await doneBtn.click()
      await new Promise(r => setTimeout(r, 300))
    }

    // 6. Leaves Page
    console.log('👉 Checking Leaves /leaves ...')
    await page.goto('http://localhost:3000/leaves', { waitUntil: 'networkidle2' })
    await new Promise(r => setTimeout(r, 800))
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, '06_leaves.png') })
    console.log('📸 Captured 06_leaves.png')

    // Click "+ Request Leave" button
    const reqLeaveBtn = await page.$('button::-p-text(Request Leave)')
    if (reqLeaveBtn) {
      console.log('👉 Clicking Request Leave button...')
      await reqLeaveBtn.click()
      await new Promise(r => setTimeout(r, 500))
      await page.screenshot({ path: path.join(SCREENSHOT_DIR, '06_leaves_modal.png') })
      console.log('📸 Captured 06_leaves_modal.png (inspecting button colors!)')
      
      // Click Cancel button
      const cancelBtn = await page.$('button::-p-text(Cancel)')
      if (cancelBtn) {
        console.log('👉 Clicking Cancel button in modal...')
        await cancelBtn.click()
        await new Promise(r => setTimeout(r, 300))
      }
    }

    // 7. Payrolls Page
    console.log('👉 Checking Payroll /payrolls ...')
    await page.goto('http://localhost:3000/payrolls', { waitUntil: 'networkidle2' })
    await new Promise(r => setTimeout(r, 800))
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, '07_payrolls.png') })
    console.log('📸 Captured 07_payrolls.png')

    // 8. Performance Page
    console.log('👉 Checking Performance /performances ...')
    await page.goto('http://localhost:3000/performances', { waitUntil: 'networkidle2' })
    await new Promise(r => setTimeout(r, 800))
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, '08_performance.png') })
    console.log('📸 Captured 08_performance.png')

    // 9. AI Assistant Chatbot Page
    console.log('👉 Checking AI Assistant /chatbot ...')
    await page.goto('http://localhost:3000/chatbot', { waitUntil: 'networkidle2' })
    await new Promise(r => setTimeout(r, 800))
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, '09_chatbot.png') })
    console.log('📸 Captured 09_chatbot.png')

    // 10. Reports Page
    console.log('👉 Checking Reports /reports ...')
    await page.goto('http://localhost:3000/reports', { waitUntil: 'networkidle2' })
    await new Promise(r => setTimeout(r, 800))
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, '10_reports.png') })
    console.log('📸 Captured 10_reports.png')

    // 11. Models / MLOps Page
    console.log('👉 Checking Models /mlops ...')
    await page.goto('http://localhost:3000/mlops', { waitUntil: 'networkidle2' })
    await new Promise(r => setTimeout(r, 800))
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, '11_mlops.png') })
    console.log('📸 Captured 11_mlops.png')

    // 12. Settings Page
    console.log('👉 Checking Settings /settings ...')
    await page.goto('http://localhost:3000/settings', { waitUntil: 'networkidle2' })
    await new Promise(r => setTimeout(r, 800))
    await page.screenshot({ path: path.join(SCREENSHOT_DIR, '12_settings.png') })
    console.log('📸 Captured 12_settings.png')

    console.log('🎉 Full Browser Suite Completed Successfully!')
    console.log('Total console errors observed:', consoleErrors.length)
  } catch (err) {
    console.error('❌ Error during browser audit:', err)
  } finally {
    await browser.close()
  }
}

runAudit()
