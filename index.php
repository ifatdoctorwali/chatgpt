<?php
session_start();
if (!isset($_SESSION['messages'])) {
    $_SESSION['messages'] = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern ChatGPT</title>
    <style>
        :root {
            --sidebar-width: 260px;
            --primary-color: #10a37f;
            --background-dark: #343541;
            --sidebar-dark: #202123;
            --message-dark: #444654;
            --border-color: #4d4d4f;
            --text-color: #ECECF1;
            --hover-color: #2A2B32;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: var(--background-dark);
            color: var(--text-color);
            line-height: 1.5;
            height: 100vh;
            overflow: hidden;
        }

        /* Container Layout */
        .container {
            display: flex;
            height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: var(--sidebar-width);
            background-color: var(--sidebar-dark);
            padding: 0.8rem;
            display: flex;
            flex-direction: column;
            gap: 0.8rem;
            border-right: 1px solid var(--border-color);
            transition: transform 0.3s ease;
        }

        .new-chat-btn {
            background-color: transparent;
            color: var(--text-color);
            border: 1px solid var(--border-color);
            border-radius: 0.375rem;
            padding: 0.75rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.2s ease;
            font-size: 0.875rem;
            width: 100%;
        }

        .new-chat-btn:hover {
            background-color: var(--hover-color);
        }

        .chat-history {
            flex: 1;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .history-item {
            padding: 0.75rem;
            border-radius: 0.375rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: var(--text-color);
            font-size: 0.875rem;
            transition: background-color 0.2s ease;
        }

        .history-item:hover {
            background-color: var(--hover-color);
        }

        /* Main Chat Area */
        .chat-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
        }

        .chat-box {
            flex: 1;
            overflow-y: auto;
            padding: 1rem 0;
            scroll-behavior: smooth;
        }

        .message {
            display: flex;
            padding: 1.5rem;
            transition: background-color 0.2s ease;
        }

        .message-content {
            max-width: 48rem;
            margin: 0 auto;
            width: 100%;
            display: flex;
            gap: 1.5rem;
        }

        .user-message {
            background-color: var(--background-dark);
        }

        .assistant-message {
            background-color: var(--message-dark);
        }

        .avatar {
            width: 2rem;
            height: 2rem;
            border-radius: 0.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .user-message .avatar {
            background-color: #5436DA;
        }

        .assistant-message .avatar {
            background-color: var(--primary-color);
        }

        .message-text {
            flex: 1;
            line-height: 1.75;
            padding-top: 0.25rem;
        }

        /* Input Area */
        .input-container {
            position: fixed;
            bottom: 0;
            left: var(--sidebar-width);
            right: 0;
            padding: 1.5rem 1rem;
            background: linear-gradient(180deg, rgba(52,53,65,0) 0%, var(--background-dark) 50%);
        }

        .input-box {
            max-width: 48rem;
            margin: 0 auto;
            position: relative;
        }

        .message-input {
            width: 100%;
            min-height: 3.25rem;
            max-height: 12rem;
            padding: 0.875rem 2.5rem 0.875rem 1rem;
            background-color: var(--message-dark);
            border: 1px solid var(--border-color);
            border-radius: 0.875rem;
            color: var(--text-color);
            font-size: 1rem;
            line-height: 1.5;
            resize: none;
            outline: none;
            transition: border-color 0.2s ease;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .message-input:focus {
            border-color: var(--primary-color);
        }

        .send-button {
            position: absolute;
            right: 0.75rem;
            bottom: 0.75rem;
            background: none;
            border: none;
            color: var(--primary-color);
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 0.375rem;
            transition: background-color 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .send-button:hover {
            background-color: rgba(16, 163, 127, 0.1);
        }

        .send-button:disabled {
            color: var(--border-color);
            cursor: not-allowed;
        }

        /* Loading Animation */
        .loading {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
            padding: 1.5rem;
        }

        .loading span {
            width: 0.5rem;
            height: 0.5rem;
            background-color: var(--primary-color);
            border-radius: 50%;
            animation: bounce 0.5s alternate infinite;
        }

        .loading span:nth-child(2) { animation-delay: 0.15s; }
        .loading span:nth-child(3) { animation-delay: 0.3s; }

        @keyframes bounce {
            from { transform: translateY(0); }
            to { transform: translateY(-0.5rem); }
        }

        /* Code Block Styling */
        pre {
            background-color: #1E1E1E;
            padding: 1rem;
            border-radius: 0.5rem;
            overflow-x: auto;
            margin: 1rem 0;
            position: relative;
        }

        code {
            font-family: 'Menlo', 'Monaco', 'Courier New', monospace;
            font-size: 0.875rem;
            line-height: 1.6;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                left: -100%;
                top: 0;
                bottom: 0;
                z-index: 1000;
            }

            .sidebar.active {
                left: 0;
            }

            .input-container {
                left: 0;
                padding: 1rem;
            }

            .message {
                padding: 1rem;
            }

            .message-content {
                padding: 0 1rem;
            }
        }

        /* Scrollbar Styling */
        ::-webkit-scrollbar {
            width: 0.5rem;
            height: 0.5rem;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--border-color);
            border-radius: 0.25rem;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <button class="new-chat-btn" onclick="clearChat()">
                <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" height="24" width="24">
                    <path d="M12 4.5v15m7.5-7.5h-15"></path>
                </svg>
                New chat
            </button>
            <div class="chat-history" id="chatHistory"></div>
        </aside>

        <main class="chat-container">
            <div class="chat-box" id="chatBox">
                <?php
                foreach ($_SESSION['messages'] as $message) {
                    $class = $message['role'] === 'user' ? 'user-message' : 'assistant-message';
                    $avatar = $message['role'] === 'user' ? 'U' : 'A';
                    
                    echo '<div class="message '.$class.'">
                            <div class="message-content">
                                <div class="avatar">'.$avatar.'</div>
                                <div class="message-text">'.nl2br(htmlspecialchars($message['content'])).'</div>
                            </div>
                          </div>';
                }
                ?>
            </div>

            <div class="input-container">
                <div class="input-box">
                    <textarea 
                        id="messageInput" 
                        class="message-input" 
                        placeholder="Send a message..."
                        rows="1"
                        onkeydown="handleKeyDown(event)"
                    ></textarea>
                    <button class="send-button" id="sendButton" onclick="sendMessage()" disabled>
                        <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" height="16" width="16">
                            <path d="M7 11L12 6L17 11M12 18V7" stroke="currentColor"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </main>
    </div>

    <script>
        const messageInput = document.getElementById('messageInput');
        const chatBox = document.getElementById('chatBox');
        const sendButton = document.getElementById('sendButton');

        // Auto-resize textarea
        messageInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 192) + 'px';
            sendButton.disabled = !this.value.trim();
        });

        function handleKeyDown(event) {
            if (event.key === 'Enter' && !event.shiftKey && !sendButton.disabled) {
                event.preventDefault();
                sendMessage();
            }
        }

        function addMessage(content, role) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${role}-message`;
            messageDiv.innerHTML = `
                <div class="message-content">
                    <div class="avatar">${role === 'user' ? 'U' : 'A'}</div>
                    <div class="message-text">${content}</div>
                </div>
            `;
            chatBox.appendChild(messageDiv);
            chatBox.scrollTop = chatBox.scrollHeight;
        }

        function showLoading() {
            const loadingDiv = document.createElement('div');
            loadingDiv.className = 'loading';
            loadingDiv.innerHTML = '<span></span><span></span><span></span>';
            chatBox.appendChild(loadingDiv);
            chatBox.scrollTop = chatBox.scrollHeight;
            return loadingDiv;
        }

        function sendMessage() {
            const message = messageInput.value.trim();
            if (!message) return;

            // Add user message
            addMessage(message, 'user');
            messageInput.value = '';
            messageInput.style.height = 'auto';
            sendButton.disabled = true;

            // Show loading animation
            const loadingDiv = showLoading();

            // Send to backend
            fetch('process.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ message: message })
            })
            .then(response => response.json())
            .then(data => {
                loadingDiv.remove();
                if (data.success) {
                    addMessage(data.response, 'assistant');
                } else {
                    addMessage('Sorry, there was an error processing your request.', 'assistant');
                }
            })
            .catch(error => {
                loadingDiv.remove();
                addMessage('Sorry, there was an error connecting to the server.', 'assistant');
                console.error('Error:', error);
            });
        }

        function clearChat() {
            fetch('clear.php')
            .then(() => {
                chatBox.innerHTML = '';
                location.reload();
            });
        }
    </script>
</body>
</html>
