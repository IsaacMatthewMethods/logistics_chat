document.addEventListener('DOMContentLoaded', function() {
    const chatInput = document.querySelector('.chat-input-field');
    const sendBtn = document.querySelector('.chat-send-btn');
    const messagesContainer = document.querySelector('.chat-messages');
    
    if (chatInput && sendBtn && messagesContainer) {
        // Auto-resize textarea
        chatInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
        
        // Send message on Enter (but allow Shift+Enter for new line)
        chatInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });
        
        // Send message on button click
        sendBtn.addEventListener('click', sendMessage);
        
        // Scroll to bottom of messages
        scrollToBottom();
        
        // Poll for new messages every 2 seconds
        setInterval(fetchMessages, 2000);
    }
    
    // Conversation item click handler
    const conversationItems = document.querySelectorAll('.conversation-item');
    conversationItems.forEach(item => {
        item.addEventListener('click', function() {
            // Remove active class from all items
            conversationItems.forEach(i => i.classList.remove('active'));
            
            // Add active class to clicked item
            this.classList.add('active');
            
            // Get conversation ID and fetch messages
            const conversationId = this.dataset.conversationId;
            fetchMessages(conversationId);
        });
    });
    
    function sendMessage() {
        const message = chatInput.value.trim();
        if (message === '') return;
        
        const receiverId = document.querySelector('.chat-header').dataset.receiverId;
        const shipmentId = document.querySelector('.chat-header').dataset.shipmentId || null;
        
        // AJAX request to send message
        fetch('ajax/send_message.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `receiver_id=${receiverId}&shipment_id=${shipmentId}&content=${encodeURIComponent(message)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Clear input
                chatInput.value = '';
                chatInput.style.height = 'auto';
                
                // Fetch messages again to update the view
                fetchMessages();
            } else {
                alert('Failed to send message: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while sending the message');
        });
    }
    
    function fetchMessages(conversationId = null) {
        if (!conversationId) {
            conversationId = document.querySelector('.chat-header')?.dataset.receiverId;
            if (!conversationId) return;
        }
        
        const shipmentId = document.querySelector('.chat-header')?.dataset.shipmentId || null;
        
        // AJAX request to get messages
        fetch(`ajax/get_messages.php?receiver_id=${conversationId}&shipment_id=${shipmentId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update messages container
                messagesContainer.innerHTML = '';
                data.messages.forEach(message => {
                    const messageElement = createMessageElement(message);
                    messagesContainer.appendChild(messageElement);
                });
                
                // Scroll to bottom
                scrollToBottom();
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
    
    function createMessageElement(message) {
        const isSent = message.sender_id == <?php echo $_SESSION['user_id'] ?? 0; ?>;
        const messageClass = isSent ? 'message-sent' : 'message-received';
        
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${messageClass}`;
        
        const contentDiv = document.createElement('div');
        contentDiv.className = 'message-content';
        contentDiv.textContent = message.content;
        
        const timeDiv = document.createElement('div');
        timeDiv.className = 'message-time';
        timeDiv.textContent = formatTime(message.created_at);
        
        messageDiv.appendChild(contentDiv);
        messageDiv.appendChild(timeDiv);
        
        return messageDiv;
    }
    
    function formatTime(timestamp) {
        const date = new Date(timestamp);
        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }
    
    function scrollToBottom() {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
});