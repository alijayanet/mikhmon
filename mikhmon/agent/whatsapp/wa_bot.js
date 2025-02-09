require('dotenv').config()
const { default: makeWASocket, DisconnectReason, useMultiFileAuthState } = require('@whiskeysockets/baileys')
const express = require('express')
const app = express()
const port = process.env.WA_PORT || 2000

async function connectToWhatsApp() {
    const { state, saveCreds } = await useMultiFileAuthState('auth_info_baileys')
    
    const sock = makeWASocket({
        auth: state,
        printQRInTerminal: true
    })
    
    sock.ev.on('connection.update', (update) => {
        const { connection, lastDisconnect } = update
        if(connection === 'close') {
            const shouldReconnect = lastDisconnect?.error?.output?.statusCode !== DisconnectReason.loggedOut
            if(shouldReconnect) {
                connectToWhatsApp()
            }
        } else if(connection === 'open') {
            console.log('Terhubung ke WhatsApp!')
        }
    })

    // Handle pesan masuk
    sock.ev.on('messages.upsert', async ({ messages }) => {
        const m = messages[0]
        if (!m.message) return
        
        const messageText = m.message.conversation || m.message.extendedTextMessage?.text || ''
        
        if (messageText.startsWith('!voucher')) {
            // Kirim request ke PHP endpoint untuk generate voucher
            const response = await fetch('http://localhost/mikhmon/whatsapp/generate_voucher.php', {
                method: 'POST',
                body: JSON.stringify({
                    sender: m.key.remoteJid,
                    message: messageText
                })
            })
            
            const voucherData = await response.json()
            
            // Kirim balik ke user
            await sock.sendMessage(m.key.remoteJid, { text: voucherData.message })
        }
    })

    sock.ev.on('creds.update', saveCreds)
}

// Express endpoint untuk PHP
app.post('/send-message', express.json(), async (req, res) => {
    const { to, message } = req.body
    try {
        await sock.sendMessage(to, { text: message })
        res.json({ success: true })
    } catch (err) {
        res.json({ success: false, error: err.message })
    }
})

// Tambahkan endpoint status
app.get('/status', (req, res) => {
    res.json({
        connected: sock?.user?.id ? true : false,
        phone: sock?.user?.id || null
    })
})

app.listen(port, () => {
    console.log(`Server berjalan di port ${port}`)
})

connectToWhatsApp()

// Tambahkan error handling
process.on('uncaughtException', err => {
    console.error('Uncaught Exception:', err)
}) 