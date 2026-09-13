import puppeteer from 'puppeteer-core'
import fs from 'fs'
import path from 'path'

const CHROME_PATH = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe'
const SCREENSHOT_DIR = 'C:\\Users\\AI\\.gemini\\antigravity-ide\\brain\\f4912954-8e8b-46a4-a3e2-c81d7d0cb6d5\\scratch\\browser_screenshots'

if (!fs.existsSync(SCREENSHOT_DIR)) {
  fs.mkdirSync(SCREENSHOT_DIR, { recursive: true })
}

async function testChatbot() {
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
    // 1. Login
    console.log('👉 Navigating to http://localhost:3000/login...')
    await page.goto('http://localhost:3000/login', { waitUntil: 'networkidle2' })
    const adminBtn = await page.$('button::-p-text(Admin (Full Access))')
    if (adminBtn) {
      await adminBtn.click()
      await new Promise(r => setTimeout(r, 200))
    }
    const submitBtn = await page.$('button[type="submit"]')
    if (submitBtn) {
      await submitBtn.click()
      await page.waitForNavigation({ waitUntil: 'networkidle2' }).catch(() => {})
      await new Promise(r => setTimeout(r, 1000))
    }

    // 2. Go to /chatbot
    console.log('👉 Navigating to http://localhost:3000/chatbot...')
    await page.goto('http://localhost:3000/chatbot', { waitUntil: 'networkidle2' })
    await new Promise(r => setTimeout(r, 1200))

    // Clear chat if messages exist
    const clearBtn = await page.$('button::-p-text(Clear)')
    if (clearBtn) {
      console.log('👉 Clearing chat history...')
      await clearBtn.click()
      await new Promise(r => setTimeout(r, 500))
    }

    // 3. Ask Attrition Risk Question
    console.log('👉 Asking: "Which teams have the highest attrition risk?"...')
    let input = await page.$('input[placeholder*="Ask about"]')
    if (input) {
      await input.type('Which teams have the highest attrition risk?')
      await new Promise(r => setTimeout(r, 200))
      await page.keyboard.press('Enter')
    }

    console.log('⏳ Waiting for AI response...')
    await page.waitForSelector('.chat-markdown', { timeout: 35000 })
    await new Promise(r => setTimeout(r, 2000))

    const screenshot1 = path.join(SCREENSHOT_DIR, '09_chatbot_attrition_response.png')
    await page.screenshot({ path: screenshot1 })
    console.log('📸 Captured screenshot to', screenshot1)

    // Check message content in DOM
    const attritionText = await page.evaluate(() => {
      const els = document.querySelectorAll('.chat-markdown')
      return els.length > 0 ? els[els.length - 1].innerText : 'NOT FOUND'
    })
    console.log('\n📄 AI Response (Attrition Risk):\n', attritionText)

    // 4. Ask Policy Question
    console.log('\n👉 Asking: "What is the policy for annual leave?"...')
    input = await page.$('input[placeholder*="Ask about"]')
    if (input) {
      await input.type('What is the policy for annual leave?')
      await new Promise(r => setTimeout(r, 200))
      await page.keyboard.press('Enter')
    }

    console.log('⏳ Waiting for policy response...')
    // Wait until there are at least 2 chat-markdown blocks
    await page.waitForFunction(() => document.querySelectorAll('.chat-markdown').length >= 2, { timeout: 35000 })
    await new Promise(r => setTimeout(r, 2000))

    const screenshot2 = path.join(SCREENSHOT_DIR, '09_chatbot_policy_response.png')
    await page.screenshot({ path: screenshot2 })
    console.log('📸 Captured screenshot to', screenshot2)

    const policyText = await page.evaluate(() => {
      const els = document.querySelectorAll('.chat-markdown')
      return els.length > 0 ? els[els.length - 1].innerText : 'NOT FOUND'
    })
    console.log('\n📄 AI Response (Policy):\n', policyText)

  } catch (err) {
    console.error('❌ Error during chatbot test:', err)
  } finally {
    await browser.close()
    console.log('🏁 Done.')
  }
}

testChatbot()
