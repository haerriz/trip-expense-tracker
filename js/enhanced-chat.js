// Enhanced Chat System
class EnhancedChat {
    constructor() {
        this.currentTripId = null;
        this.currentUserId = window.currentUserId;
        this.messages = [];
        this.typingTimeout = null;
        this.isTyping = false;
        this.lastMessageDate = null;
        this.autoScrollEnabled = true;
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.setupEmojiPicker();
        this.startHeartbeat();
    }
    
    bindEvents() {
        // Send message events
        $('#send-message').on('click', () => this.sendMessage());
        $('#chat-message').on('keypress', (e) => {
            if (e.which === 13) {
                e.preventDefault();
                this.sendMessage();
            } else {
                this.handleTyping();
            }
        });
        
        // Input validation
        $('#chat-message').on('input', () => {
            const message = $('#chat-message').val().trim();
            $('#send-message').prop('disabled', message.length === 0);
        });
        
        // Scroll to bottom
        $('#scroll-to-bottom').on('click', () => this.scrollToBottom());
        
        // Chat features
        $('#emoji-btn').on('click', () => this.toggleEmojiPicker());
        $('#clear-chat-btn').on('click', () => this.clearChat());
        $('#attachment-btn').on('click', () => this.handleAttachment());
        
        // Auto-scroll detection
        $('#chat-messages').on('scroll', () => this.handleScroll());
    }
    
    setTripId(tripId) {
        this.currentTripId = tripId;
        this.loadMessages();
    }
    
    loadMessages() {
        if (!this.currentTripId) return;
        
        $.get('/api/get_chat.php', { trip_id: this.currentTripId })
            .done((response) => {
                if (response.success) {
                    this.messages = response.messages;
                    this.renderMessages();
                    this.scrollToBottom();
                }
            })
            .fail(() => {
                this.showError('Failed to load messages');
            });
    }
    
    sendMessage() {
        const messageText = $('#chat-message').val().trim();
        if (!messageText || !this.currentTripId) return;
        
        // Disable send button temporarily
        $('#send-message').prop('disabled', true);
        
        $.post('/api/send_chat.php', {
            trip_id: this.currentTripId,
            message: messageText
        })
        .done((response) => {
            if (response.success) {
                $('#chat-message').val('');
                this.loadMessages(); // Reload to get the new message
                this.stopTyping();
            } else {
                this.showError('Failed to send message');
            }
        })
        .fail(() => {
            this.showError('Failed to send message');
        })
        .always(() => {
            $('#send-message').prop('disabled', false);
        });
    }
    
    renderMessages() {
        const container = $('#chat-messages');
        const scrollButton = $('#scroll-to-bottom');
        
        // Clear existing messages but keep scroll button
        container.empty().append(scrollButton);
        
        let lastDate = null;
        
        this.messages.forEach((message, index) => {
            const messageDate = new Date(message.created_at).toDateString();
            
            // Add date divider if date changed
            if (messageDate !== lastDate) {
                container.append(this.createDateDivider(messageDate));
                lastDate = messageDate;
            }
            
            const messageElement = this.createMessageElement(message);
            container.append(messageElement);
        });
        
        // Show scroll button if needed
        this.updateScrollButton();
    }
    
    createDateDivider(dateString) {
        const date = new Date(dateString);
        const today = new Date().toDateString();
        const yesterday = new Date(Date.now() - 86400000).toDateString();
        
        let displayDate;
        if (dateString === today) {
            displayDate = 'Today';
        } else if (dateString === yesterday) {
            displayDate = 'Yesterday';
        } else {
            displayDate = date.toLocaleDateString();
        }
        
        return `
            <div class="chat-date-divider">
                <span>${displayDate}</span>
            </div>
        `;
    }
    
    createMessageElement(message) {
        const isOwn = message.user_id == this.currentUserId;
        const messageClass = isOwn ? 'chat-message--own' : 'chat-message--other';
        const time = new Date(message.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        
        const avatar = this.getUserAvatar(message);
        
        return `
            <div class="chat-message ${messageClass}" data-message-id="${message.id}">
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
                        </div>
                    `}
                    <div class="chat-message__text">${this.formatMessage(message.message, message)}</div>
                    ${isOwn ? `
                        <div class="message-status">
                            <i class="material-icons">done</i>
                        </div>
                    ` : ''}
                </div>
            </div>
        `;
    }
    
    formatMessage(text, message = null) {
        // Handle file attachments
        if (message && message.file_url) {
            const fileExt = message.file_name.split('.').pop().toLowerCase();
            const isImage = ['jpg', 'jpeg', 'png', 'gif'].includes(fileExt);
            
            if (isImage) {
                return `
                    ${text}
                    <div class="chat-attachment">
                        <img src="${message.file_url}" alt="${message.file_name}" 
                             style="max-width: 200px; max-height: 200px; border-radius: 8px; margin-top: 8px; cursor: pointer;"
                             onclick="window.open('${message.file_url}', '_blank')">
                    </div>
                `;
            } else {
                return `
                    ${text}
                    <div class="chat-attachment" style="margin-top: 8px; padding: 8px; background: rgba(0,0,0,0.1); border-radius: 8px;">
                        <a href="${message.file_url}" target="_blank" style="color: inherit; text-decoration: none;">
                            <i class="material-icons" style="vertical-align: middle; margin-right: 4px;">attach_file</i>
                            ${message.file_name}
                        </a>
                    </div>
                `;
            }
        }
        
        // Convert URLs to links
        const urlRegex = /(https?:\/\/[^\s]+)/g;
        text = text.replace(urlRegex, '<a href="$1" target="_blank" rel="noopener">$1</a>');
        
        // Convert emojis (basic implementation)
        const emojiMap = {
            ':)': '😊',
            ':D': '😃',
            ':(': '😢',
            ':P': '😛',
            '<3': '❤️',
            ':thumbsup:': '👍',
            ':thumbsdown:': '👎'
        };
        
        Object.keys(emojiMap).forEach(key => {
            text = text.replace(new RegExp(key.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'g'), emojiMap[key]);
        });
        
        return text;
    }
    
    getUserAvatar(message) {
        if (message.sender_avatar && message.sender_avatar !== '') {
            return message.sender_avatar;
        }
        return `https://ui-avatars.com/api/?name=${encodeURIComponent(message.sender_name)}&size=24&background=667eea&color=fff`;
    }
    
    handleTyping() {
        if (!this.isTyping) {
            this.isTyping = true;
            this.sendTypingIndicator(true);
        }
        
        clearTimeout(this.typingTimeout);
        this.typingTimeout = setTimeout(() => {
            this.stopTyping();
        }, 2000);
    }
    
    stopTyping() {
        if (this.isTyping) {
            this.isTyping = false;
            this.sendTypingIndicator(false);
        }
        clearTimeout(this.typingTimeout);
    }
    
    scrollToBottom() {
        const container = $('#chat-messages');
        container.animate({ scrollTop: container[0].scrollHeight }, 300);
        this.autoScrollEnabled = true;
        this.updateScrollButton();
    }
    
    handleScroll() {
        const container = $('#chat-messages')[0];
        const isAtBottom = container.scrollHeight - container.clientHeight <= container.scrollTop + 1;
        this.autoScrollEnabled = isAtBottom;
        this.updateScrollButton();
    }
    
    updateScrollButton() {
        const container = $('#chat-messages')[0];
        const isAtBottom = container.scrollHeight - container.clientHeight <= container.scrollTop + 50;
        
        if (isAtBottom) {
            $('#scroll-to-bottom').removeClass('show');
        } else {
            $('#scroll-to-bottom').addClass('show');
        }
    }
    
    setupEmojiPicker() {
        // Simple emoji picker implementation
        this.emojis = ['😊', '😃', '😢', '😛', '❤️', '👍', '👎', '🎉', '🔥', '💯', '😍', '🤔', '😂', '👌', '🙌'];
        this.emojiPickerVisible = false;
    }
    
    toggleEmojiPicker() {
        if (this.emojiPickerVisible) {
            this.hideEmojiPicker();
        } else {
            this.showEmojiPicker();
        }
    }
    
    showEmojiPicker() {
        if ($('.emoji-picker').length > 0) return;
        
        const picker = $(`
            <div class="emoji-picker" style="
                position: absolute;
                bottom: 60px;
                right: 10px;
                background: white;
                border: 1px solid #e0e0e0;
                border-radius: 8px;
                padding: 10px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                z-index: 1000;
                display: grid;
                grid-template-columns: repeat(5, 1fr);
                gap: 5px;
                max-width: 200px;
            ">
                ${this.emojis.map(emoji => `
                    <button class="emoji-btn" style="
                        border: none;
                        background: none;
                        font-size: 1.2rem;
                        padding: 5px;
                        border-radius: 4px;
                        cursor: pointer;
                        transition: background 0.2s;
                    " data-emoji="${emoji}">${emoji}</button>
                `).join('')}
            </div>
        `);
        
        $('.trip-chat .card-content').append(picker);
        this.emojiPickerVisible = true;
        
        // Bind emoji click events
        $('.emoji-btn').on('click', (e) => {
            const emoji = $(e.target).data('emoji');
            const currentMessage = $('#chat-message').val();
            $('#chat-message').val(currentMessage + emoji).focus();
            this.hideEmojiPicker();
        });
        
        // Close on outside click
        $(document).on('click.emoji-picker', (e) => {
            if (!$(e.target).closest('.emoji-picker, #emoji-btn').length) {
                this.hideEmojiPicker();
            }
        });
    }
    
    hideEmojiPicker() {
        $('.emoji-picker').remove();
        this.emojiPickerVisible = false;
        $(document).off('click.emoji-picker');
    }
    
    clearChat() {
        if (confirm('Are you sure you want to clear the chat? This action cannot be undone.')) {
            $.post('/api/clear_chat.php', { trip_id: this.currentTripId })
                .done((response) => {
                    if (response.success) {
                        this.messages = [];
                        this.renderMessages();
                        M.toast({html: 'Chat cleared successfully', classes: 'green'});
                    } else {
                        M.toast({html: response.error || 'Failed to clear chat', classes: 'red'});
                    }
                })
                .fail(() => {
                    M.toast({html: 'Failed to clear chat', classes: 'red'});
                });
        }
    }
    
    handleAttachment() {
        const fileInput = $('<input type="file" accept="image/*,.pdf,.doc,.docx" style="display:none;">');
        
        fileInput.on('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                this.uploadFile(file);
            }
        });
        
        $('body').append(fileInput);
        fileInput.click();
        fileInput.remove();
    }
    
    uploadFile(file) {
        if (!this.currentTripId) return;
        
        const formData = new FormData();
        formData.append('file', file);
        formData.append('trip_id', this.currentTripId);
        
        // Show upload progress
        M.toast({html: 'Uploading file...', classes: 'blue'});
        
        $.ajax({
            url: '/api/upload_chat_file.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: (response) => {
                if (response.success) {
                    M.toast({html: 'File uploaded successfully', classes: 'green'});
                    this.loadMessages();
                } else {
                    M.toast({html: response.error || 'Upload failed', classes: 'red'});
                }
            },
            error: () => {
                M.toast({html: 'Upload failed', classes: 'red'});
            }
        });
    }
    
    sendTypingIndicator(isTyping) {
        if (!this.currentTripId) return;
        
        $.post('/api/typing_status.php', {
            trip_id: this.currentTripId,
            is_typing: isTyping
        });
    }
    
    checkTypingStatus() {
        if (!this.currentTripId) return;
        
        $.get('/api/typing_status.php', { trip_id: this.currentTripId })
            .done((response) => {
                if (response.success && response.typing_users.length > 0) {
                    const typingText = response.typing_users.length === 1 
                        ? `${response.typing_users[0]} is typing`
                        : `${response.typing_users.slice(0, -1).join(', ')} and ${response.typing_users.slice(-1)} are typing`;
                    
                    $('#typing-user').text(typingText.replace(' is typing', '').replace(' are typing', ''));
                    $('#typing-indicator').addClass('active');
                } else {
                    $('#typing-indicator').removeClass('active');
                }
            });
    }
    
    startHeartbeat() {
        setInterval(() => {
            if (this.currentTripId) {
                if (this.autoScrollEnabled) {
                    this.loadMessages();
                }
                this.checkTypingStatus();
            }
        }, 3000);
        
        setInterval(() => {
            if (this.currentTripId) {
                this.checkTypingStatus();
            }
        }, 1000);
    }
    
    showError(message) {
        M.toast({html: message, classes: 'red'});
    }
}

// Initialize enhanced chat when document is ready
$(document).ready(() => {
    window.enhancedChat = new EnhancedChat();
    
    // Hook into existing trip selection
    const originalLoadTripData = window.loadTripData;
    if (originalLoadTripData) {
        window.loadTripData = function(tripId) {
            originalLoadTripData(tripId);
            window.enhancedChat.setTripId(tripId);
        };
    }
});

// CSS for emoji picker hover effects
$('<style>').text(`
    .emoji-btn:hover {
        background-color: #f0f0f0 !important;
    }
    
    .chat-message__text a {
        color: inherit;
        text-decoration: underline;
    }
    
    .chat-message--own .chat-message__text a {
        color: rgba(255, 255, 255, 0.9);
    }
`).appendTo('head');