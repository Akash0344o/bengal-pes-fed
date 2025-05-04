class LiveMatchUpdates {
    constructor(options) {
        this.matchId = options.matchId;
        this.userId = options.userId;
        this.userToken = options.userToken;
        this.socket = null;
        this.connected = false;
        this.init();
    }
    
    init() {
        this.connectWebSocket();
        this.setupEventHandlers();
    }
    
    connectWebSocket() {
        this.socket = new WebSocket(`ws://${window.location.hostname}:8080`);
        
        this.socket.onopen = () => {
            this.connected = true;
            console.log('WebSocket connected');
            this.authenticate();
            this.subscribeToMatch();
        };
        
        this.socket.onmessage = (e) => {
            this.handleMessage(JSON.parse(e.data));
        };
        
        this.socket.onclose = () => {
            this.connected = false;
            console.log('WebSocket disconnected');
            setTimeout(() => this.connectWebSocket(), 5000);
        };
        
        this.socket.onerror = (error) => {
            console.error('WebSocket error:', error);
        };
    }
    
    authenticate() {
        if (!this.userId || !this.userToken) return;
        
        this.socket.send(JSON.stringify({
            action: 'authenticate',
            user_id: this.userId,
            token: this.userToken
        }));
    }
    
    subscribeToMatch() {
        this.socket.send(JSON.stringify({
            action: 'subscribe',
            channel: `match_${this.matchId}`
        }));
    }
    
    handleMessage(data) {
        switch (data.action) {
            case 'update':
                this.handleMatchUpdate(data);
                break;
                
            case 'comment':
                this.handleNewComment(data);
                break;
                
            case 'authentication':
                console.log('Authentication result:', data.success);
                break;
        }
    }
    
    handleMatchUpdate(data) {
        const matchContainer = document.querySelector(`.match-container[data-match-id="${data.match_id}"]`);
        if (!matchContainer) return;
        
        switch (data.type) {
            case 'score':
                this.updateScores(data.data);
                break;
                
            case 'status':
                this.updateMatchStatus(data.data);
                break;
                
            case 'time':
                this.updateMatchTime(data.data);
                break;
        }
        
        this.showUpdateNotification('Match updated!');
    }
    
    updateScores(scores) {
        if (scores.team1 !== undefined) {
            const scoreElement = document.querySelector('.team1-score');
            if (scoreElement) scoreElement.textContent = scores.team1;
        }
        
        if (scores.team2 !== undefined) {
            const scoreElement = document.querySelector('.team2-score');
            if (scoreElement) scoreElement.textContent = scores.team2;
        }
    }
    
    updateMatchStatus(status) {
        const statusElement = document.querySelector('.match-status');
        if (!statusElement) return;
        
        statusElement.className = 'match-status ' + status;
        statusElement.textContent = status.replace('_', ' ');
        
        if (status === 'completed') {
            const winnerElement = document.querySelector('.match-winner');
            if (winnerElement) winnerElement.style.display = 'block';
        }
    }
    
    updateMatchTime(timeData) {
        const timeElement = document.querySelector('.match-time');
        if (timeElement) {
            timeElement.textContent = new Date(timeData).toLocaleString();
        }
    }
    
    handleNewComment(comment) {
        const commentsContainer = document.querySelector('.comments-container');
        if (!commentsContainer) return;
        
        const commentElement = document.createElement('div');
        commentElement.className = 'comment';
        commentElement.innerHTML = `
            <div class="comment-header">
                <span class="comment-author">User ${comment.user_id}</span>
                <span class="comment-time">${new Date(comment.timestamp * 1000).toLocaleTimeString()}</span>
            </div>
            <div class="comment-content">${comment.content}</div>
        `;
        
        commentsContainer.insertBefore(commentElement, commentsContainer.firstChild);
        
        // Auto-scroll if at bottom
        const isAtBottom = commentsContainer.scrollTop + commentsContainer.clientHeight >= 
                          commentsContainer.scrollHeight - 50;
        
        if (isAtBottom) {
            commentsContainer.scrollTop = commentsContainer.scrollHeight;
        }
    }
    
    sendComment(content) {
        if (!this.connected || !this.userId) return false;
        
        this.socket.send(JSON.stringify({
            action: 'comment',
            match_id: this.matchId,
            content: content
        }));
        
        return true;
    }
    
    sendUpdate(type, data) {
        if (!this.connected) return false;
        
        this.socket.send(JSON.stringify({
            action: 'update',
            match_id: this.matchId,
            type: type,
            data: data
        }));
        
        return true;
    }
    
    showUpdateNotification(message) {
        const notification = document.createElement('div');
        notification.className = 'update-notification';
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.classList.add('fade-out');
            setTimeout(() => notification.remove(), 500);
        }, 3000);
    }
}

// Initialize on match page
document.addEventListener('DOMContentLoaded', function() {
    const matchContainer = document.querySelector('.match-container');
    if (!matchContainer) return;
    
    const matchId = matchContainer.dataset.matchId;
    const userId = matchContainer.dataset.userId || null;
    const userToken = matchContainer.dataset.userToken || null;
    
    const liveUpdates = new LiveMatchUpdates({
        matchId: matchId,
        userId: userId,
        userToken: userToken
    });
    
    // Comment form submission
    const commentForm = document.querySelector('.comment-form');
    if (commentForm) {
        commentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const textarea = this.querySelector('textarea');
            const content = textarea.value.trim();
            
            if (content && liveUpdates.sendComment(content)) {
                textarea.value = '';
            }
        });
    }
    
    // Admin controls
    const adminForm = document.querySelector('.admin-match-controls form');
    if (adminForm) {
        adminForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = {
                team1_score: formData.get('team1_score'),
                team2_score: formData.get('team2_score'),
                status: formData.get('status')
            };
            
            // Send update via WebSocket
            liveUpdates.sendUpdate('score', {
                team1: data.team1_score,
                team2: data.team2_score
            });
            
            liveUpdates.sendUpdate('status', data.status);
            
            // Also submit to server
            fetch(this.action, {
                method: 'POST',
                body: formData
            });
        });
    }
});