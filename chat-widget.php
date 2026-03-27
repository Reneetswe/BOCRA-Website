<?php
session_start();

/*
|--------------------------------------------------------------------------
| BOCRA CHAT WIDGET
|--------------------------------------------------------------------------
| Customer support chat widget for BOCRA website
*/

// Check if user is logged in (you can integrate with your existing auth system)
$user = null;
if (isset($_SESSION['user'])) {
    $user = $_SESSION['user'];
} else {
    // Create guest user session for demo
    $_SESSION['user'] = [
        'id' => 'guest_' . uniqid(),
        'email' => 'guest@bocra.org.bw',
        'name' => 'Guest User'
    ];
    $user = $_SESSION['user'];
}

// Handle AJAX requests
if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'send_message':
            $message = $_POST['message'] ?? '';
            $response = [
                'success' => true,
                'message' => generateBotResponse($message),
                'timestamp' => date('H:i')
            ];
            echo json_encode($response);
            exit;
            
        case 'get_history':
            // In real implementation, fetch from database
            $history = [
                ['type' => 'bot', 'message' => 'Welcome to BOCRA Support! How can I help you today?', 'time' => date('H:i')]
            ];
            echo json_encode(['success' => true, 'history' => $history]);
            exit;
    }
}

function generateBotResponse($message) {
    $message = strtolower($message);
    
    // Simple keyword-based responses
    if (strpos($message, 'license') !== false || strpos($message, 'licensing') !== false) {
        return "I can help you with licensing! You can apply for licenses through our Licensing Portal. What type of license are you interested in?";
    } elseif (strpos($message, 'complaint') !== false) {
        return "For complaints, please visit our Complaints Portal or call our hotline at +267 395-7755. What is your complaint about?";
    } elseif (strpos($message, 'contact') !== false || strpos($message, 'phone') !== false) {
        return "You can reach BOCRA at:\n📞 +267 395-7755\n📧 info@bocra.org.bw\n📍 Plot No. 50671, Independence Avenue, Gaborone";
    } elseif (strpos($message, 'hello') !== false || strpos($message, 'hi') !== false) {
        return "Hello! Welcome to BOCRA Support. I'm here to help with licensing, complaints, regulations, and general inquiries. How can I assist you?";
    } elseif (strpos($message, 'cyber') !== false || strpos($message, 'security') !== false) {
        return "For cybersecurity matters, please visit our Cybersecurity Portal or report incidents through our online form. Is this about reporting an incident or general cybersecurity information?";
    } else {
        return "I understand you're asking about: " . ucfirst($message) . ". Let me connect you with the right information. You can also browse our website or call us at +267 395-7755 for immediate assistance.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BOCRA Support Chat</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Forum&family=Lato:wght@300;400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <style>
        :root {
            --teal: #006B5E;
            --teal-dark: #004D43;
            --teal-light: #E8F4F2;
            --gold: #C9A227;
            --charcoal: #2C2C2C;
            --mid: #555555;
            --light: #888888;
            --border: #DDDDDD;
            --bg: #F7F7F5;
            --white: #FFFFFF;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Lato', sans-serif;
            background: var(--bg);
            color: var(--charcoal);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .chat-container {
            width: 100%;
            max-width: 400px;
            height: 600px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            position: relative;
        }

        .chat-header {
            background: var(--teal);
            color: white;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .chat-avatar {
            width: 45px;
            height: 45px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .chat-info h3 {
            font-family: 'Forum', serif;
            font-size: 18px;
            font-weight: 400;
            margin-bottom: 2px;
        }

        .chat-info .status {
            font-size: 12px;
            opacity: 0.9;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            background: #4CAF50;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .chat-actions {
            margin-left: auto;
            display: flex;
            gap: 10px;
        }

        .chat-action-btn {
            background: none;
            border: none;
            color: white;
            font-size: 18px;
            cursor: pointer;
            padding: 5px;
            border-radius: 50%;
            transition: background 0.3s;
        }

        .chat-action-btn:hover {
            background: rgba(255,255,255,0.1);
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background: #FAFAFA;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .message {
            display: flex;
            gap: 10px;
            max-width: 80%;
            animation: messageSlide 0.3s ease;
        }

        @keyframes messageSlide {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .message.user {
            align-self: flex-end;
            flex-direction: row-reverse;
        }

        .message-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            flex-shrink: 0;
        }

        .message.bot .message-avatar {
            background: var(--teal);
            color: white;
        }

        .message.user .message-avatar {
            background: var(--gold);
            color: white;
        }

        .message-content {
            background: white;
            padding: 12px 16px;
            border-radius: 18px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            position: relative;
        }

        .message.user .message-content {
            background: var(--teal);
            color: white;
        }

        .message-time {
            font-size: 11px;
            color: var(--light);
            margin-top: 4px;
        }

        .message.user .message-time {
            color: rgba(255,255,255,0.8);
        }

        .typing-indicator {
            display: none;
            align-items: center;
            gap: 10px;
            padding: 15px 20px;
        }

        .typing-indicator.active {
            display: flex;
        }

        .typing-dots {
            display: flex;
            gap: 4px;
        }

        .typing-dot {
            width: 8px;
            height: 8px;
            background: var(--mid);
            border-radius: 50%;
            animation: typing 1.4s infinite;
        }

        .typing-dot:nth-child(2) { animation-delay: 0.2s; }
        .typing-dot:nth-child(3) { animation-delay: 0.4s; }

        @keyframes typing {
            0%, 60%, 100% { transform: translateY(0); }
            30% { transform: translateY(-10px); }
        }

        .chat-input-container {
            padding: 20px;
            background: white;
            border-top: 1px solid var(--border);
        }

        .chat-input-wrapper {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .chat-input {
            flex: 1;
            border: 1px solid var(--border);
            border-radius: 25px;
            padding: 12px 20px;
            font-family: 'Lato', sans-serif;
            font-size: 14px;
            outline: none;
            transition: border-color 0.3s;
        }

        .chat-input:focus {
            border-color: var(--teal);
        }

        .chat-send-btn {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            border: none;
            background: var(--teal);
            color: white;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s;
        }

        .chat-send-btn:hover {
            background: var(--teal-dark);
        }

        .chat-send-btn:disabled {
            background: var(--light);
            cursor: not-allowed;
        }

        .quick-actions {
            padding: 15px 20px;
            background: var(--teal-light);
            border-top: 1px solid var(--border);
        }

        .quick-actions-title {
            font-size: 12px;
            font-weight: 600;
            color: var(--teal);
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .quick-action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .quick-action-btn {
            background: white;
            border: 1px solid var(--teal);
            color: var(--teal);
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .quick-action-btn:hover {
            background: var(--teal);
            color: white;
        }

        /* Mobile Responsive */
        @media (max-width: 480px) {
            .chat-container {
                width: 100vw;
                height: 100vh;
                border-radius: 0;
            }
        }

        /* Widget Mode (for embedding) */
        .widget-mode .chat-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 350px;
            height: 500px;
            z-index: 10000;
        }

        .widget-toggle {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--teal);
            color: white;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            transition: all 0.3s;
            z-index: 9999;
        }

        .widget-toggle:hover {
            transform: scale(1.1);
            background: var(--teal-dark);
        }

        .widget-toggle.hidden {
            display: none;
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <!-- Chat Header -->
        <div class="chat-header">
            <div class="chat-avatar">
                <i class="fas fa-headset"></i>
            </div>
            <div class="chat-info">
                <h3>BOCRA Support</h3>
                <div class="status">
                    <span class="status-dot"></span>
                    <span>Online - We typically reply instantly</span>
                </div>
            </div>
            <div class="chat-actions">
                <button class="chat-action-btn" onclick="minimizeChat()">
                    <i class="fas fa-minus"></i>
                </button>
                <button class="chat-action-btn" onclick="closeChat()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <!-- Chat Messages -->
        <div class="chat-messages" id="chatMessages">
            <div class="message bot">
                <div class="message-avatar">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="message-content">
                    <div>Welcome to BOCRA Support! 👋</div>
                    <div>I'm here to help you with licensing, complaints, regulations, and general inquiries. How can I assist you today?</div>
                    <div class="message-time">Just now</div>
                </div>
            </div>
        </div>

        <!-- Typing Indicator -->
        <div class="typing-indicator" id="typingIndicator">
            <div class="message-avatar">
                <i class="fas fa-robot"></i>
            </div>
            <div class="message-content">
                <div class="typing-dots">
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <div class="quick-actions-title">Quick Actions</div>
            <div class="quick-action-buttons">
                <button class="quick-action-btn" onclick="sendQuickMessage('Apply for license')">
                    📋 Apply for License
                </button>
                <button class="quick-action-btn" onclick="sendQuickMessage('File a complaint')">
                    📝 File Complaint
                </button>
                <button class="quick-action-btn" onclick="sendQuickMessage('Contact information')">
                    📞 Contact Info
                </button>
                <button class="quick-action-btn" onclick="sendQuickMessage('Cybersecurity report')">
                    🔒 Cyber Report
                </button>
            </div>
        </div>

        <!-- Chat Input -->
        <div class="chat-input-container">
            <div class="chat-input-wrapper">
                <input 
                    type="text" 
                    class="chat-input" 
                    id="chatInput" 
                    placeholder="Type your message..."
                    onkeypress="handleKeyPress(event)"
                >
                <button class="chat-send-btn" id="sendBtn" onclick="sendMessage()">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>

    <script>
        let isTyping = false;

        // Initialize chat
        document.addEventListener('DOMContentLoaded', function() {
            loadChatHistory();
            document.getElementById('chatInput').focus();
        });

        function handleKeyPress(event) {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                sendMessage();
            }
        }

        function sendMessage() {
            const input = document.getElementById('chatInput');
            const message = input.value.trim();
            
            if (!message || isTyping) return;
            
            // Add user message
            addMessage(message, 'user');
            input.value = '';
            
            // Show typing indicator
            showTypingIndicator();
            
            // Send to server
            fetch('chat-widget.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=send_message&message=${encodeURIComponent(message)}`
            })
            .then(response => response.json())
            .then(data => {
                hideTypingIndicator();
                if (data.success) {
                    addMessage(data.message, 'bot', data.timestamp);
                } else {
                    addMessage('Sorry, I encountered an error. Please try again.', 'bot');
                }
            })
            .catch(error => {
                hideTypingIndicator();
                addMessage('Sorry, I\'m having trouble connecting. Please try again later.', 'bot');
            });
        }

        function sendQuickMessage(message) {
            document.getElementById('chatInput').value = message;
            sendMessage();
        }

        function addMessage(text, sender, timestamp = null) {
            const messagesContainer = document.getElementById('chatMessages');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${sender}`;
            
            const time = timestamp || new Date().toLocaleTimeString('en-US', { 
                hour: '2-digit', 
                minute: '2-digit' 
            });
            
            const avatar = sender === 'bot' ? 'fa-robot' : 'fa-user';
            
            messageDiv.innerHTML = `
                <div class="message-avatar">
                    <i class="fas ${avatar}"></i>
                </div>
                <div class="message-content">
                    <div>${text.replace(/\n/g, '<br>')}</div>
                    <div class="message-time">${time}</div>
                </div>
            `;
            
            messagesContainer.appendChild(messageDiv);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        function showTypingIndicator() {
            isTyping = true;
            document.getElementById('typingIndicator').classList.add('active');
            document.getElementById('sendBtn').disabled = true;
            
            const messagesContainer = document.getElementById('chatMessages');
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        function hideTypingIndicator() {
            isTyping = false;
            document.getElementById('typingIndicator').classList.remove('active');
            document.getElementById('sendBtn').disabled = false;
        }

        function loadChatHistory() {
            // In real implementation, load from server
            fetch('chat-widget.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=get_history'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.history) {
                    data.history.forEach(msg => {
                        addMessage(msg.message, msg.type, msg.time);
                    });
                }
            })
            .catch(error => {
                console.log('Could not load chat history');
            });
        }

        function minimizeChat() {
            // In widget mode, this would minimize the chat
            alert('Chat minimized - implement widget mode for full functionality');
        }

        function closeChat() {
            if (confirm('Are you sure you want to close this chat?')) {
                window.close();
                // In widget mode, this would hide the widget
            }
        }
    </script>
</body>
</html>
