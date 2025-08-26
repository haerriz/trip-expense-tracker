// Floating Chat Manager
class FloatingChatManager {
    constructor() {
        this.isOpen = false;
        this.unreadCount = 0;
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

        // Show chat bubble when trip is selected
        this.setupEventListeners();
        this.syncWithOriginalChat();
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

        // Listen for trip selection to show/hide chat bubble
        document.addEventListener('tripSelected', () => {
            this.showChatBubble();
        });
        
        // Also listen for global trip change events
        if (window.enhancedChat) {
            const originalSetTripId = window.enhancedChat.setTripId;
            window.enhancedChat.setTripId = (tripId) => {
                originalSetTripId.call(window.enhancedChat, tripId);
                if (tripId) {
                    this.showChatBubble();
                } else {
                    this.hideChatBubble();
                }
            };
        }

        // Listen for new messages to update badge
        document.addEventListener('newChatMessage', (e) => {
            if (!this.isOpen) {
                this.updateUnreadCount(this.unreadCount + 1);
            }
        });
    }

    syncWithOriginalChat() {
        // Sync messages from original chat system
        const originalMessages = document.getElementById('chat-messages');
        if (originalMessages) {
            // Copy existing messages
            this.floatingMessages.innerHTML = originalMessages.innerHTML;
            
            // Set up mutation observer to sync new messages
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.type === 'childList') {
                        this.floatingMessages.innerHTML = originalMessages.innerHTML;
                        this.scrollToBottom();
                        
                        if (!this.isOpen) {
                            this.updateUnreadCount(this.unreadCount + 1);
                        }
                    }
                });
            });

            observer.observe(originalMessages, { childList: true, subtree: true });
        }
    }

    showChatBubble() {
        if (this.chatBubble) {
            this.chatBubble.style.display = 'flex';
            console.log('Chat bubble shown'); // Debug log
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
            this.updateUnreadCount(0);
            this.scrollToBottom();
            
            // Focus input
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
        if (!message) return;

        // Use the existing chat system's send function
        if (window.sendChatMessage) {
            window.sendChatMessage(message);
            this.floatingInput.value = '';
            this.floatingSendBtn.disabled = true;
        } else {
            // Fallback: trigger the original send button
            const originalInput = document.getElementById('chat-message');
            const originalSendBtn = document.getElementById('send-message');
            
            if (originalInput && originalSendBtn) {
                originalInput.value = message;
                originalSendBtn.click();
                this.floatingInput.value = '';
                this.floatingSendBtn.disabled = true;
            }
        }
    }

    scrollToBottom() {
        if (this.floatingMessages) {
            setTimeout(() => {
                this.floatingMessages.scrollTop = this.floatingMessages.scrollHeight;
            }, 100);
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
                document.dispatchEvent(new CustomEvent('tripSelected'));
                window.floatingChat.showChatBubble();
            } else {
                window.floatingChat.hideChatBubble();
            }
        });
    }
    
    // Monitor for trip dashboard visibility
    const observer = new MutationObserver(() => {
        const tripDashboard = document.getElementById('trip-dashboard');
        if (tripDashboard && tripDashboard.style.display !== 'none') {
            const tripSelect = document.getElementById('current-trip');
            if (tripSelect && tripSelect.value) {
                window.floatingChat.showChatBubble();
            }
        }
    });
    
    const tripDashboard = document.getElementById('trip-dashboard');
    if (tripDashboard) {
        observer.observe(tripDashboard, { attributes: true, attributeFilter: ['style'] });
    }
});