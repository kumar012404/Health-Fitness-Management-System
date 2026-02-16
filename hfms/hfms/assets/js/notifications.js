/**
 * Notification System - Health and Fitness Management System
 * Handles browser notifications and toast alerts for reminders
 */

// Notification System Class
class NotificationSystem {
    constructor() {
        this.permission = 'default';
        this.checkInterval = 30000; // Check every 30 seconds
        this.notifiedReminders = new Set();
        this.init();
    }

    // Initialize notification system
    init() {
        this.requestPermission();
        this.createToastContainer();
        this.startReminderCheck();
    }

    // Request browser notification permission
    requestPermission() {
        if ('Notification' in window) {
            if (Notification.permission === 'granted') {
                this.permission = 'granted';
            } else if (Notification.permission !== 'denied') {
                Notification.requestPermission().then(permission => {
                    this.permission = permission;
                    if (permission === 'granted') {
                        this.showToast('🔔 Notifications enabled!', 'success');
                    }
                });
            }
        }
    }

    // Create toast notification container
    createToastContainer() {
        if (document.getElementById('toast-container')) return;

        const container = document.createElement('div');
        container.id = 'toast-container';
        container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-width: 350px;
        `;
        document.body.appendChild(container);
    }

    // Show toast notification on screen
    showToast(message, type = 'info', duration = 5000) {
        const container = document.getElementById('toast-container');
        if (!container) return;

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;

        const colors = {
            success: { bg: '#48bb78', icon: '✓' },
            error: { bg: '#f56565', icon: '✕' },
            warning: { bg: '#ecc94b', icon: '⚠' },
            info: { bg: '#4299e1', icon: 'ℹ' },
            reminder: { bg: '#667eea', icon: '🔔' }
        };

        const color = colors[type] || colors.info;

        toast.style.cssText = `
            background: ${color.bg};
            color: white;
            padding: 16px 20px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            display: flex;
            align-items: flex-start;
            gap: 12px;
            animation: slideIn 0.3s ease;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
        `;

        toast.innerHTML = `
            <span style="font-size: 1.2rem;">${color.icon}</span>
            <div style="flex: 1;">
                <div style="font-weight: 600; margin-bottom: 2px;">${type === 'reminder' ? 'Reminder' : type.charAt(0).toUpperCase() + type.slice(1)}</div>
                <div style="font-size: 0.9rem; opacity: 0.95;">${message}</div>
            </div>
            <span style="cursor: pointer; font-size: 1.2rem; opacity: 0.7;" onclick="this.parentElement.remove()">×</span>
        `;

        container.appendChild(toast);

        // Add slide-in animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
        `;
        document.head.appendChild(style);

        // Auto remove after duration
        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, duration);

        // Click to dismiss
        toast.addEventListener('click', () => {
            toast.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        });
    }

    // Show browser notification
    showBrowserNotification(title, body, icon = '🔔') {
        if (this.permission !== 'granted') {
            // Fallback to toast
            this.showToast(body, 'reminder', 10000);
            return;
        }

        const notification = new Notification(title, {
            body: body,
            icon: '/hfms/assets/images/notification-icon.png',
            badge: '/hfms/assets/images/badge.png',
            tag: 'hfms-reminder',
            requireInteraction: true,
            vibrate: [200, 100, 200]
        });

        // Also show toast
        this.showToast(body, 'reminder', 10000);

        // Play notification sound
        this.playSound();

        notification.onclick = () => {
            window.focus();
            notification.close();
        };

        // Auto close after 30 seconds
        setTimeout(() => notification.close(), 30000);
    }

    // Play notification sound
    playSound() {
        try {
            const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2teleQQAOZPY6NKkQgAALobP4tibLf/+KIfP3tOLGP/7Ko/S3c6FFgD9MpnW3siADgD+Op/Z3cV+EP/+QKTc27x4DwD9Qqfe2rVyEAD8R6zh2bBtEAD6TK/j2KsqAAAAAAAAAAAAAAAAAAAAAAAA');
            audio.volume = 0.5;
            audio.play().catch(() => { });
        } catch (e) { }
    }

    // Check for due reminders
    async checkReminders() {
        try {
            const response = await fetch('/hfms/api/get_due_reminders.php');
            const data = await response.json();

            if (data.success && data.reminders && data.reminders.length > 0) {
                data.reminders.forEach(reminder => {
                    // Don't notify same reminder twice
                    const reminderKey = `${reminder.reminder_id}-${data.current_time.substring(0, 5)}`;
                    if (!this.notifiedReminders.has(reminderKey)) {
                        this.notifiedReminders.add(reminderKey);
                        this.triggerReminder(reminder);
                    }
                });
            }
        } catch (error) {
            console.log('Reminder check failed:', error);
        }
    }

    // Trigger reminder notification
    triggerReminder(reminder) {
        const categoryIcons = {
            exercise: '🏃',
            water: '💧',
            medication: '💊',
            meal: '🍽️',
            sleep: '😴',
            other: '🔔'
        };

        const icon = categoryIcons[reminder.category] || '🔔';
        const title = `${icon} ${reminder.title}`;
        const body = reminder.description || `Time for: ${reminder.title}`;

        // Show browser notification
        this.showBrowserNotification('HFMS Reminder', `${icon} ${reminder.title}`, icon);

        // Also show modal alert
        this.showReminderModal(reminder, icon);
    }

    // Show reminder modal
    showReminderModal(reminder, icon) {
        // Remove existing modal
        document.getElementById('reminder-alert-modal')?.remove();

        const modal = document.createElement('div');
        modal.id = 'reminder-alert-modal';
        modal.style.cssText = `
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            backdrop-filter: blur(4px);
            animation: fadeIn 0.3s ease;
        `;

        modal.innerHTML = `
            <div style="
                background: white;
                border-radius: 20px;
                padding: 2rem;
                max-width: 400px;
                width: 90%;
                text-align: center;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                animation: scaleIn 0.3s ease;
            ">
                <div style="font-size: 4rem; margin-bottom: 1rem;">${icon}</div>
                <h2 style="color: #1a202c; margin-bottom: 0.5rem; font-family: Poppins, sans-serif;">
                    Reminder!
                </h2>
                <h3 style="color: #667eea; margin-bottom: 1rem; font-family: Poppins, sans-serif;">
                    ${reminder.title}
                </h3>
                <p style="color: #718096; margin-bottom: 1.5rem;">
                    ${reminder.description || 'It\'s time for your scheduled activity!'}
                </p>
                <p style="color: #a0aec0; font-size: 0.9rem; margin-bottom: 1.5rem;">
                    ⏰ ${reminder.reminder_time}
                </p>
                <div style="display: flex; gap: 1rem; justify-content: center;">
                    <button onclick="document.getElementById('reminder-alert-modal').remove(); notificationSystem.markDone(${reminder.reminder_id});" 
                        style="
                            background: linear-gradient(135deg, #48bb78, #38a169);
                            color: white;
                            border: none;
                            padding: 0.75rem 1.5rem;
                            border-radius: 10px;
                            font-weight: 600;
                            cursor: pointer;
                            font-size: 1rem;
                        ">
                        ✓ Done
                    </button>
                    <button onclick="document.getElementById('reminder-alert-modal').remove();" 
                        style="
                            background: #e2e8f0;
                            color: #4a5568;
                            border: none;
                            padding: 0.75rem 1.5rem;
                            border-radius: 10px;
                            font-weight: 600;
                            cursor: pointer;
                            font-size: 1rem;
                        ">
                        Dismiss
                    </button>
                </div>
            </div>
        `;

        // Add animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
            @keyframes scaleIn { from { transform: scale(0.8); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        `;
        document.head.appendChild(style);

        document.body.appendChild(modal);

        // Close on backdrop click
        modal.addEventListener('click', (e) => {
            if (e.target === modal) modal.remove();
        });
    }

    // Mark reminder as done
    async markDone(reminderId) {
        try {
            await fetch('/hfms/api/complete_reminder.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ reminder_id: reminderId })
            });
            this.showToast('Reminder marked as complete!', 'success');
        } catch (error) {
            console.log('Failed to mark complete:', error);
        }
    }

    // Start periodic reminder check
    startReminderCheck() {
        // Check immediately
        this.checkReminders();

        // Then check every 30 seconds
        setInterval(() => this.checkReminders(), this.checkInterval);
    }

    // Clear old notified reminders (cleanup)
    clearOldNotifications() {
        const now = new Date();
        const currentMinute = now.getHours() + ':' + now.getMinutes();

        // Keep only current minute's notifications
        this.notifiedReminders.forEach(key => {
            if (!key.endsWith(currentMinute)) {
                this.notifiedReminders.delete(key);
            }
        });
    }
}

// Initialize notification system when DOM is ready
let notificationSystem;
document.addEventListener('DOMContentLoaded', () => {
    notificationSystem = new NotificationSystem();
});

// Export for global access
window.NotificationSystem = NotificationSystem;
