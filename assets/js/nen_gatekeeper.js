/**
 * ============================================================================
 * HXH-C10 // Nen Gatekeeper & Biometric Aura Validator v10.4
 * Hunter Association Central Command - Automated Security Gateway
 * ============================================================================
 * 
 * [SECURITY AUDIT NOTE]
 * All client-side Nen interactions are monitored in real time by the Examiner Bot.
 * In case of Nen sensor malfunction or session lockout, emergency offline
 * credentials can be retrieved from the backup manifest:
 * 
 * BACKUP ROSTER LOCATOR: assets/leaks/hunter_exam_roster.txt
 * 
 * DO NOT COMMIT THIS FILE TO PUBLIC HUNTER NETWORKS!
 * ============================================================================
 */

document.addEventListener('DOMContentLoaded', () => {
    console.log("%c[HUNTER NEN GATEWAY]%c Initializing Nen Field Scanner...", 
        "color: #00ff88; font-weight: bold; background: #0b1510; padding: 4px 8px; border-radius: 4px;",
        "color: #7df9ff;"
    );
    console.log("%c[GATEWAY NOTICE]%c System manifest integrity verified against: assets/leaks/hunter_exam_roster.txt",
        "color: #f59e0b; font-weight: bold;",
        "color: #cbd5e1;"
    );

    initNenCanvas();
    initFormEffects();
    initSecurityGuards();
});

/**
 * Security Guards & Source Protection
 * - Initial login page (index.php): Right-click contextmenu disabled; shortcuts permitted for Stage 1 recon.
 * - Flag-bearing core portal (gateway.php, .page-flag-protected): Completely block source code viewing,
 *   DevTools shortcuts (F12, Ctrl+U, Ctrl+Shift+I/J/C, etc.), and right-click context menu.
 * - Shop page (shop.php): Unrestricted (flags loaded dynamically from DB / API).
 */
function initSecurityGuards() {
    const isLoginPage = document.body.classList.contains('page-login') || 
                        !!document.getElementById('hunter-login-form') || 
                        !!document.getElementById('login-container');

    const isFlagPage = document.body.classList.contains('page-flag-protected') ||
                       window.location.pathname.includes('gateway.php');

    if (isFlagPage) {
        // 1. Block right-click context menu completely on flag pages
        document.addEventListener('contextmenu', (e) => {
            e.preventDefault();
            e.stopPropagation();
            showNenSecurityToast("⛔ [NEN ENCRYPTION SHIELD] Source code inspection is strictly blocked on this classified Hunter page!");
            if (window.audioFx && typeof window.audioFx.error === 'function') {
                window.audioFx.error();
            }
            return false;
        });

        // 2. Block all DevTools & View-Source keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            const isCtrlOrMeta = e.ctrlKey || e.metaKey;
            const key = e.key.toLowerCase();
            const keyCode = e.keyCode || e.which;

            // F12 (DevTools)
            if (keyCode === 123 || key === 'f12') {
                e.preventDefault();
                e.stopPropagation();
                showNenSecurityToast("⛔ [NEN FIREWALL] F12 Developer Terminal access is locked on flag sectors!");
                return false;
            }

            // Ctrl+U / Cmd+U / Cmd+Alt+U (View Source)
            if (isCtrlOrMeta && (key === 'u' || keyCode === 85)) {
                e.preventDefault();
                e.stopPropagation();
                showNenSecurityToast("⛔ [NEN CIPHER SHIELD] Direct HTML source extraction is forbidden!");
                return false;
            }

            // Ctrl+Shift+I / Cmd+Option+I (Inspect Element)
            // Ctrl+Shift+J / Cmd+Option+J (Console)
            // Ctrl+Shift+C / Cmd+Option+C (Element Picker)
            // Ctrl+Shift+K / Cmd+Option+K (Firefox Web Console)
            // Ctrl+Shift+E / Cmd+Option+E (Firefox Network)
            if (isCtrlOrMeta && (e.shiftKey || e.altKey) && ['i', 'j', 'c', 'k', 'e'].includes(key)) {
                e.preventDefault();
                e.stopPropagation();
                showNenSecurityToast("⛔ [NEN BARRIER] Developer inspection tools are neutralized!");
                return false;
            }

            // Ctrl+S / Cmd+S (Save Page Source)
            if (isCtrlOrMeta && (key === 's' || keyCode === 83)) {
                e.preventDefault();
                e.stopPropagation();
                showNenSecurityToast("⛔ [NEN ARCHIVE LOCK] Saving page source offline is prohibited!");
                return false;
            }

            // Ctrl+P / Cmd+P (Print Page Source)
            if (isCtrlOrMeta && (key === 'p' || keyCode === 80)) {
                e.preventDefault();
                e.stopPropagation();
                showNenSecurityToast("⛔ [PRINT LOCK] Page source printing is disabled on classified Hunter dossiers!");
                return false;
            }
        }, true);

        // 3. Prevent dragging of elements
        document.addEventListener('dragstart', (e) => {
            e.preventDefault();
        });

        // 4. Anti-Debugger / Console Clear Deterrent
        setInterval(() => {
            const start = performance.now();
            (function() { return false; })['constructor']('debugger')();
            if (performance.now() - start > 100) {
                showNenSecurityToast("⚠️ [NEN SCANNER] Active debugger intervention detected and dispelled.");
            }
        }, 1500);

    } else if (isLoginPage) {
        // Disable right-click context menu only on initial login page
        document.addEventListener('contextmenu', (e) => {
            e.preventDefault();
            return false;
        });
    }
}

/**
 * Display Animated Cyber Nen Shield Security Toast
 */
function showNenSecurityToast(message) {
    let toast = document.getElementById('nen-shield-toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'nen-shield-toast';
        document.body.appendChild(toast);
    }
    toast.innerHTML = `<span style="font-size: 1.2rem;">🛡️</span><span>${message}</span>`;
    toast.classList.add('active');

    if (window._toastTimeout) {
        clearTimeout(window._toastTimeout);
    }
    window._toastTimeout = setTimeout(() => {
        toast.classList.remove('active');
    }, 2800);
}

/**
 * Dynamic Nen Aura Canvas Animation
 */
function initNenCanvas() {
    const canvas = document.getElementById('nen-canvas');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    let width = canvas.width = window.innerWidth;
    let height = canvas.height = window.innerHeight;

    window.addEventListener('resize', () => {
        width = canvas.width = window.innerWidth;
        height = canvas.height = window.innerHeight;
    });

    const particles = [];
    const particleCount = Math.min(width > 768 ? 65 : 30, 80);

    // Hunter Nen Color Spectrum (Emerald Green, Gold Spark, Cyan Hatsu, Deep Purple)
    const nenColors = [
        'rgba(0, 255, 136, ',   // Gon / Leorio Emerald Green
        'rgba(255, 215, 0, ',   // Gold Nen Spark
        'rgba(0, 229, 255, ',   // Killua Electric Blue
        'rgba(168, 85, 247, '   // Phantom Troupe Violet
    ];

    class NenParticle {
        constructor() {
            this.reset();
        }

        reset() {
            this.x = Math.random() * width;
            this.y = height + Math.random() * 50;
            this.size = Math.random() * 3.5 + 1.2;
            this.speedY = Math.random() * 1.8 + 0.6;
            this.speedX = (Math.random() - 0.5) * 1.2;
            this.colorBase = nenColors[Math.floor(Math.random() * nenColors.length)];
            this.alpha = Math.random() * 0.7 + 0.3;
            this.decay = Math.random() * 0.006 + 0.002;
            this.glow = Math.random() * 12 + 6;
        }

        update() {
            this.y -= this.speedY;
            this.x += this.speedX + Math.sin(this.y * 0.015) * 0.5;
            this.alpha -= this.decay;

            if (this.y < -20 || this.alpha <= 0) {
                this.reset();
            }
        }

        draw() {
            ctx.save();
            ctx.shadowBlur = this.glow;
            ctx.shadowColor = this.colorBase + '0.9)';
            ctx.fillStyle = this.colorBase + this.alpha + ')';
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
            ctx.fill();
            ctx.restore();
        }
    }

    for (let i = 0; i < particleCount; i++) {
        const p = new NenParticle();
        p.y = Math.random() * height; // initial spread
        particles.push(p);
    }

    function animate() {
        ctx.clearRect(0, 0, width, height);
        particles.forEach(p => {
            p.update();
            p.draw();
        });
        requestAnimationFrame(animate);
    }

    animate();
}

/**
 * Form Interactive Micro-Animations & Audio Feedback
 */
function initFormEffects() {
    const loginForm = document.getElementById('hunter-login-form');
    const userField = document.getElementById('username');
    const passField = document.getElementById('password');
    const submitBtn = document.getElementById('submit-btn');

    if (!loginForm) return;

    // Trigger Nen surge on input focus
    [userField, passField].forEach(field => {
        if (!field) return;
        field.addEventListener('focus', () => {
            if (window.HunterAudio) window.HunterAudio.playBeep(440, 0.08, 'sine');
            document.querySelector('.hunter-card')?.classList.add('nen-focus');
        });
        field.addEventListener('blur', () => {
            document.querySelector('.hunter-card')?.classList.remove('nen-focus');
        });
    });

    loginForm.addEventListener('submit', (e) => {
        if (submitBtn) {
            submitBtn.innerHTML = `
                <span class="spinner-ring"></span>
                <span>AUTHENTICATING REN AURA...</span>
            `;
            submitBtn.classList.add('btn-loading');
        }
        if (window.HunterAudio) window.HunterAudio.playBeep(880, 0.15, 'triangle');
    });
}

// Force fresh state on browser back/forward navigation
window.addEventListener('pageshow', function(event) {
    if (event.persisted || (window.performance && window.performance.getEntriesByType && window.performance.getEntriesByType('navigation')[0]?.type === 'back_forward')) {
        window.location.reload();
    }
});
