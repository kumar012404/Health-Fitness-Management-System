/**
 * ALARM System - Health and Fitness Management System
 * Real alarm-like notifications with sound and persistent popup
 * Supports multiple ringtone options and custom upload
 */

class AlarmSystem {
    constructor() {
        this.alarmSound = null;
        this.isPlaying = false;
        this.checkInterval = 10000; // Check every 10 seconds
        this.notifiedReminders = new Set();
        this.snoozedReminders = new Map(); // Track snoozed reminders with expiry time
        this.audioContext = null;
        this.customAudio = null; // For custom ringtone playback
        this.customRingtoneUrl = null;

        // Available ringtones
        this.ringtones = {
            'classic': { name: 'Classic Beep', frequency: 800, pattern: 'beep', icon: '🔔' },
            'gentle': { name: 'Gentle Chime', frequency: 523, pattern: 'chime', icon: '🎵' },
            'urgent': { name: 'Urgent Alert', frequency: 1000, pattern: 'urgent', icon: '🚨' },
            'melody': { name: 'Soft Melody', frequency: 659, pattern: 'melody', icon: '🎶' },
            'digital': { name: 'Digital Tone', frequency: 880, pattern: 'digital', icon: '📱' },
            'custom': { name: 'My Ringtone', pattern: 'custom', icon: '⭐' }
        };

        // Load saved ringtone preference
        this.selectedRingtone = localStorage.getItem('alarmRingtone') || 'classic';

        this.init();
    }

    async init() {
        this.createAlarmSound();
        this.createAlarmStyles();
        await this.loadCustomRingtone();
        this.startAlarmCheck();
        console.log('🔔 Alarm System Initialized with ringtone:', this.selectedRingtone);
    }

    // Load custom ringtone from server
    async loadCustomRingtone() {
        try {
            const response = await fetch('/hfms/api/get_user_ringtone.php');
            const data = await response.json();

            if (data.success && data.has_custom) {
                this.customRingtoneUrl = data.ringtone_url;
                console.log('🎵 Custom ringtone loaded:', data.filename);
            }
        } catch (error) {
            console.log('Error loading custom ringtone:', error);
        }
    }

    // Create audio context
    createAlarmSound() {
        try {
            this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
        } catch (e) {
            console.log('Audio not supported');
        }
    }

    // Set ringtone
    setRingtone(ringtoneId) {
        if (this.ringtones[ringtoneId]) {
            if (ringtoneId === 'custom' && !this.customRingtoneUrl) {
                this.showToast('⚠️ Please upload a custom ringtone first!', 'error');
                return false;
            }
            this.selectedRingtone = ringtoneId;
            localStorage.setItem('alarmRingtone', ringtoneId);
            console.log('🎵 Ringtone changed to:', this.ringtones[ringtoneId].name);
            return true;
        }
        return false;
    }

    // Get current ringtone
    getRingtone() {
        return this.selectedRingtone;
    }

    // Get all available ringtones
    getRingtones() {
        return this.ringtones;
    }

    // Preview a ringtone
    previewRingtone(ringtoneId) {
        this.stopAlarm();

        if (ringtoneId === 'custom') {
            if (this.customRingtoneUrl) {
                this.playCustomRingtone(3000); // Preview for 3 seconds
            } else {
                this.showToast('⚠️ No custom ringtone uploaded', 'error');
            }
            return;
        }

        const ringtone = this.ringtones[ringtoneId] || this.ringtones['classic'];
        this.isPlaying = true;
        this.playTone(ringtone, 2000); // Play for 2 seconds
    }

    // Play custom audio ringtone
    playCustomRingtone(duration = null) {
        if (!this.customRingtoneUrl) return;

        // Stop previous audio if playing
        if (this.customAudio) {
            this.customAudio.pause();
            this.customAudio.currentTime = 0;
        }

        this.customAudio = new Audio(this.customRingtoneUrl);
        this.customAudio.loop = !duration;
        this.customAudio.volume = 0.8;
        this.customAudio.play().catch(e => console.log('Audio play error:', e));

        if (duration) {
            setTimeout(() => {
                if (this.customAudio) {
                    this.customAudio.pause();
                    this.customAudio.currentTime = 0;
                }
            }, duration);
        }
    }

    // Stop custom audio
    stopCustomAudio() {
        if (this.customAudio) {
            this.customAudio.pause();
            this.customAudio.currentTime = 0;
        }
    }

    // Upload custom ringtone
    async uploadRingtone(file) {
        const formData = new FormData();
        formData.append('ringtone', file);

        try {
            const response = await fetch('/hfms/api/upload_ringtone.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                this.customRingtoneUrl = data.ringtone_url;
                this.showToast('✓ ' + data.message, 'success');
                this.updateRingtoneUI();
                return true;
            } else {
                this.showToast('❌ ' + data.message, 'error');
                return false;
            }
        } catch (error) {
            this.showToast('❌ Upload failed. Please try again.', 'error');
            return false;
        }
    }

    // Delete custom ringtone
    async deleteCustomRingtone() {
        try {
            const response = await fetch('/hfms/api/delete_ringtone.php');
            const data = await response.json();

            if (data.success) {
                this.customRingtoneUrl = null;
                if (this.selectedRingtone === 'custom') {
                    this.setRingtone('classic');
                }
                this.showToast('✓ ' + data.message, 'success');
                this.updateRingtoneUI();
            }
        } catch (error) {
            this.showToast('❌ Delete failed', 'error');
        }
    }

    // Play specific ringtone pattern
    playTone(ringtone, duration = null) {
        if (!this.audioContext) return;

        if (this.audioContext.state === 'suspended') {
            this.audioContext.resume();
        }

        const pattern = ringtone.pattern;
        const frequency = ringtone.frequency;

        switch (pattern) {
            case 'beep':
                this.playBeepPattern(frequency, duration);
                break;
            case 'chime':
                this.playChimePattern(frequency, duration);
                break;
            case 'urgent':
                this.playUrgentPattern(frequency, duration);
                break;
            case 'melody':
                this.playMelodyPattern(frequency, duration);
                break;
            case 'digital':
                this.playDigitalPattern(frequency, duration);
                break;
            default:
                this.playBeepPattern(frequency, duration);
        }
    }

    // Classic beep pattern
    playBeepPattern(frequency, duration) {
        const playBeep = () => {
            if (!this.isPlaying && !duration) return;

            try {
                const oscillator = this.audioContext.createOscillator();
                const gainNode = this.audioContext.createGain();

                oscillator.connect(gainNode);
                gainNode.connect(this.audioContext.destination);

                oscillator.frequency.value = frequency;
                oscillator.type = 'sine';
                gainNode.gain.value = 0.5;

                oscillator.start();

                setTimeout(() => {
                    oscillator.stop();
                    if (this.isPlaying && !duration) {
                        setTimeout(playBeep, 500);
                    }
                }, 200);
            } catch (e) {
                console.log('Sound error:', e);
            }
        };

        playBeep();

        if (duration) {
            setTimeout(() => this.isPlaying = false, duration);
        }
    }

    // Gentle chime pattern (ascending notes)
    playChimePattern(frequency, duration) {
        const notes = [frequency, frequency * 1.25, frequency * 1.5];
        let noteIndex = 0;

        const playNote = () => {
            if (!this.isPlaying && !duration) return;

            try {
                const oscillator = this.audioContext.createOscillator();
                const gainNode = this.audioContext.createGain();

                oscillator.connect(gainNode);
                gainNode.connect(this.audioContext.destination);

                oscillator.frequency.value = notes[noteIndex % notes.length];
                oscillator.type = 'triangle';

                gainNode.gain.setValueAtTime(0.4, this.audioContext.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, this.audioContext.currentTime + 0.5);

                oscillator.start();
                setTimeout(() => oscillator.stop(), 500);

                noteIndex++;

                if (this.isPlaying && !duration) {
                    setTimeout(playNote, 600);
                }
            } catch (e) {
                console.log('Sound error:', e);
            }
        };

        playNote();

        if (duration) {
            setTimeout(() => this.isPlaying = false, duration);
        }
    }

    // Urgent pattern (rapid beeps)
    playUrgentPattern(frequency, duration) {
        const playBeep = () => {
            if (!this.isPlaying && !duration) return;

            try {
                const oscillator = this.audioContext.createOscillator();
                const gainNode = this.audioContext.createGain();

                oscillator.connect(gainNode);
                gainNode.connect(this.audioContext.destination);

                oscillator.frequency.value = frequency;
                oscillator.type = 'square';
                gainNode.gain.value = 0.3;

                oscillator.start();

                setTimeout(() => {
                    oscillator.stop();
                    if (this.isPlaying && !duration) {
                        setTimeout(playBeep, 150);
                    }
                }, 100);
            } catch (e) {
                console.log('Sound error:', e);
            }
        };

        playBeep();

        if (duration) {
            setTimeout(() => this.isPlaying = false, duration);
        }
    }

    // Melody pattern (musical sequence)
    playMelodyPattern(frequency, duration) {
        const notes = [frequency, frequency * 1.125, frequency * 1.25, frequency * 1.5, frequency * 1.25];
        let noteIndex = 0;

        const playNote = () => {
            if (!this.isPlaying && !duration) return;

            try {
                const oscillator = this.audioContext.createOscillator();
                const gainNode = this.audioContext.createGain();

                oscillator.connect(gainNode);
                gainNode.connect(this.audioContext.destination);

                oscillator.frequency.value = notes[noteIndex % notes.length];
                oscillator.type = 'sine';
                gainNode.gain.value = 0.4;

                oscillator.start();
                setTimeout(() => oscillator.stop(), 300);

                noteIndex++;

                if (this.isPlaying && !duration) {
                    setTimeout(playNote, 400);
                }
            } catch (e) {
                console.log('Sound error:', e);
            }
        };

        playNote();

        if (duration) {
            setTimeout(() => this.isPlaying = false, duration);
        }
    }

    // Digital pattern (two-tone)
    playDigitalPattern(frequency, duration) {
        let high = true;

        const playBeep = () => {
            if (!this.isPlaying && !duration) return;

            try {
                const oscillator = this.audioContext.createOscillator();
                const gainNode = this.audioContext.createGain();

                oscillator.connect(gainNode);
                gainNode.connect(this.audioContext.destination);

                oscillator.frequency.value = high ? frequency : frequency * 0.75;
                oscillator.type = 'square';
                gainNode.gain.value = 0.25;

                oscillator.start();

                setTimeout(() => {
                    oscillator.stop();
                    high = !high;
                    if (this.isPlaying && !duration) {
                        setTimeout(playBeep, 200);
                    }
                }, 150);
            } catch (e) {
                console.log('Sound error:', e);
            }
        };

        playBeep();

        if (duration) {
            setTimeout(() => this.isPlaying = false, duration);
        }
    }

    // Play alarm with selected ringtone
    playAlarm() {
        if (this.isPlaying) return;
        this.isPlaying = true;

        if (this.selectedRingtone === 'custom' && this.customRingtoneUrl) {
            this.playCustomRingtone();
        } else {
            const ringtone = this.ringtones[this.selectedRingtone] || this.ringtones['classic'];
            this.playTone(ringtone);
        }
    }

    // Stop alarm sound
    stopAlarm() {
        this.isPlaying = false;
        this.stopCustomAudio();
    }

    // Create alarm popup styles
    createAlarmStyles() {
        const style = document.createElement('style');
        style.textContent = `
            @keyframes alarmPulse {
                0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
                50% { transform: scale(1.02); box-shadow: 0 0 0 20px rgba(239, 68, 68, 0); }
            }
            @keyframes alarmShake {
                0%, 100% { transform: translateX(0); }
                25% { transform: translateX(-5px); }
                75% { transform: translateX(5px); }
            }
            @keyframes bellRing {
                0%, 100% { transform: rotate(0deg); }
                25% { transform: rotate(20deg); }
                75% { transform: rotate(-20deg); }
            }
            .alarm-overlay {
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, 0.8);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 99999;
                backdrop-filter: blur(8px);
            }
            .alarm-popup {
                background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
                border: 3px solid #ef4444;
                border-radius: 24px;
                padding: 2.5rem;
                max-width: 450px;
                width: 90%;
                text-align: center;
                animation: alarmPulse 1s infinite;
            }
            .alarm-icon {
                font-size: 5rem;
                animation: bellRing 0.5s infinite;
                margin-bottom: 1rem;
            }
            .alarm-title {
                color: #ef4444;
                font-size: 1.5rem;
                font-weight: 700;
                margin-bottom: 0.5rem;
                text-transform: uppercase;
                letter-spacing: 2px;
            }
            .alarm-message {
                color: #fff;
                font-size: 1.8rem;
                font-weight: 600;
                margin-bottom: 1rem;
            }
            .alarm-time {
                color: #94a3b8;
                font-size: 1.1rem;
                margin-bottom: 1.5rem;
            }
            .alarm-buttons {
                display: flex;
                gap: 1rem;
                justify-content: center;
                flex-wrap: wrap;
            }
            .alarm-btn {
                padding: 1rem 2rem;
                border: none;
                border-radius: 12px;
                font-size: 1.1rem;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s;
            }
            .alarm-btn-stop {
                background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
                color: white;
            }
            .alarm-btn-stop:hover {
                transform: scale(1.05);
            }
            .alarm-btn-snooze {
                background: rgba(255,255,255,0.1);
                color: #94a3b8;
                border: 1px solid #475569;
            }
            .alarm-btn-snooze:hover {
                background: rgba(255,255,255,0.2);
            }
            
            /* Ringtone Settings Styles */
            .ringtone-settings {
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, 0.8);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 99998;
                backdrop-filter: blur(8px);
            }
            .ringtone-panel {
                background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
                border: 2px solid #667eea;
                border-radius: 24px;
                padding: 2rem;
                max-width: 450px;
                width: 90%;
                max-height: 90vh;
                overflow-y: auto;
            }
            .ringtone-title {
                color: #fff;
                font-size: 1.5rem;
                font-weight: 700;
                margin-bottom: 1.5rem;
                text-align: center;
            }
            .ringtone-section-title {
                color: #94a3b8;
                font-size: 0.9rem;
                text-transform: uppercase;
                letter-spacing: 1px;
                margin: 1rem 0 0.5rem;
            }
            .ringtone-option {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 1rem;
                margin-bottom: 0.5rem;
                background: rgba(255,255,255,0.05);
                border-radius: 12px;
                cursor: pointer;
                transition: all 0.3s;
                border: 2px solid transparent;
            }
            .ringtone-option:hover {
                background: rgba(255,255,255,0.1);
            }
            .ringtone-option.selected {
                border-color: #667eea;
                background: rgba(102, 126, 234, 0.2);
            }
            .ringtone-option.disabled {
                opacity: 0.5;
                cursor: not-allowed;
            }
            .ringtone-name {
                color: #fff;
                font-weight: 500;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }
            .ringtone-preview-btn {
                background: #667eea;
                color: white;
                border: none;
                padding: 0.5rem 1rem;
                border-radius: 8px;
                cursor: pointer;
                font-size: 0.9rem;
            }
            .ringtone-preview-btn:hover {
                background: #5a67d8;
            }
            .ringtone-close {
                display: block;
                width: 100%;
                margin-top: 1rem;
                padding: 1rem;
                background: rgba(255,255,255,0.1);
                color: #fff;
                border: none;
                border-radius: 12px;
                font-size: 1rem;
                cursor: pointer;
            }
            .ringtone-close:hover {
                background: rgba(255,255,255,0.2);
            }
            
            /* Upload section */
            .ringtone-upload {
                background: rgba(102, 126, 234, 0.1);
                border: 2px dashed #667eea;
                border-radius: 12px;
                padding: 1.5rem;
                text-align: center;
                margin-top: 1rem;
            }
            .ringtone-upload-label {
                color: #fff;
                display: block;
                cursor: pointer;
            }
            .ringtone-upload-label i {
                font-size: 2rem;
                color: #667eea;
                margin-bottom: 0.5rem;
                display: block;
            }
            .ringtone-upload-input {
                display: none;
            }
            .ringtone-upload-hint {
                color: #94a3b8;
                font-size: 0.85rem;
                margin-top: 0.5rem;
            }
            .custom-ringtone-info {
                background: rgba(16, 185, 129, 0.2);
                border: 1px solid #10b981;
                border-radius: 12px;
                padding: 1rem;
                margin-top: 1rem;
                display: flex;
                align-items: center;
                justify-content: space-between;
            }
            .custom-ringtone-name {
                color: #10b981;
                font-weight: 500;
            }
            .custom-ringtone-delete {
                background: #ef4444;
                color: white;
                border: none;
                padding: 0.5rem 1rem;
                border-radius: 8px;
                cursor: pointer;
                font-size: 0.85rem;
            }
        `;
        document.head.appendChild(style);
    }

    // Update ringtone UI
    updateRingtoneUI() {
        const customOption = document.querySelector('.ringtone-option[data-ringtone="custom"]');
        const customInfo = document.getElementById('custom-ringtone-info');

        if (customOption) {
            if (this.customRingtoneUrl) {
                customOption.classList.remove('disabled');
            } else {
                customOption.classList.add('disabled');
            }
        }

        if (customInfo) {
            if (this.customRingtoneUrl) {
                customInfo.style.display = 'flex';
            } else {
                customInfo.style.display = 'none';
            }
        }
    }

    // Show ringtone settings panel
    showRingtoneSettings() {
        document.getElementById('ringtone-settings')?.remove();

        const panel = document.createElement('div');
        panel.id = 'ringtone-settings';
        panel.className = 'ringtone-settings';

        let optionsHtml = '';
        for (const [id, ringtone] of Object.entries(this.ringtones)) {
            if (id === 'custom') continue; // Handle custom separately

            const selected = id === this.selectedRingtone ? 'selected' : '';
            optionsHtml += `
                <div class="ringtone-option ${selected}" data-ringtone="${id}" onclick="alarmSystem.selectRingtone('${id}')">
                    <span class="ringtone-name">${ringtone.icon} ${ringtone.name}</span>
                    <button class="ringtone-preview-btn" onclick="event.stopPropagation(); alarmSystem.previewRingtone('${id}')">
                        ▶ Preview
                    </button>
                </div>
            `;
        }

        const customSelected = this.selectedRingtone === 'custom' ? 'selected' : '';
        const customDisabled = !this.customRingtoneUrl ? 'disabled' : '';

        panel.innerHTML = `
            <div class="ringtone-panel">
                <div class="ringtone-title">🔔 Alarm Ringtone Settings</div>
                
                <div class="ringtone-section-title">Built-in Ringtones</div>
                ${optionsHtml}
                
                <div class="ringtone-section-title">Custom Ringtone</div>
                <div class="ringtone-option ${customSelected} ${customDisabled}" data-ringtone="custom" onclick="alarmSystem.selectRingtone('custom')">
                    <span class="ringtone-name">⭐ My Ringtone</span>
                    <button class="ringtone-preview-btn" onclick="event.stopPropagation(); alarmSystem.previewRingtone('custom')" ${!this.customRingtoneUrl ? 'disabled' : ''}>
                        ▶ Preview
                    </button>
                </div>
                
                <div class="ringtone-upload">
                    <label class="ringtone-upload-label">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <span>Click to upload your ringtone</span>
                        <input type="file" class="ringtone-upload-input" accept="audio/mp3,audio/wav,audio/ogg,audio/webm,.mp3,.wav,.ogg,.webm" onchange="alarmSystem.handleFileUpload(event)">
                    </label>
                    <div class="ringtone-upload-hint">
                        Supported: MP3, WAV, OGG, WebM (max 2MB)
                    </div>
                </div>
                
                <div id="custom-ringtone-info" class="custom-ringtone-info" style="display: ${this.customRingtoneUrl ? 'flex' : 'none'}">
                    <span class="custom-ringtone-name">✓ Custom ringtone uploaded</span>
                    <button class="custom-ringtone-delete" onclick="alarmSystem.deleteCustomRingtone()">
                        🗑️ Delete
                    </button>
                </div>
                
                <button class="ringtone-close" onclick="alarmSystem.closeRingtoneSettings()">
                    ✓ Done
                </button>
            </div>
        `;

        document.body.appendChild(panel);
    }

    // Handle file upload
    handleFileUpload(event) {
        const file = event.target.files[0];
        if (!file) return;

        // Validate file size (2MB)
        if (file.size > 2 * 1024 * 1024) {
            this.showToast('❌ File is too large. Maximum size is 2MB.', 'error');
            return;
        }

        // Validate file type
        const validTypes = ['audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/x-wav', 'audio/ogg', 'audio/webm'];
        if (!validTypes.includes(file.type)) {
            this.showToast('❌ Invalid file type. Please upload MP3, WAV, OGG, or WebM files.', 'error');
            return;
        }

        this.uploadRingtone(file);
    }

    // Select a ringtone
    selectRingtone(ringtoneId) {
        if (ringtoneId === 'custom' && !this.customRingtoneUrl) {
            this.showToast('⚠️ Please upload a custom ringtone first!', 'error');
            return;
        }

        this.setRingtone(ringtoneId);

        // Update UI
        document.querySelectorAll('.ringtone-option').forEach(opt => {
            opt.classList.remove('selected');
            if (opt.dataset.ringtone === ringtoneId) {
                opt.classList.add('selected');
            }
        });

        this.showToast(`🎵 Ringtone set to: ${this.ringtones[ringtoneId].name}`, 'success');
    }

    // Close ringtone settings
    closeRingtoneSettings() {
        this.stopAlarm();
        document.getElementById('ringtone-settings')?.remove();
    }

    // Show alarm popup
    showAlarm(reminder) {
        // Remove existing alarm
        document.getElementById('alarm-popup')?.remove();

        const categoryIcons = {
            exercise: '🏃',
            water: '💧',
            medication: '💊',
            meal: '🍽️',
            sleep: '😴',
            other: '🔔'
        };

        const icon = categoryIcons[reminder.category] || '🔔';
        const now = new Date();
        const timeStr = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });

        const overlay = document.createElement('div');
        overlay.id = 'alarm-popup';
        overlay.className = 'alarm-overlay';
        overlay.innerHTML = `
            <div class="alarm-popup">
                <div class="alarm-icon">🔔</div>
                <div class="alarm-title">⏰ REMINDER ALERT!</div>
                <div class="alarm-message">${icon} ${reminder.title}</div>
                <div class="alarm-time">
                    ${reminder.description || 'Time for your scheduled activity!'}
                    <br><br>
                    <strong>🕐 ${timeStr}</strong>
                </div>
                <div class="alarm-buttons">
                    <button class="alarm-btn alarm-btn-snooze" onclick="alarmSystem.snooze(${reminder.reminder_id})">
                        ⏱️ Snooze 5 min
                    </button>
                    <button class="alarm-btn alarm-btn-stop" onclick="alarmSystem.dismiss(${reminder.reminder_id})">
                        ✓ DISMISS
                    </button>
                </div>
            </div>
        `;

        document.body.appendChild(overlay);

        // Start alarm sound
        this.playAlarm();

        // Vibrate if supported
        if (navigator.vibrate) {
            navigator.vibrate([500, 200, 500, 200, 500]);
        }
    }

    // Dismiss alarm
    dismiss(reminderId) {
        this.stopAlarm();
        document.getElementById('alarm-popup')?.remove();

        // Mark as complete
        fetch('/hfms/api/complete_reminder.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ reminder_id: reminderId })
        });

        this.showToast('✓ Reminder dismissed!', 'success');
    }

    // Snooze alarm (5 minutes)
    snooze(reminderId) {
        this.stopAlarm();
        document.getElementById('alarm-popup')?.remove();

        // Set snooze expiry time (5 minutes from now)
        const snoozeUntil = Date.now() + (5 * 60 * 1000);
        this.snoozedReminders.set(reminderId, snoozeUntil);

        console.log(`🔕 Reminder ${reminderId} snoozed until ${new Date(snoozeUntil).toLocaleTimeString()}`);

        this.showToast('⏱️ Snoozed for 5 minutes', 'info');
    }

    // Check if reminder is snoozed
    isSnoozed(reminderId) {
        if (!this.snoozedReminders.has(reminderId)) {
            return false;
        }

        const snoozeUntil = this.snoozedReminders.get(reminderId);
        if (Date.now() >= snoozeUntil) {
            this.snoozedReminders.delete(reminderId);
            console.log(`⏰ Snooze expired for reminder ${reminderId}`);
            return false;
        }

        return true;
    }

    // Show toast notification
    showToast(message, type = 'info') {
        // Remove existing toasts
        document.querySelectorAll('.alarm-toast').forEach(t => t.remove());

        const toast = document.createElement('div');
        toast.className = 'alarm-toast';
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6'};
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            z-index: 999999;
            animation: slideIn 0.3s ease;
            max-width: 300px;
        `;
        toast.textContent = message;
        document.body.appendChild(toast);

        setTimeout(() => toast.remove(), 3000);
    }

    // Check for due reminders
    async checkReminders() {
        try {
            const response = await fetch('/hfms/api/get_due_reminders.php');
            const data = await response.json();

            console.log('Checking reminders...', data);

            if (data.success && data.reminders && data.reminders.length > 0) {
                data.reminders.forEach(reminder => {
                    const reminderId = parseInt(reminder.reminder_id);

                    if (this.isSnoozed(reminderId)) {
                        console.log(`⏸️ Reminder ${reminderId} is snoozed, skipping...`);
                        return;
                    }

                    const key = `${reminder.reminder_id}-${new Date().getMinutes()}`;
                    if (!this.notifiedReminders.has(key)) {
                        this.notifiedReminders.add(key);
                        console.log('🔔 Triggering alarm for:', reminder.title);
                        this.showAlarm(reminder);
                    }
                });
            }
        } catch (error) {
            console.log('Reminder check error:', error);
        }
    }

    // Start checking
    startAlarmCheck() {
        setTimeout(() => this.checkReminders(), 2000);
        setInterval(() => this.checkReminders(), this.checkInterval);
    }
}

// Initialize alarm system
let alarmSystem;
document.addEventListener('DOMContentLoaded', () => {
    alarmSystem = new AlarmSystem();
});

// Make it global
window.alarmSystem = alarmSystem;

// Test alarm function
window.testAlarm = function () {
    const testReminder = {
        reminder_id: 999,
        title: 'Test Alarm',
        description: 'This is a test alarm to show how it works!',
        category: 'water'
    };

    if (window.alarmSystem) {
        window.alarmSystem.showAlarm(testReminder);
    } else {
        alarmSystem = new AlarmSystem();
        setTimeout(() => alarmSystem.showAlarm(testReminder), 500);
    }
};

// Open ringtone settings
window.openRingtoneSettings = function () {
    if (window.alarmSystem) {
        window.alarmSystem.showRingtoneSettings();
    } else {
        alarmSystem = new AlarmSystem();
        setTimeout(() => alarmSystem.showRingtoneSettings(), 500);
    }
};

console.log('💡 Type testAlarm() in console to test the alarm!');
console.log('🎵 Type openRingtoneSettings() to change ringtone!');
