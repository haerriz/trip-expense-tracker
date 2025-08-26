// Floating Chat Manager
class FloatingChatManager {
    constructor() {
        this.isOpen = false;
        this.unreadCount = 0;
        this.currentTripId = null;
        this.messages = [];
        this.userScrolledUp = false;
        this.init();
    }

    init() {
        this.chatBubble = document.getElementById('chat-bubble');
        this.chatWindow = document.getElementById('chat-window');
        this.chatWindowClose = document.getElementById('chat-window-close');
        this.chatBadge = document.getElementById('chat-badge');
        this.floatingMessages = document.getElementById('chat-messages-floating');
        this.floatingInput = document.getElementById('chat-message-floating');
        this.floatingSendBtn = document.getElementById('send-message-floating');

        this.setupEventListeners();
        this.startMessageSync();
    }

    setupEventListeners() {
        // Chat bubble click
        if (this.chatBubble) {
            this.chatBubble.addEventListener('click', () => this.toggleChat());
        }

        // Close button click
        if (this.chatWindowClose) {
            this.chatWindowClose.addEventListener('click', () => this.closeChat());
        }

        // Send message
        if (this.floatingSendBtn) {
            this.floatingSendBtn.addEventListener('click', () => this.sendMessage());
        }

        if (this.floatingInput) {
            this.floatingInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    this.sendMessage();
                }
            });

            this.floatingInput.addEventListener('input', () => {
                this.floatingSendBtn.disabled = !this.floatingInput.value.trim();
            });
        }

        // Track user scroll
        if (this.floatingMessages) {
            this.floatingMessages.addEventListener('scroll', () => {
                const { scrollTop, scrollHeight, clientHeight } = this.floatingMessages;
                this.userScrolledUp = scrollTop < scrollHeight - clientHeight - 30;
            });
        }
    }

    setTripId(tripId) {
        this.currentTripId = tripId;
        this.messages = []; // Reset messages when changing trip
        if (tripId) {
            this.showChatBubble();
            if (this.isOpen) {
                this.renderAllMessages();
                this.loadMessages();
            }
        } else {
            this.hideChatBubble();
        }
    }

    loadMessages() {
        if (!this.currentTripId) return;
        
        $.get('api/get_chat.php', { trip_id: this.currentTripId })
            .done((response) => {
                if (response.success) {
                    this.updateMessages(response.messages);
                }
            })
            .fail(() => {
            });
    }

    updateMessages(newMessages) {
        if (!this.floatingMessages) return;
        
        // Check if there are actually new messages
        const hasNewMessages = newMessages.length > this.messages.length;
        
        if (hasNewMessages) {
            // Only add the new messages
            const messagesToAdd = newMessages.slice(this.messages.length);
            
            messagesToAdd.forEach(message => {
                const messageElement = this.createMessageElement(message);
                this.floatingMessages.appendChild(messageElement);
            });
            
            // Only scroll to bottom if user is near bottom or it's their own message
            if (!this.userScrolledUp) {
                this.scrollToBottom();
            }
        }
        
        this.messages = newMessages;
    }

    renderAllMessages() {
        if (!this.floatingMessages) return;
        
        this.floatingMessages.innerHTML = '';
        
        this.messages.forEach(message => {
            const messageElement = this.createMessageElement(message);
            this.floatingMessages.appendChild(messageElement);
        });
        
        this.scrollToBottom();
    }

    createMessageElement(message) {
        const div = document.createElement('div');
        const isOwn = message.user_id == window.currentUserId;
        const messageClass = isOwn ? 'chat-message--own' : 'chat-message--other';
        
        const messageDate = new Date(message.created_at);
        const time = messageDate.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        
        // Get user avatar
        const avatar = message.sender_avatar || 
            `https://ui-avatars.com/api/?name=${encodeURIComponent(message.sender_name)}&size=16&background=2196F3&color=fff`;
        
        div.className = `chat-message ${messageClass}`;
        div.innerHTML = `
            <div class="chat-message__bubble">
                ${!isOwn ? `
                    <div class="chat-message__header">
                        <img src="${avatar}" alt="${message.sender_name}" class="chat-message__avatar">
                        <span class="chat-message__sender">${message.sender_name}</span>
                        <span class="chat-message__time">${time}</span>
                    </div>
                ` : `
                    <div class="chat-message__header">
                        <span class="chat-message__time">${time}</span>
                        <img src="${avatar}" alt="You" class="chat-message__avatar">
                    </div>
                `}
                <div class="chat-message__text">${message.message}</div>
            </div>
        `;
        
        return div;
    }

    startMessageSync() {
        // Sync messages every 3 seconds but only add new ones
        setInterval(() => {
            if (this.currentTripId && this.isOpen) {
                this.loadMessages();
            }
        }, 3000);
    }

    showChatBubble() {
        if (this.chatBubble) {
            this.chatBubble.style.display = 'flex';
        }
    }

    hideChatBubble() {
        if (this.chatBubble) {
            this.chatBubble.style.display = 'none';
        }
        this.closeChat();
    }

    toggleChat() {
        if (this.isOpen) {
            this.closeChat();
        } else {
            this.openChat();
        }
    }

    openChat() {
        if (this.chatWindow) {
            this.chatWindow.style.display = 'flex';
            this.isOpen = true;
            this.userScrolledUp = false;
            this.updateUnreadCount(0);
            
            // Load all messages when opening
            this.messages = [];
            this.loadMessages();
            
            if (this.floatingInput) {
                setTimeout(() => this.floatingInput.focus(), 100);
            }
        }
    }

    closeChat() {
        if (this.chatWindow) {
            this.chatWindow.style.display = 'none';
            this.isOpen = false;
        }
    }

    updateUnreadCount(count) {
        this.unreadCount = count;
        if (this.chatBadge) {
            if (count > 0) {
                this.chatBadge.textContent = count > 99 ? '99+' : count;
                this.chatBadge.style.display = 'flex';
            } else {
                this.chatBadge.style.display = 'none';
            }
        }
    }

    sendMessage() {
        const message = this.floatingInput.value.trim();
        if (!message || !this.currentTripId) return;

        this.floatingSendBtn.disabled = true;
        
        $.post('api/send_chat.php', {
            trip_id: this.currentTripId,
            message: message
        })
        .done((response) => {
            if (response.success) {
                this.floatingInput.value = '';
                this.floatingSendBtn.disabled = true;
                // Load messages immediately after sending
                setTimeout(() => this.loadMessages(), 200);
            }
        })
        .fail(() => {
            this.floatingSendBtn.disabled = false;
        });
    }

    scrollToBottom() {
        if (this.floatingMessages) {
            this.floatingMessages.scrollTop = this.floatingMessages.scrollHeight;
            this.userScrolledUp = false;
        }
    }
}

// Initialize floating chat when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.floatingChat = new FloatingChatManager();
    
    // Listen for trip selection from main dashboard
    const currentTripSelect = document.getElementById('current-trip');
    if (currentTripSelect) {
        currentTripSelect.addEventListener('change', () => {
            if (currentTripSelect.value) {
                window.floatingChat.setTripId(currentTripSelect.value);
            } else {
                window.floatingChat.hideChatBubble();
            }
        });
    }
    
    // Hook into enhanced chat system
    setTimeout(() => {
        if (window.enhancedChat) {
            const originalSetTripId = window.enhancedChat.setTripId;
            window.enhancedChat.setTripId = function(tripId) {
                originalSetTripId.call(this, tripId);
                window.floatingChat.setTripId(tripId);
            };
        }
    }, 1000);
});